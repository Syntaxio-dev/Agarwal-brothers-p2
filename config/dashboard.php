<?php

return [

    // A customer should get a first answer within this many hours (shown against the average on the dashboard).
    'response_target_hours' => 24,

    // Live products not edited for this many months are listed as "stale".
    'stale_months' => 6,

    // The date-range choices on the dashboard, in days.
    'ranges' => [7, 30, 90],
    'default_range' => 30,

];
