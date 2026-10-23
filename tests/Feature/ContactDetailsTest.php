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

    public function test_the_contact_page_has_two_people_cards_no_branch_and_the_office_timings_beside_the_map(): void
    {
        $html = $this->get('/contact-us')->assertOk()->getContent();

        // two people cards (the head office and the timings now sit beside the map)
        preg_match_all('/<h3 class="mt-4 text-base font-bold text-navy">([^<]+)<\/h3>/', $html, $m);
        $this->assertSame(['Founder', 'Enquiries & Sales'], array_map('html_entity_decode', $m[1]));

        // Jodhpur is only a pin on the map (no branch card, no address, no 'Branch Office' label)
        $this->assertSame(2, substr_count($html, 'class="map-pin absolute"'));
        $this->assertSame(1, substr_count($html, 'Jodhpur'));
        $this->assertStringNotContainsString('Branch Office', $html);
        $this->assertStringNotContainsString('Plot No. 00', $html);
        $this->assertStringContainsString('Headquartered in Jaipur', $html);
        $this->assertStringContainsString('Head Office - Jaipur', $html);
        $this->assertStringContainsString('Office timings', $html);

        // the timings card says only what the owner asked for
        $this->assertStringContainsString('Monday to Saturday: 10:00 am - 7:00 pm', $html);
        $this->assertStringContainsString('Closed on festive holidays', $html);
        $this->assertStringNotContainsString('09:30 am', $html);
        $this->assertStringNotContainsString('Holiday</p>', $html);

        // three across on desktop, two on tablets, one on phones
        $this->assertStringContainsString('lg:w-[calc(33.333%-0.84rem)]', $html);
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
            'Get in', 'Years of experience', 'What can we', 'From enquiry to', 'Headquartered in', 'For support', 'Common', 'Contact our', 'Prefer to talk?',
        ]);

        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);

        $this->assertStringContainsString('href="https://wa.me/919829015024"', $html);
        $this->assertStringContainsString('tel:+919829015024', $html);
        $this->assertStringNotContainsString('{$', $html);
    }

    public function test_the_header_has_call_and_whatsapp_next_to_the_message_button_and_a_trust_strip(): void
    {
        $html = $this->get(route('contact'))->assertOk()->getContent();
        $header = substr($html, strpos($html, 'Get in'), strpos($html, 'What can we') - strpos($html, 'Get in'));

        $this->assertStringContainsString('href="#contact-form"', $header);
        $this->assertStringContainsString('href="tel:+919829015024"', $header);
        $this->assertStringContainsString('href="https://wa.me/919829015024"', $header);
        foreach (['30+', '1000+', '50+', '70+'] as $n) {
            $this->assertStringContainsString($n, $header);
        }
    }

    public function test_the_form_keeps_company_and_city_behind_a_toggle_and_shows_the_reply_note(): void
    {
        $html = $this->get(route('contact'))->assertOk()->getContent();

        $this->assertStringContainsString('We usually reply within one working day.', $html);
        $this->assertStringContainsString('Add company and city (optional)', $html);
        $this->assertGreaterThan(strpos($html, 'name="message"'), strpos($html, 'name="company"'));   // after the message
        $this->assertStringContainsString('name="city"', $html);
    }

    public function test_the_company_and_city_toggle_opens_by_itself_when_they_were_filled(): void
    {
        $this->assertStringContainsString('x-data="{ more: false }"', $this->get(route('contact'))->getContent());

        $html = $this->withSession(['_old_input' => ['company' => 'Acme Labs']])->get(route('contact'))->assertOk()->getContent();
        $this->assertStringContainsString('x-data="{ more: true }"', $html);
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
