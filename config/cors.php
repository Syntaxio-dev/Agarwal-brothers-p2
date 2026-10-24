<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing
|--------------------------------------------------------------------------
| Only the Sales API is reachable from another origin, and only from the origins listed in SALES_CORS_ORIGINS
| (default: https://localhost, the origin the Android app's WebView runs under; add the Vite dev server address while
| developing). Tokens travel in the Authorization header, never in cookies, so credentials are not allowed, and CORS is no
| security boundary here: every call still needs a valid token.
*/

return [
    'paths' => ['api/sales/*'],
    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],
    // read from the environment directly: config files load in alphabetical order, so config('sales') is not ready yet
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('SALES_CORS_ORIGINS', 'https://localhost'))))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Authorization', 'Content-Type', 'Accept', 'X-App-Version'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => false,
];
