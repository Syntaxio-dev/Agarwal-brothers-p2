<?php

/*
|--------------------------------------------------------------------------
| Admin panel roles
|--------------------------------------------------------------------------
| 'access' lists the admin areas a role can open and change. "*" means all.
| Area keys match the $accessKey of each Filament resource/page.
| Users, Site Settings and Backups are never granted to anyone but admins.
*/

return [

    'admin' => [
        'label' => 'Administrator',
        'description' => 'Full access, including users, site settings and backups.',
        'access' => ['*'],
    ],

    'editor' => [
        'label' => 'Content editor',
        'description' => 'Manages website content: catalogue, blogs, news, webinars, slides, team and reviews.',
        'access' => [
            'slides', 'clients', 'reviews', 'team-members', 'insights', 'application-resources',
            'verticals', 'brands', 'countries', 'categories', 'products',
        ],
    ],

    'sales' => [
        'label' => 'Sales',
        'description' => 'Handles product enquiries and contact messages, and can update products.',
        'access' => ['enquiries', 'contact-messages', 'products'],
    ],

    'hr' => [
        'label' => 'HR',
        'description' => 'Manages job openings and reviews job applications.',
        'access' => ['job-openings', 'job-applications'],
    ],

    // Field sales team (Sales module). Office staff who handle enquiries are the "sales" role above, not this one.
    // The field rep (`field_rep`) is deliberately NOT listed: a role listed here can sign in to the admin panel,
    // and reps use the phone app only.
    'sales_manager' => [
        'label' => 'Sales team manager',
        'description' => 'Sees every field rep\'s expenses, meetings and clients; approves expenses; adds and switches reps.',
        'access' => ['sales-dashboard', 'sales-reps', 'sales-expenses', 'sales-meetings', 'sales-clients', 'sales-calls', 'sales-positions', 'sales-enquiries', 'sales-enquiries-move', 'sales-reports', 'sales-products', 'sales-product-requests'],
    ],

];
