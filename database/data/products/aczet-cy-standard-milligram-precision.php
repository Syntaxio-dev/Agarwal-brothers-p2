<?php

/**
 * Aczet CY Series Milligram Precision Balance (0.001 g, external calibration) - ONE product for the whole line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet datasheet "Precision Balances - CY Series" https://aczet.com/upload/product_documentation/Precision_Balances.pdf  <- attached as the document
 *    (2 pages, both rendered and read; page 2 is this product, page 1 is the 0.01 g CY models: CY 1202, 2202, 3102, 4102, 6102, 2202C, 6102C).
 *  - Picture: https://aczet.com/upload/product_description/Precision-Balance-(CY)4.jpg (label on the balance: CY 223, Max 220 g, d 0.001 g).
 *  The aczet.com 'Precision Balance' web page (under Jewellery Balances) shows only the CY-C 6102C table, and the other precision menu pages
 *  (top pan, high precision, CZ single block) are other products, so the datasheet is the only source of the 0.001 g CY table. No web table to compare.
 *
 * Models found: CY 223, CY 323, CY 513, CY 723, CY 1003 = 5 models, the same as the pasted list. Extra codes from the brief (123, 363, 423, 623, 2003,
 * 3003) are NOT in the datasheet and were left out.
 *
 * Doubts / notes (owner to confirm):
 *  - USP minimum weight is printed '1gm' for all five models (taken as typed).
 *  - Pan size: 110 mm diameter for CY 223, 323, 513, 723 and 128 x 128 mm for CY 1003.
 *  - Datasheet text spells 'Briliant' and 'Backlite'; corrected here. Datasheet says 'DC Adaptor ... output 13V / 15.A' (typo for 1.5 A, as in the other CY datasheets).
 *  - The datasheet has an 'Alpha Numeric Backlite LCD Display' and 'External Calibration' only: no touch screen and no internal calibration are claimed.
 *  - IP rating, USP chapters 41 / 1251 compliance and PSAC are NOT stated for this series, so they are not claimed (the datasheet's compliance line is 'ISO, GLP / GMP').
 *  - The datasheet's feature texts (Mono-Tech sensor, capacity tracker, 15 mm digits, RS 232C and optional USB) are used.
 *  - The balance in the picture is the CY 223 with a glass draft shield; the datasheet does not say which models have a shield, but the one chamber height
 *    (158.5 mm) is printed for all five.
 *  - Sensitivity drift is printed '± 1ppm (1 x 10^-6 / °C)'; used as '± 1 ppm/°C'.
 * Nothing is claimed about Agarwal Brothers.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CY Series';
$type     = 'Milligram Precision Balance';

$shortDescription = 'The Aczet CY Series Milligram Precision Balances read to 0.001 g (1 mg) and cover five capacities from 220 g to 1,000 g: CY 223, CY 323, CY 513, CY 723 and CY 1003. A Mono-Tech weigh sensor, a backlit alphanumeric LCD with 15 mm digits, a glass draft shield, a stainless-steel pan and external calibration make them practical for everyday milligram weighing in laboratories and quality control.';

$overview = '<p>The <strong>Aczet CY Series Milligram Precision Balance</strong> range is built for routine laboratory weighing where a reading to 0.001 g (1 mg) is needed. Five models share one compact body: the CY 223 (220 g), CY 323 (320 g), CY 513 (510 g), CY 723 (720 g) and CY 1003 (1,000 g).</p>'
    . '<p><strong>Five models, one body</strong></p>'
    . '<ul>'
    . '<li><p>All five read 0.001 g with 0.001 g repeatability and 0.002 g linearity.</p></li>'
    . '<li><p>The CY 223, CY 323, CY 513 and CY 723 have a 110 mm diameter pan, and the CY 1003 a 128 x 128 mm pan.</p></li>'
    . '<li><p>Minimum load is 20 mg and the response time is 2 to 3 seconds on every model.</p></li>'
    . '</ul>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>Aczet builds the series around its Mono-Tech weigh sensor, designed to meet high international standards. The balances are calibrated with an external test weight, which keeps the instrument simple and the price reasonable for laboratories that calibrate on a schedule. A capacity tracker built into the display lets you watch the load and avoid overloading the weighing cell.</p>'
    . '<p><strong>Built for daily use</strong></p>'
    . '<p>The body is durable ABS, and the large stainless-steel weighing pan is quick to clean. A level indicator and adjustable feet make setting up the balance easy. The high-contrast backlit alphanumeric LCD has 15 mm digits that are easy to read under any room lighting.</p>'
    . '<p><strong>Applications and connectivity</strong></p>'
    . '<p>Application modes include piece counting, percentage weighing, animal / dynamic weighing, check weighing and density determination. Many units of measure are available, such as mg, g, kg, lb, ct, GN, mo, oz and dwt. A bidirectional RS 232C interface connects the balance to a PC or printer, with USB as an option, and the series is listed as ISO, GLP / GMP compliant.</p>'
    . '<p>The balance runs on a DC adaptor for 100 to 240 V mains at 50 / 60 Hz, works in an ambient range of 15 to 35 degrees C and weighs about 7.5 kg, so it sits comfortably on a normal laboratory bench.</p>'
    . '<p>The table on this page shows ranges for the whole CY series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Five capacities, 0.001 g readability', 'text' => 'CY 223, 323, 513, 723 and 1003 cover 220 g to 1,000 g, all reading to 1 mg.'],
    ['title' => 'Mono-Tech weigh sensor', 'text' => 'Aczet\'s Mono-Tech sensor is designed to match high international standards for stable weighing.'],
    ['title' => 'Backlit LCD with 15 mm digits', 'text' => 'A high-contrast backlit display that stays easy to read in any room lighting.'],
    ['title' => 'Capacity tracker', 'text' => 'The display shows the load so you can avoid overloading the weighing cell.'],
    ['title' => 'RS 232C and optional USB', 'text' => 'A bidirectional data interface connects to a PC or printer, with USB as an option.'],
];

$advantages = [
    ['title' => 'Right capacity, same readability', 'text' => 'Choose from 220 g to 1,000 g and keep a 1 mg reading on every model.'],
    ['title' => 'Simple external calibration', 'text' => 'Calibrating with an external test weight keeps the balance straightforward to operate.'],
    ['title' => 'Easy to clean', 'text' => 'The durable ABS housing and the large stainless-steel pan wipe clean quickly.'],
    ['title' => 'Quick set-up', 'text' => 'A level indicator and adjustable feet help you level the balance correctly.'],
    ['title' => 'Everyday applications', 'text' => 'Piece counting, percentage, animal / dynamic and check weighing and density determination are built in.'],
];

$faqs = [
    ['question' => 'Which models are in the CY milligram series?', 'answer' => 'The Aczet datasheet lists five models: CY 223 (220 g), CY 323 (320 g), CY 513 (510 g), CY 723 (720 g) and CY 1003 (1,000 g). All read to 0.001 g.'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability (standard deviation) is 0.001 g and linearity is 0.002 g on all five models. Minimum load is 20 mg.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'These models use external calibration with a test weight. Aczet does not list internal calibration for this series, so keep a suitable calibration weight available.'],
    ['question' => 'What is the pan size?', 'answer' => 'The CY 223, CY 323, CY 513 and CY 723 have a 110 mm diameter pan. The CY 1003 has a 128 x 128 mm pan.'],
    ['question' => 'How is it different from the CZ and CY-C series?', 'answer' => 'The CY milligram series reads 0.001 g with external calibration and an alphanumeric backlit LCD. The CY-C Series uses motorised internal calibration, and the CZ Series is a different single-block design. Ask our team if you are unsure which one suits you.'],
    ['question' => 'Can it be connected to a computer or printer?', 'answer' => 'Yes. It has an RS 232C data interface, and USB is available as an option.'],
    ['question' => 'Which weighing modes does it have?', 'answer' => 'Piece counting, percentage weighing, animal / dynamic weighing, check weighing and density determination, with units such as mg, g, kg, lb, ct, GN, mo, oz and dwt.'],
    ['question' => 'What is the operating temperature range?', 'answer' => 'Aczet gives an operating temperature of 15 to 35 degrees C, with a sensitivity drift of 1 ppm per degree C.'],
];

$documents = [
    ['title' => 'CY Series Precision Balances Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DS1.pdf'],
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
        ['model' => 'CY Series Milligram Precision Balance', 'heading' => 'CY Series Milligram Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CY 223, CY 323, CY 513, CY 723, CY 1003',
            'Readability (g)'                => '0.001',
            'Weighing capacity (g)'          => '220 to 1,000 (CY 223: 220; CY 323: 320; CY 513: 510; CY 723: 720; CY 1003: 1,000)',
            'Minimum load (mg)'              => '20',
            'Repeatability, sd (g)'          => '0.001',
            'Linearity (g)'                  => '± 0.002',
            'Tare range, subtractive (g)'    => 'Up to -220 to -1,000 (equal to the capacity of each model)',
            'USP minimum weight (g)'         => '1 (typical, USP Chapter 41)',
            'Pan size (mm)'                  => 'Ø 110 (CY 223, 323, 513, 723); 128 × 128 (CY 1003)',
            'Response time (sec.)'           => '2 to 3',
            'Display'                        => 'Alphanumeric backlit LCD, 15 mm digits',
            'Calibration and adjustment'     => 'External calibration',
            'Units'                          => 'mg, g, kg, lb, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'     => '15 to 35',
            'Sensitivity drift (ppm/°C)'     => '± 1',
            'Interface'                      => 'RS 232C, USB optional',
            'Application modes'              => 'PCS, % weighing, animal / dynamic weighing, check weighing, density determination',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V, 50 / 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP',
            'Balance size, W × D × H (mm)'   => '342.5 × 212 × 341 (chamber height: 158.5)',
            'Weight, approx. (kg)'           => '7.5',
        ]],
    ],
];
