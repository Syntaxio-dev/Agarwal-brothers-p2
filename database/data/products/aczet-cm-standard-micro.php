<?php

/**
 * Aczet CM Series Standard Micro Balance - ONE product for the whole standard CM line, specs as RANGES / per-model values.
 *
 * Sources (read 14 Nov 2026):
 *  - Aczet datasheet "CM Series Micro Balances, Professional Level" (https://aczet.com/upload/product_documentation/Micro_Balance1.pdf; the
 *    link labelled "Micro Balance" on the product page, Micro_Balance11.pdf, returns 404; Micro_Balance.pdf is the same sheet with the ultra-micro UCM 2A added).
 *    The sheet lists CM 2, CM 5, CM 6, CM 11, CM 21 and CM 31.
 *  - https://aczet.com/product/micro-balance (Overview, Products and Specs tab, Documentation). Its spec tables list ONLY CM 2, CM 5, CM 6 and CM 11.
 *  - Aczet "Micro Balance Operating Manual" (Micro_Balance_(Manual).pdf), which covers CM 2, CM 5 and the filter versions CM 2F / CM 5F.
 *
 * Differences from the unverified paste / between Aczet's own sources (owner to know):
 *  - The model list is right: CM 2, CM 5, CM 6, CM 11, CM 21, CM 31 (all on the datasheet). The website's spec tables show only the first four.
 *  - Aczet's website and datasheet disagree on some numbers. The DATASHEET values are used here because the datasheet is attached to the product:
 *      Repeatability (sd): datasheet 0.7 / 1 / 1 / 1.2 / 1.2 / 2 µg (CM 2 ... CM 31); website says ±1 µg (CM 2, 5, 6) and ±1.2 µg (CM 11).
 *      Linearity: datasheet 3 / 5 / 5 / 6 / 7 / 8 µg; website says 3 / 6 / 6 / 6 µg for CM 2 / 5 / 6 / 11.
 *      USP minimum weight: datasheet 2 / 2 / 2 / 2.4 / 2.4 / 4 mg; website says 1 / 2.4 / 2.4 / 2.4 mg (CM 2 / 5 / 6 / 11).
 *      Weight: datasheet 10.20 / 14.70 kg; website 12 kg / 14 kg. Weight is therefore NOT listed (the CM-A datasheet also says 10.20 / 14.70 kg).
 *    Ask Aczet which set is current before switching the product on.
 *  - Known from the site already: CM 2 = 2 g, 1 µg readability, 30 mm pan, 8 - 10 s response (confirmed). The datasheet gives the pan as "15 Ø ~ 30 Ø" for the whole line.
 *  - Power supply: datasheet says A/C adaptor 230 V or 115 V ± 20 %, 50 - 60 Hz; the website says DC adapter input 100 ~ 240 V, output 13 V / 1.5 A. Both are written.
 *  - The operating manual also lists the filter-weighing versions CM 2F and CM 5F; they are a different product (filter micro balance) and are not included here.
 *  - Website text says the technology is electromagnetic force compensation (EMFC); the datasheet says "Mono-Tech". Both are used in the overview.
 *  - Photos: the Aczet product pictures show the LCD / push-button version. The website's "Sensor Micro Balance (Right)" picture (6000 x 4000) looks like a
 *    different electronics unit with a 7-digit display and is used on Aczet's ultra-micro page, so it is NOT used here.
 */

$category = 'Micro Balance';
$brand    = 'Aczet';
$group    = 'CM Series';
$type     = 'Micro Balance';

$shortDescription = 'The Aczet CM series are micro balances with 1 µg (0.001 mg) readability and capacities from 2 g up to 31 g (models CM 2, CM 5, CM 6, CM 11, CM 21 and CM 31). A two-terminal design keeps the electronics apart from the weighing cell, a motorised internal calibration with Perfect Self Auto Calibration (PSAC) keeps results reliable, and a backlit LCD with push buttons and ten built-in applications keep daily work simple. Suitable for pharma, food, research and testing laboratories.';

$overview = '<p>The <strong>Aczet CM Series Micro Balances</strong> are professional-level balances for weighing very small samples with a readability of <strong>1 µg (0.001 mg)</strong>. Six models, <strong>CM 2, CM 5, CM 6, CM 11, CM 21 and CM 31</strong>, cover capacities from <strong>2 g up to 31 g</strong>, so you can choose the capacity that suits your samples and keep the same simple operation.</p>'
    . '<p><strong>Two-terminal design</strong></p>'
    . '<p>One part holds the electronics and display, the other holds the precise mechanical measuring cell with its glass draft shield. Separating them reduces the effect of heat from the electronics on the weighing cell. The draft shield parts can be taken apart and put back easily, which makes cleaning simple.</p>'
    . '<p><strong>Weighing technology and calibration</strong></p>'
    . '<ul>'
    . '<li><p>High-end Mono-Tech weigh sensor based on electromagnetic force compensation (EMFC).</p></li>'
    . '<li><p>Fully automatic motorised internal calibration with <strong>Perfect Self Auto Calibration (PSAC)</strong>: the balance calibrates itself when the temperature changes by a set value or after a set time.</p></li>'
    . '<li><p>Capacity tracker in the display to watch for overloads, and a round bubble level with adjustable feet for quick levelling.</p></li>'
    . '</ul>'
    . '<p><strong>Everyday operation</strong></p>'
    . '<ul>'
    . '<li><p>Bright backlit LCD with large 15 mm digits that stay easy to read in any room lighting.</p></li>'
    . '<li><p>Ten built-in applications such as density determination, check weighing, totalling, percent weighing and formulation.</p></li>'
    . '<li><p>Infrared sensor for automatic opening and closing of the glass shield doors, as listed on the datasheet.</p></li>'
    . '<li><p>Standard RS 232C interface, with an optional USB interface for direct communication with Windows applications such as Microsoft Excel.</p></li>'
    . '<li><p>Built-in real-time clock for dated GLP records in line with ISO, GLP and GMP practice.</p></li>'
    . '</ul>'
    . '<p><strong>Accessories</strong></p>'
    . '<p>An anti-static ionizer (CI-01) removes electrostatic charge from samples and containers, a dot-matrix data printer (CPR 02), anti-vibration weighing table, calibration test weights, a battery backup unit, 21 CFR Part 11 PC software and IQ, OQ and PQ qualification documents are available from Aczet.</p>'
    . '<p>Exact values differ from model to model, so the table on this page shows ranges for the whole CM line. Tell our team what you weigh and how much, and we will recommend the right model and share the exact figures from the Aczet datasheet.</p>';

$features = [
    ['title' => '1 µg readability', 'text' => 'Six models, CM 2 to CM 31, read to 1 µg (0.001 mg) with capacities from 2 g up to 31 g.'],
    ['title' => 'Two-terminal design', 'text' => 'The electronics and display are separate from the mechanical weighing cell, which reduces the influence of heat on the result.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised internal calibration with Perfect Self Auto Calibration (PSAC), triggered by a temperature change or a time interval.'],
    ['title' => 'Clear backlit LCD', 'text' => 'Bright display with 15 mm digits, push-button operation and ten built-in applications such as check weighing and percent weighing.'],
    ['title' => 'Easy data transfer', 'text' => 'Standard RS 232C interface, optional USB with Windows direct communication, and a built-in real-time clock for GLP records.'],
];

$advantages = [
    ['title' => 'Six capacities, one operation', 'text' => 'Choose from 2 g to 31 g and keep the same easy push-button operation and display.'],
    ['title' => 'Quick weighing', 'text' => 'Response time is 8 to 10 seconds, so routine weighing does not slow your work.'],
    ['title' => 'Easy to clean', 'text' => 'The draft shield can be dismantled and assembled easily, which keeps the balance clean.'],
    ['title' => 'Overload awareness', 'text' => 'A capacity tracker on the display shows how close the load is to the maximum capacity.'],
    ['title' => 'Traceable records', 'text' => 'The built-in real-time clock and printer option support dated documentation in line with ISO, GLP and GMP practice.'],
];

$faqs = [
    ['question' => 'Which models are in the CM series and what are their capacities?', 'answer' => 'The Aczet datasheet lists CM 2 (2 g), CM 5 (5 g), CM 6 (6.1 g), CM 11 (11 g), CM 21 (21 g) and CM 31 (31 g). All read to 1 µg (0.001 mg).'],
    ['question' => 'What is the difference between the CM and the CM-A series?', 'answer' => 'The CM series has an alpha-numeric backlit LCD with push buttons. The CM-A series has a 5.7-inch touch screen display and is offered in 5 g, 6.1 g, 11 g and 21 g capacities.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with Perfect Self Auto Calibration (PSAC). The balance calibrates itself when the temperature changes by a set value or after a set time. Calibration with an external test weight is also possible.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The datasheet gives a typical minimum sample weight according to USP chapter 41 of 2 mg for the CM 2, CM 5 and CM 6, 2.4 mg for the CM 11 and CM 21, and 4 mg for the CM 31. It depends on the installation location and environmental conditions.'],
    ['question' => 'Can I connect the balance to a computer or printer?', 'answer' => 'Yes. It has an RS 232C interface, and an optional USB interface allows direct communication with Windows applications such as Microsoft Excel. Aczet also offers a dot-matrix data printer (CPR 02).'],
    ['question' => 'What are the response time and operating temperature?', 'answer' => 'The response time is 8 to 10 seconds and the operating temperature range is 18 °C to 30 °C.'],
    ['question' => 'How do I reduce static charge when weighing small samples?', 'answer' => 'Static charge on samples and containers is a common problem in micro weighing. Aczet offers a free-standing anti-static ionizer, the CI-01, which removes electrostatic charge.'],
    ['question' => 'Where should a micro balance be installed?', 'answer' => 'Indoors in a dry environment on a stable bench, away from vibration and draughts. An anti-vibration weighing table is available as an accessory.'],
];

$documents = [
    ['title' => 'CM Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC9ACZ3M5T8WQ2HNB7RVDXB.pdf'],
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
        ['model' => 'CM Standard Micro', 'heading' => 'CM Series Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                              => 'CM 2, CM 5, CM 6, CM 11, CM 21, CM 31',
            'Readability (µg)'                   => '1 (0.001 mg)',
            'Weighing capacity (g)'              => '2 to 31 (CM 2: 2, CM 5: 5, CM 6: 6.1, CM 11: 11, CM 21: 21, CM 31: 31)',
            'Repeatability, sd (µg)'             => '0.7 to 2 (CM 2: 0.7, CM 5 and CM 6: 1, CM 11 and CM 21: 1.2, CM 31: 2)',
            'Linearity (±µg)'                    => '3 to 8 (CM 2: 3, CM 5 and CM 6: 5, CM 11: 6, CM 21: 7, CM 31: 8)',
            'Tare range (subtractive)'           => 'Full capacity (CM 2: -2 g up to CM 31: -31 g)',
            'USP minimum weight, typical (mg)'   => '2 to 4 (CM 2, CM 5 and CM 6: 2, CM 11 and CM 21: 2.4, CM 31: 4)',
            'Pan size (mm)'                      => 'Ø 15 to Ø 30',
            'Response time (sec.)'               => '8 - 10',
            'Calibration and adjustment'         => 'Motorised internal calibration (PSAC, time and temperature controlled)',
            'Display'                            => 'Alpha-numeric backlit LCD, 15 mm digits',
            'Unit of measure'                    => 'g, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Sensitivity drift (ppm/°C)'         => '± 1 (1 × 10⁻⁶ / °C)',
            'Interface'                          => 'RS 232C (USB, Windows direct communication, optional)',
            'Application modes'                  => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Operating temperature (°C)'         => '18 to 30',
            'Power supply'                       => 'A/C adaptor 230 V or 115 V ± 20 %, 50 - 60 Hz (output 13 V DC / 1.5 A)',
            'Compliance'                         => 'ISO / GLP / GMP',
            'Mechanical box, L × W × H (mm)'    => '360 × 140 × 146',
            'Electronic box, W × D × H (mm)'    => '240 × 210 × 65',
        ]],
    ],
];
