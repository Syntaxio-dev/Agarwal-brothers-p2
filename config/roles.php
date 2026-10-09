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

];
