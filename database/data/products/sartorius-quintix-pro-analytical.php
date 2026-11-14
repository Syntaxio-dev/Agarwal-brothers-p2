<?php

/**
 * Sartorius Quintix Pro Analytical - ONE product for the six Quintix Pro analytical models (specs as ranges / per-model text).
 *
 * Values: Sartorius "Quintix Pro Product Datasheet" (14 Feb 2024, page 4 "Model specific data, models with internal adjustment":
 * QTX324Ixy-1z, QTX224Ixy-1z, QTX124Ixy-1z) and the "Quintix Pro: Redefining the Standard" brochure.
 * Model code legend from the datasheet: x = R real-time level support / M motorized, automatic level feet; y = U manual glass draft shield /
 * O no draft shield; z = S standard balances (country markings otherwise). The owner's pasted list of six models matched the datasheet.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'Quintix Pro Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius Quintix Pro analytical balances weigh 120 g, 220 g or 320 g with 0.1 mg readability and a typical stabilization time of 1.5 seconds or less. A large 7-inch full-touch graphic display, isoCAL internal adjustment, LevelControl with real-time level support or motorized automatic levelling, user management and 15 built-in applications make this premium standard balance universal, from routine weighing to regulated work and cleanrooms.';

$overview = '<p>The <strong>Sartorius Quintix Pro Analytical Balances</strong> are standard laboratory balances with premium features: a large, 7-inch, high-resolution full-touch graphic display, internal adjustment with isoCAL and a choice between real-time level support and fully motorized levelling. They read to <strong>0.1 mg</strong> and are designed to be a true universal balance for research, quality control, pharma and education.</p>'
    . '<p><strong>Six models</strong></p>'
    . '<ul>'
    . '<li><p><strong>QTX324IRU-1S / QTX324IMU-1S:</strong> capacity 320 g.</p></li>'
    . '<li><p><strong>QTX224IRU-1S / QTX224IMU-1S:</strong> capacity 220 g.</p></li>'
    . '<li><p><strong>QTX124IRU-1S / QTX124IMU-1S:</strong> capacity 120 g.</p></li>'
    . '</ul>'
    . '<p>In the model code, I means internal adjustment (isoCAL), R means real-time level support, M means motorized automatic level feet and U means a manual glass draft shield.</p>'
    . '<p><strong>Correct levelling, every time</strong></p>'
    . '<p>A level indicator shows at a glance whether the balance is level. The LevelControl function monitors the level status continuously in real time; if it detects a tilt it alerts you, temporarily blocks data transfer to a printer or computer and guides you through levelling. The motorized models level themselves fully automatically at the push of a button.</p>'
    . '<p><strong>Easy to use and fully documented</strong></p>'
    . '<ul>'
    . '<li><p>A startup assistant, a built-in interactive manual and 15 languages make setup and daily use quick.</p></li>'
    . '<li><p>User management assigns individual roles and passcode protection, and the Device-Log records calibration, levelling and settings changes with date and time stamps.</p></li>'
    . '<li><p>Fifteen integrated applications cover common tasks such as dosing, counting, percentage weighing, density determination, statistics, checkweighing and pipette smart check.</p></li>'
    . '</ul>'
    . '<p><strong>Connectivity and robustness</strong></p>'
    . '<p>Three USB Type-C interfaces, a 9-pin RS232 interface and an Ethernet interface connect printers, computers, USB sticks and databases. Parts made of PBT, glass and stainless steel resist chemicals and support routine cleaning, and the balance is tested for use in cleanrooms of grade A and B and in special environments such as vacuum, argon and nitrogen.</p>'
    . '<p>All models have a Ø 90 mm weighing pan and a weighing chamber 240 mm high, and measure 220 × 377 × 346 mm (W × D × H). Our team can help you choose between level support and motorized levelling and the right accessories.</p>';

$features = [
    ['title' => '7-inch full-touch graphic display', 'text' => 'A large, high-resolution display with a modern interface, a status center and a built-in interactive manual.'],
    ['title' => 'LevelControl: level support or motorized levelling', 'text' => 'Real-time level monitoring with alerts that block data transfer on tilt (R models), or motorized feet that level the balance automatically (M models).'],
    ['title' => 'isoCAL and Device-Log', 'text' => 'Temperature- and time-controlled internal adjustment, with every calibration, levelling and settings change recorded with date and time.'],
    ['title' => 'User management and safety levels', 'text' => 'Individual user roles with passcode protection and high, standard or low monitoring levels for secure data transfer.'],
    ['title' => '15 applications and rich connectivity', 'text' => 'Fifteen built-in applications, three USB Type-C ports, RS232 and Ethernet for printers, PCs and databases.'],
];

$advantages = [
    ['title' => 'Always correctly levelled', 'text' => 'LevelControl and the optional motorized feet protect your measurements from tilt errors.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time of 1.5 seconds or less and typical measurement time of 2 seconds or less.'],
    ['title' => 'Suited to special environments', 'text' => 'Tested for cleanrooms of grade A and B, vacuum, argon and nitrogen, with chemical-resistant surfaces that are easy to clean.'],
    ['title' => 'Flexible and efficient', 'text' => 'The semi-automatic filter adaption (AFA) adapts the balance to your environment with one click, and power-saving modes and a start timer reduce energy use.'],
    ['title' => 'Quick to learn', 'text' => 'The startup assistant, 15 languages and the built-in manual help new users start right away.'],
];

$faqs = [
    ['question' => 'What is the difference between the IRU and IMU models?', 'answer' => 'IRU models have real-time level support: the balance monitors its level continuously and guides you through levelling. IMU models have motorized, automatic level feet and level themselves at the push of a button.'],
    ['question' => 'Which capacities are available?', 'answer' => 'Three analytical capacities, each with 0.1 mg readability: 320 g (QTX324), 220 g (QTX224) and 120 g (QTX124).'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal adjustment (a temperature change of 1.5 K or a time interval of 4 hours, which you can customize). The recommended external test weight is 200 g for the 324 and 224 models and 100 g for the 124.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'According to USP chapter 41 the optimum minimum initial weighing is 0.082 g and the typical minimum is 0.16 g.'],
    ['question' => 'Which interfaces does it have?', 'answer' => 'Three USB Type-C interfaces (two at the back, one at the front), a 9-pin RS232 interface and an Ethernet interface. The PC-Direct feature sends results to a computer without extra software.'],
    ['question' => 'Can it be used in a cleanroom?', 'answer' => 'Yes. The balance is tested for use in cleanrooms of grade A and B and in special environments such as vacuum, argon and nitrogen.'],
    ['question' => 'Can access to the balance be controlled?', 'answer' => 'Yes. User management lets you assign individual roles to each user with passcode protection and limits menu access to authorized staff.'],
];

$documents = [
    ['title' => 'Quintix Pro Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGPD.pdf'],
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
        ['model' => 'Quintix Pro Analytical', 'heading' => 'Quintix Pro Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'QTX324IRU-1S, QTX324IMU-1S, QTX224IRU-1S, QTX224IMU-1S, QTX124IRU-1S, QTX124IMU-1S',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '120 to 320 (QTX124: 120 / QTX224: 220 / QTX324: 320)',
            'Pan size (mm)'                  => 'Ø 90',
            'Repeatability at 5% load, typical (mg)' => '± 0.08',
            'Repeatability near Max, typical (mg)' => '± 0.1',
            'Linearity, typical (mg)'        => '± 0.06 (limit ± 0.2)',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '1',
            'Stabilization time, typical (sec.)' => '≤ 1.5',
            'Measurement time, typical (sec.)' => '≤ 2.0',
            'Minimum weight, USP, optimum (g)' => '0.082',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Recommended external test weight (g)' => '100 to 200 (class E2)',
            'Levelling'                      => 'R: real-time level support / M: motorized, automatic level feet',
            'Draft shield'                   => 'U: manual glass',
            'Display'                        => 'Intuitive graphic touch (7-inch)',
            'Interfaces'                     => '3 × USB Type-C, RS232 (9-pin), Ethernet',
            'Weighing chamber height (mm)'   => '240',
            'Dimensions, W × D × H (mm)'     => '220 × 377 × 346',
        ]],
    ],
];
