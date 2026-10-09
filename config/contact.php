<?php

/*
|--------------------------------------------------------------------------
| Contact page content
|--------------------------------------------------------------------------
| All details below are DUMMY placeholders. Edit them here and the Contact
| Us page updates; no view changes needed.
*/

return [

    // Where "Contact Our Team" form submissions are emailed.
    'inbox' => env('CONTACT_INBOX', env('MAIL_FROM_ADDRESS')),

    'head_office' => [
        'city' => 'Jaipur',
        'label' => 'Head Office',
        'address' => 'Plot No. 00, VKI Area, Jaipur, Rajasthan - 302013',
    ],

    'branches' => [
        [
            'city' => 'Jodhpur',
            'label' => 'Branch Office',
            'address' => 'Plot No. 00, Industrial Area, Jodhpur, Rajasthan - 342001',
        ],
    ],

    'departments' => [
        [
            'title' => 'Sales Enquiries',
            'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
            'email' => 'sales@agarwalbrothers.com',
            'phone' => '+91 00000 00001',
        ],
        [
            'title' => 'Service & Technical Support',
            'icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z',
            'email' => 'service@agarwalbrothers.com',
            'phone' => '+91 00000 00002',
        ],
    ],

    'mail_24x7' => 'info@agarwalbrothers.com',
    'call' => '+91 00000 00000',

    'working_days' => [
        'Mon-Fri: 09:30 am - 05:30 pm',
        '1st & 3rd Sat: 09:30 am - 01:30 pm',
        '2nd & 4th Saturday and Sunday: Holiday',
    ],
];
