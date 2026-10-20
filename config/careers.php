<?php

/*
|--------------------------------------------------------------------------
| Roles shown in the "Position" dropdown of the general application form (/careers)
|--------------------------------------------------------------------------
| Grouped by department. Picking a role fills the Department field for the visitor.
| Add or rename a role here and the dropdown follows; visitors can also choose "Other" and type their own role.
| (A specific job page, /careers/{job}, always applies to that job, so its role stays fixed there.)
*/

return [

    // "What happens after you apply" on the Careers page. No timelines are promised on purpose.
    'process' => [
        ['title' => 'Apply online', 'text' => 'Choose a role and send your details and resume, or share your profile if no role fits.', 'icon' => 'M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5'],
        ['title' => 'We review your profile', 'text' => 'Our team reads every application and shortlists the profiles that fit the role.', 'icon' => 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z'],
        ['title' => 'Interview', 'text' => 'Shortlisted candidates are contacted by phone or email to talk to our team.', 'icon' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM12 20.25c4.97 0 9-3.694 9-8.25S16.97 3.75 12 3.75 3 7.444 3 12c0 1.74.59 3.352 1.595 4.68L3.75 20.25l3.67-.905A9.4 9.4 0 0 0 12 20.25Z'],
        ['title' => 'Offer and joining', 'text' => 'Selected candidates receive an offer, and we help them settle into the team.', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ],


    'roles' => [
        'Sales & Marketing' => [
            'Sales Representative',
            'Sales Executive (Field Sales)',
            'Business Development Executive',
            'Telecaller / Tele-sales Executive',
            'Social Media Executive',
            'Digital Marketing Executive',
            'Graphic Designer / Content Creator',
        ],
        'Office & Support' => [
            'Back Office Executive',
            'Receptionist / Front Office Executive',
            'Customer Support Executive',
            'Accounts Executive',
            'HR Executive',
            'Office Assistant',
        ],
        'Technical & Service' => [
            'Service Engineer (Installation & Calibration)',
            'Application Specialist',
            'Lab Technician / Trainee',
        ],
        'Operations' => [
            'Purchase Executive',
            'Logistics & Dispatch Executive',
            'Store & Warehouse Executive',
            'Delivery Executive',
        ],
    ],

    // the value sent when the visitor picks "Other" and types their own role
    'other' => '__other',
];
