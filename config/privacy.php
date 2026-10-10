<?php

/*
|--------------------------------------------------------------------------
| Privacy policy details
|--------------------------------------------------------------------------
| Shown on /privacy-policy. Update these when the company decides its
| retention periods. Have the final wording reviewed by a legal adviser.
*/

return [

    'updated' => '10 October 2026',

    // People whose enquiries / applications are kept this long (in months), unless a longer period is required by law.
    'retention' => [
        'enquiries' => 24,
        'applications' => 12,
    ],

    // Where visitors send access / correction / deletion requests.
    'contact_email' => env('PRIVACY_EMAIL', env('CONTACT_INBOX', 'info@agarwalbrothers.com')),
];
