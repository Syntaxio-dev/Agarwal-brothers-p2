<?php

/*
|--------------------------------------------------------------------------
| Sales module (field team app + manager screens)
|--------------------------------------------------------------------------
| Everything here can be changed from .env without touching code.
| The module is self-contained: to move it to its own sub-domain, take app/Sales, routes/sales-api.php, this file and the
| sales_ tables along, and point the app at the new address.
*/

return [

    // All business rules are checked in this timezone; the database stores UTC.
    'timezone' => env('SALES_TIMEZONE', 'Asia/Kolkata'),

    // App login tokens expire after this many days (the rep signs in again).
    'token_days' => (int) env('SALES_TOKEN_DAYS', 30),

    // A record dated further ahead than this many days (in the business timezone) is rejected.
    'max_future_days' => (int) env('SALES_MAX_FUTURE_DAYS', 1),

    // Largest amount one expense line may have, in rupees.
    'max_expense_rupees' => (int) env('SALES_MAX_EXPENSE', 1000000),

    // Bill photo limit in KB (5 MB) and the picture types that are accepted (judged by the file's real content).
    'bill_max_kb' => 5120,

    // A rep whose last sync is older than this many hours shows as "pending sync" in the admin.
    'stale_sync_hours' => (int) env('SALES_STALE_SYNC_HOURS', 24),

    // One push may carry at most this many records of each kind (the app sends bigger backlogs in several pushes).
    'max_push_records' => 300,

    // One pull returns at most this many records of each kind (the app asks again while "has_more" is true).
    'max_pull_records' => 1000,

    'expense_categories' => ['travel', 'food', 'fuel', 'parking', 'gifting', 'lodging', 'other'],

    // The Reps screens: a daily report is expected from this hour (business time) on the working days (ISO weekdays, 1 = Monday),
    // and a phone that runs an app older than this version is marked "update".
    'report_hour' => (int) env('SALES_REPORT_HOUR', 18),
    'working_days' => [1, 2, 3, 4, 5, 6],
    'min_app_version' => env('SALES_MIN_APP_VERSION', '1.7'),

    // Field reps never get a real mailbox: users.email must be unique, so a placeholder is made from the employee code.
    'rep_email_domain' => 'reps.invalid',

    // Browser origins that may call the API during development: see config/cors.php (SALES_CORS_ORIGINS, empty in production).
];
