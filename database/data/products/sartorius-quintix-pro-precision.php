<?php

/**
 * Sartorius Quintix Pro Precision - ONE product for the Quintix Pro precision models (specs as ranges / per-group text).
 *
 * Values: Sartorius "Quintix Pro Product Datasheet" (14 Feb 2024), pages 5-7: 1 mg models 1503, 1203, 623, 423, 323; 10 mg models 6202, 4202,
 * 3202, 2202, 1202; 100 mg models 12201, 10201, 8201, 6201, 3201 (each as QTX...Ixy-1z). Code legend from the datasheet: x = R real-time level
 * support / M motorized automatic level feet; y = U manual glass draft shield / O no draft shield; z = S standard. The owner's pasted list
 * matched the datasheet. Draft shield by group: the 1 mg models have a weighing chamber 240 mm high (draft shield, U); the 10 mg and
 * 100 mg models have an open 182 × 182 mm pan (O).
 */

$category = 'Precision Lab Balances';
$brand    = 'Sartorius';
$group    = 'Quintix Pro Series';
$type     = 'Precision Balance';

$shortDescription = 'The Sartorius Quintix Pro precision balances weigh from 320 g up to 12,200 g with 1 mg, 10 mg or 100 mg readability and a typical stabilization time of 1.0 second or less. A large 7-inch full-touch graphic display, isoCAL internal adjustment, LevelControl with real-time level support or motorized automatic levelling, user management and 15 built-in applications make this premium standard balance universal for everyday weighing.';

$overview = '<p>The <strong>Sartorius Quintix Pro Precision Balances</strong> are standard laboratory balances with premium features: a large, 7-inch, high-resolution full-touch graphic display, internal adjustment with isoCAL and a choice between real-time level support and fully motorized levelling. Fifteen models cover capacities from <strong>320 g to 12,200 g</strong>, each with R (level support) or M (motorized levelling) versions.</p>'
    . '<p><strong>1 mg models with draft shield</strong> (Ø 120 mm pan, manual glass draft shield)</p>'
    . '<ul>'
    . '<li><p><strong>QTX1503:</strong> 1,500 g. <strong>QTX1203:</strong> 1,200 g. <strong>QTX623:</strong> 620 g. <strong>QTX423:</strong> 420 g. <strong>QTX323:</strong> 320 g.</p></li>'
    . '</ul>'
    . '<p><strong>10 mg models with open pan</strong> (182 × 182 mm pan)</p>'
    . '<ul>'
    . '<li><p><strong>QTX6202:</strong> 6,200 g. <strong>QTX4202:</strong> 4,200 g. <strong>QTX3202:</strong> 3,200 g. <strong>QTX2202:</strong> 2,200 g. <strong>QTX1202:</strong> 1,200 g.</p></li>'
    . '</ul>'
    . '<p><strong>100 mg models with open pan</strong> (182 × 182 mm pan)</p>'
    . '<ul>'
    . '<li><p><strong>QTX12201:</strong> 12,200 g. <strong>QTX10201:</strong> 10,200 g. <strong>QTX8201:</strong> 8,200 g. <strong>QTX6201:</strong> 6,200 g. <strong>QTX3201:</strong> 3,200 g.</p></li>'
    . '</ul>'
    . '<p>In the model code, I means internal adjustment (isoCAL), R means real-time level support, M means motorized automatic level feet, U means a manual glass draft shield and O means no draft shield.</p>'
    . '<p><strong>Correct levelling, every time</strong></p>'
    . '<p>A level indicator shows at a glance whether the balance is level. The LevelControl function monitors the level status continuously in real time; if it detects a tilt it alerts you, temporarily blocks data transfer to a printer or computer and guides you through levelling. The motorized models level themselves fully automatically at the push of a button.</p>'
    . '<p><strong>Easy to use and fully documented</strong></p>'
    . '<ul>'
    . '<li><p>A startup assistant, a built-in interactive manual and 15 languages make setup and daily use quick.</p></li>'
    . '<li><p>User management assigns individual roles and passcode protection, and the Device-Log records calibration, levelling and settings changes with date and time stamps.</p></li>'
    . '<li><p>Fifteen integrated applications cover tasks such as dosing, counting, percentage weighing, density determination, statistics, checkweighing and pipette smart check.</p></li>'
    . '</ul>'
    . '<p><strong>Connectivity and robustness</strong></p>'
    . '<p>Three USB Type-C interfaces, a 9-pin RS232 interface and an Ethernet interface connect printers, computers, USB sticks and databases. Parts made of PBT, glass and stainless steel resist chemicals and support routine cleaning, and the balance is tested for use in cleanrooms of grade A and B and in special environments such as vacuum, argon and nitrogen. Our team can help you choose the right model, levelling option and accessories.</p>';

$features = [
    ['title' => '7-inch full-touch graphic display', 'text' => 'A large, high-resolution display with a modern interface, a status center and a built-in interactive manual.'],
    ['title' => 'LevelControl: level support or motorized levelling', 'text' => 'Real-time level monitoring with alerts that block data transfer on tilt (R models), or motorized feet that level the balance automatically (M models).'],
    ['title' => 'isoCAL and Device-Log', 'text' => 'Temperature- and time-controlled internal adjustment, with every calibration, levelling and settings change recorded with date and time.'],
    ['title' => 'User management and safety levels', 'text' => 'Individual user roles with passcode protection and high, standard or low monitoring levels for secure data transfer.'],
    ['title' => '15 applications and rich connectivity', 'text' => 'Fifteen built-in applications, three USB Type-C ports, RS232 and Ethernet for printers, PCs and databases.'],
];

$advantages = [
    ['title' => 'Always correctly levelled', 'text' => 'LevelControl and the optional motorized feet protect your measurements from tilt errors.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time of 1.0 second or less and typical measurement time of 1.5 seconds or less.'],
    ['title' => 'Suited to special environments', 'text' => 'Tested for cleanrooms of grade A and B, vacuum, argon and nitrogen, with chemical-resistant surfaces that are easy to clean.'],
    ['title' => 'Flexible and efficient', 'text' => 'The semi-automatic filter adaption (AFA) adapts the balance to your environment with one click, and power-saving modes and a start timer reduce energy use.'],
    ['title' => 'Quick to learn', 'text' => 'The startup assistant, 15 languages and the built-in manual help new users start right away.'],
];

$faqs = [
    ['question' => 'What is the difference between the R and M models?', 'answer' => 'R models have real-time level support: the balance monitors its level continuously and guides you through levelling. M models have motorized, automatic level feet and level themselves at the push of a button.'],
    ['question' => 'Which capacities and readability are available?', 'answer' => 'Five models at 1 mg (320 g to 1,500 g), five at 10 mg (1,200 g to 6,200 g) and five at 100 mg (3,200 g to 12,200 g).'],
    ['question' => 'Which models have a draft shield?', 'answer' => 'The 1 mg models have a manual glass draft shield (U) and a weighing chamber 240 mm high. The 10 mg and 100 mg models have an open 182 × 182 mm pan without a draft shield (O).'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal adjustment (a temperature change of 1.5 to 2 K or a time interval of 4 to 6 hours, which you can customize).'],
    ['question' => 'Which interfaces does it have?', 'answer' => 'Three USB Type-C interfaces, a 9-pin RS232 interface and an Ethernet interface. The PC-Direct feature sends results to a computer without extra software.'],
    ['question' => 'Can it be used in a cleanroom?', 'answer' => 'Yes. The balance is tested for use in cleanrooms of grade A and B and in special environments such as vacuum, argon and nitrogen.'],
    ['question' => 'Can access to the balance be controlled?', 'answer' => 'Yes. User management lets you assign individual roles to each user with passcode protection and limits menu access to authorized staff.'],
];

$documents = [
    ['title' => 'Quintix Pro Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC8N6V7EZR8X2ZN1TF6FGQD.pdf'],
];

return [
    'category'          => $category,
    'brand'             => $brand,
    'model_group'       => $group,
    'product_type'      => $type,
    'specs_general'     => true,
    'short_description' => $shortDescription,
    'overview'          => $overview,
    'features'          => $features,
    'advantages'        => $advantages,
    'faqs'              => $faqs,
    'documents'         => $documents,

    'models' => [
        ['model' => 'Quintix Pro Precision', 'heading' => 'Quintix Pro Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => '1 mg: QTX1503, QTX1203, QTX623, QTX423, QTX323 (I-R/M-U-1S) / 10 mg: QTX6202, QTX4202, QTX3202, QTX2202, QTX1202 (I-R/M-O-1S) / 100 mg: QTX12201, QTX10201, QTX8201, QTX6201, QTX3201 (I-R/M-O-1S)',
            'Readability (mg)'               => '1 / 10 / 100',
            'Weighing capacity (g)'          => '320 to 12,200',
            'Pan size (mm)'                  => 'Ø 120 (1 mg models) / 182 × 182 (10 mg and 100 mg models)',
            'Repeatability at 5% load, typical (mg)' => '± 0.5 (1 mg) / ± 5 (10 mg) / ± 50 (100 mg)',
            'Linearity, typical (mg)'        => '± 0.6 (1 mg) / ± 6 (10 mg) / ± 60 (100 mg)',
            'Stabilization time, typical (sec.)' => '≤ 1.0 (1 mg models) / ≤ 0.9 (10 mg and 100 mg models)',
            'Measurement time, typical (sec.)' => '≤ 1.5 (1 mg models) / ≤ 1.0 (10 mg and 100 mg models)',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Levelling'                      => 'R: real-time level support / M: motorized, automatic level feet',
            'Draft shield'                   => 'U: manual glass (1 mg models) / O: none, open pan',
            'Display'                        => 'Intuitive graphic touch (7-inch)',
            'Interfaces'                     => '3 × USB Type-C, RS232 (9-pin), Ethernet',
            'Dimensions, W × D × H (mm)'     => '220 × 377 × 346 (1 mg models) / 215 × 377 × 95 (10 mg and 100 mg models)',
        ]],
    ],
];
