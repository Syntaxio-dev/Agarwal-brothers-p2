<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Support\HelpLinks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_real_company_details_are_in_the_config(): void
    {
        $this->assertSame('office@agarwalbrothers.com', config('contact.mail_24x7'));
        $this->assertSame('office@agarwalbrothers.com', config('contact.inbox'));
        // primary = Mukesh Kumar Agarwal, secondary = Rupa Maheshwari
        $this->assertSame('+91 98290 15024', config('contact.call'));
        $this->assertSame('+91 93529 35901', config('contact.call_secondary'));
        $this->assertSame('Mukesh Kumar Agarwal', config('contact.departments.0.person'));
        $this->assertSame('+91 98290 15024', config('contact.departments.0.phone'));
        $this->assertSame('Rupa Maheshwari', config('contact.departments.1.person'));
        $this->assertSame('+91 93529 35901', config('contact.departments.1.phone'));
        // WhatsApp chats go to the primary number
        $this->assertSame('919829015024', config('contact.whatsapp'));
        $this->assertSame('08ABYPA8087G1Z2', config('contact.gstin'));
        $this->assertStringContainsString('Jaipur, Rajasthan 302013', config('contact.head_office.address'));
    }

    public function test_contact_page_lists_both_people_primary_first_with_the_address_and_gstin(): void
    {
        $html = $this->get('/contact-us')->assertOk()->getContent();

        foreach (['office@agarwalbrothers.com', 'Mukesh Kumar Agarwal', '+91 98290 15024', 'Rupa Maheshwari', '+91 93529 35901',
                  'Vishwakarma Industrial Area', '302013', '08ABYPA8087G1Z2'] as $text) {
            $this->assertStringContainsString($text, $html, $text);
        }
        $body = substr($html, (int) strpos($html, '</head>'));   // the FAQ data in <head> names the sales contact first
        $this->assertLessThan(strpos($body, 'Rupa Maheshwari'), strpos($body, 'Mukesh Kumar Agarwal'));   // primary first
        $this->assertStringContainsString('href="tel:+919829015024"', $html);
        $this->assertStringContainsString('href="tel:+919352935901"', $html);
        $this->assertStringContainsString('href="mailto:office@agarwalbrothers.com"', $html);
        foreach (['00000', 'sales@agarwalbrothers.com', 'service@agarwalbrothers.com', 'info@agarwalbrothers.com'] as $old) {
            $this->assertStringNotContainsString($old, $html, $old);
        }
    }

    public function test_footer_shows_both_numbers_primary_first_and_the_gstin(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $footer = substr($html, (int) strpos($html, '<footer'));

        $this->assertStringContainsString('+91 98290 15024', $footer);
        $this->assertStringContainsString('+91 93529 35901', $footer);
        $this->assertLessThan(strpos($footer, '+91 93529 35901'), strpos($footer, '+91 98290 15024'));
        $this->assertStringContainsString('Mukesh Kumar Agarwal', $footer);
        $this->assertStringContainsString('Rupa Maheshwari', $footer);
        $this->assertStringContainsString('office@agarwalbrothers.com', $footer);
        $this->assertStringContainsString('GSTIN: 08ABYPA8087G1Z2', $footer);
        $this->assertStringContainsString('Vishwakarma Industrial Area', $footer);
    }

    public function test_the_whatsapp_buttons_open_a_chat_with_the_primary_number(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('https://wa.me/919829015024?text=', $html);          // floating button
        $this->assertStringContainsString('href="https://wa.me/919829015024"', $html);          // footer icon
        $this->assertStringNotContainsString('wa.me/919352935901', $html);

        // a number saved in Site Settings still wins (the owner can change it in the admin)
        SiteSetting::create(['whatsapp_number' => '911234567890']);
        $this->assertStringContainsString('https://wa.me/911234567890', $this->get('/')->getContent());
    }

    public function test_google_company_data_leads_with_the_primary_number(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('"telephone":"+91 98290 15024"', $html);
        $this->assertStringContainsString('"email":"office@agarwalbrothers.com"', $html);
        $this->assertStringContainsString('08ABYPA8087G1Z2', $html);
        $this->assertStringContainsString('"postalCode":"302013"', $html);
    }

    public function test_help_cards_list_the_main_line_and_the_secondary_number_once_each(): void
    {
        $phones = array_column(HelpLinks::contacts(), 'phone');

        $this->assertSame(['+91 98290 15024', '+91 93529 35901'], $phones);
    }

    public function test_the_contact_page_has_four_cards_no_branch_and_the_new_office_timings(): void
    {
        $html = $this->get('/contact-us')->assertOk()->getContent();

        // exactly four cards: Founder, Enquiries & Sales, Head Office, Office Timings
        preg_match_all('/<h3 class="mt-4 text-base font-bold text-navy">([^<]+)<\/h3>/', $html, $m);
        $this->assertSame(['Founder', 'Enquiries & Sales', 'Head Office - Jaipur', 'Office Timings'], array_map('html_entity_decode', $m[1]));

        // the Jodhpur branch is gone everywhere on the page (card, map pin, texts)
        $this->assertStringNotContainsString('Jodhpur', $html);
        $this->assertStringNotContainsString('Branch Office', $html);
        $this->assertStringNotContainsString('Plot No. 00', $html);
        $this->assertStringContainsString('Tap into our local team in Jaipur', $html);

        // the timings card says only what the owner asked for
        $this->assertStringContainsString('Monday to Saturday: 10:00 am - 7:00 pm', $html);
        $this->assertStringContainsString('Closed on festive holidays', $html);
        $this->assertStringNotContainsString('09:30 am', $html);
        $this->assertStringNotContainsString('Holiday</p>', $html);

        // four across on desktop, two on tablets, one on phones
        $this->assertStringContainsString('lg:w-[calc(25%-0.9375rem)]', $html);
        $this->assertStringContainsString('lg:px-3 xl:px-6', $html);                // a bit tighter between 1024 and 1279px so the email fits
        $this->assertStringContainsString('sm:w-[calc(50%-0.625rem)]', $html);
    }

    public function test_the_hours_in_emails_and_on_error_pages_use_the_new_office_timings(): void
    {
        $this->assertSame('Monday to Saturday: 10:00 am - 7:00 pm', \App\Support\HelpLinks::hours());
        $this->assertSame('Monday to Saturday: 10:00 am - 7:00 pm', config('contact.working_days.0'));
        $this->assertSame([], config('contact.branches'));
    }

    public function test_the_contact_page_has_steps_quick_links_faq_and_a_call_band_in_order(): void
    {
        $html = $this->get(route('contact'))->assertOk()->getContent();

        $order = array_map(fn ($n) => strpos($html, $n), [
            'For Support', 'From enquiry to', 'What can we', 'Contact Our', 'Common', 'Prefer to talk?',
        ]);

        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);

        $this->assertStringContainsString('href="https://wa.me/919829015024"', $html);
        $this->assertStringContainsString('tel:+919829015024', $html);
        $this->assertStringNotContainsString('{$', $html);
    }

    public function test_the_faq_is_also_sent_to_google_as_valid_faq_page_data(): void
    {
        $html = $this->get(route('contact'))->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $faq = collect($m[1])->map(fn ($j) => json_decode($j, true))->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faq);
        $this->assertGreaterThanOrEqual(5, count($faq['mainEntity']));
        $this->assertStringContainsString('08ABYPA8087G1Z2', json_encode($faq));
    }

    public function test_a_quick_link_topic_fills_the_subject_and_unknown_topics_are_ignored(): void
    {
        $this->get(route('contact', ['topic' => 'quote']))->assertOk()->assertSee('value="Request for quotation"', false);

        $this->get(route('contact', ['topic' => '"><script>alert(1)</script>']))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
