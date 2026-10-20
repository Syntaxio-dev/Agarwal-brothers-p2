<?php

/*
|--------------------------------------------------------------------------
| Contact page content
|--------------------------------------------------------------------------
| The real company details (email, phones, address, GSTIN) given by the owner on 15 Oct.
| There is no branch office on the site any more (add one to 'branches' to show it again). Edit here and every page
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

    // Branch offices shown on the Contact Us page. Empty on purpose: only the head office is shown for now.
    'branches' => [],

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

    // "How we work with you" steps on the Contact Us page (no promised response times on purpose).
    'steps' => [
        ['title' => 'You send an enquiry', 'text' => 'Tell us the product, brand or application you are looking for, using the form or a phone call.', 'icon' => 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5'],
        ['title' => 'We connect with you', 'text' => 'Our team calls or writes to understand your requirement and the samples or tests you work with.', 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
        ['title' => 'You get a quotation', 'text' => 'We share the suitable models with their specifications and a quotation for your approval.', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
        ['title' => 'We stay in touch', 'text' => 'Once you confirm, we coordinate your order and remain available for any question afterwards.', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ],

    // Quick-intent tiles on the Contact Us page. A 'topic' tile fills the form's subject line (?topic=key).
    'topics' => [
        'product' => 'Product enquiry',
        'quote' => 'Request for quotation',
        'service' => 'Service / spare parts',
    ],

    // Office hours. Line 1 is the one used in emails ({hours}) and on the 404 pages, so it holds the days and the time.
    'working_days' => [
        'Monday to Saturday: 10:00 am - 7:00 pm',
        'Closed on festive holidays',
    ],
];
