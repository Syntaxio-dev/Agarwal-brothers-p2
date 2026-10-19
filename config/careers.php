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
