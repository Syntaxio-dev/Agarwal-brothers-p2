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
        $this->assertLessThan(strpos($html, 'Rupa Maheshwari'), strpos($html, 'Mukesh Kumar Agarwal'));   // primary first
        $this->assertStringContainsString('href="tel:+919829015024"', $html);
        $this->assertStringContainsString('href="tel:+919352935901"', $html);
        $this->assertStringContainsString('href="mailto:office@agarwalbrothers.com"', $html);
        foreach (['00000', 'sales@agarwalbrothers.com', 'service@agarwalbrothers.com', 'info@agarwalbrothers.com'] as $old) {
            $this->assertStringNotContainsString($old, $html, $old);
        }
        $this->assertStringContainsString('Jodhpur', $html);      // the branch is kept as it was
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
}
