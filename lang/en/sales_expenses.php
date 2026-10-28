<?php

return [
    // The quick reasons of the reject window: the rep reads the text on the phone.
    'reasons' => [
        'missing' => 'Bill is missing',
        'unreadable' => 'Bill is not readable',
        'too_high' => 'Amount looks too high',
        'category' => 'Wrong category',
        'date' => 'Wrong date',
    ],
    // Said under the reason box (they/them: no guess about anybody's pronouns).
    'locked_note' => 'A reviewed expense is locked on the phone too. :name adds a new expense if they have the bill.',
];
