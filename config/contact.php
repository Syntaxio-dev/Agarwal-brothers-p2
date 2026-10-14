<?php

/*
|--------------------------------------------------------------------------
| Contact page content
|--------------------------------------------------------------------------
| The real company details (email, phones, address, GSTIN) given by the owner on 15 Oct.
| The BRANCH office and the working hours are still placeholders. Edit here and every page
| (footer, Contact Us, spec sheet, emails, 404 pages, Google company data) follows.
*/

return [

    // Where "Contact Our Team" form submissions are emailed.
    'inbox' => env('CONTACT_INBOX', 'office@agarwalbrothers.com'),

    'head_office' => [
        'city' => 'Jaipur',
        'label' => 'Head Office',
        'street' => 'S3 Road No.1, Behind VKIA Association Bhawan, Vishwakarma Industrial Area',
        'postal_code' => '302013',
        'address' => 'S3 Road No.1, Behind VKIA Association Bhawan, Vishwakarma Industrial Area, Jaipur, Rajasthan 302013',
    ],

    // GST registration number (footer, Contact Us, spec sheets and the Google company data)
    'gstin' => '08ABYPA8087G1Z2',

    'branches' => [
        [
            'city' => 'Jodhpur',
            'label' => 'Branch Office',
            'address' => 'Plot No. 00, Industrial Area, Jodhpur, Rajasthan - 342001',
        ],
    ],

    // The people customers can call, most important first. The first one is the PRIMARY contact
    // (footer, emails, spec sheet, 404 pages, Google data and the WhatsApp button all lead with it);
    // the second is the secondary enquiry line. 'phone' is shown as written.
    'departments' => [
        [
            'title' => 'Founder',
            'person' => 'Mukesh Kumar Agarwal',
            'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            'email' => 'office@agarwalbrothers.com',
            'phone' => '+91 98290 15024',
        ],
        [
            'title' => 'Enquiries & Sales',
            'person' => 'Rupa Maheshwari',
            'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
            'email' => 'office@agarwalbrothers.com',
            'phone' => '+91 93529 35901',
        ],
    ],

    // For any enquiry or anything else
    'mail_24x7' => 'office@agarwalbrothers.com',
    'call' => '+91 98290 15024',              // primary number (Mukesh Kumar Agarwal)
    'call_secondary' => '+91 93529 35901',    // secondary enquiry number (Rupa Maheshwari)

    // The floating WhatsApp button opens a chat with this number (digits with country code) when Site Settings has none.
    'whatsapp' => '919829015024',

    'working_days' => [
        'Mon-Fri: 09:30 am - 05:30 pm',
        '1st & 3rd Sat: 09:30 am - 01:30 pm',
        '2nd & 4th Saturday and Sunday: Holiday',
    ],
];
