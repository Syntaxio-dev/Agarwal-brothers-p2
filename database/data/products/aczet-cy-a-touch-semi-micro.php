<?php

/**
 * Aczet CY-A Series Touch Screen Semi-Micro Balance - ONE product for the whole CY-A line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026):
 *  - Aczet technical data sheet "CY-A SERIES Touch Screen Semi-Micro Balances", 1 page, https://aczet.com/upload/product_documentation/Touch-Semi-Micro.pdf
 *    (linked in the Documentation tab of https://aczet.com/product/semi-micro-balance as "Touch Screen Semi-Micro Balance"). The table was rendered
 *    and read as an image; every spec value below is taken from it. This is the ONLY Aczet source for the CY-A models.
 *  - https://aczet.com/product/semi-micro-balance (general semi-micro text: 0.01 mg readability, up to 220 g, single-range and dual-range, applications).
 *    That page's "Products and Specs" tab lists ONLY the CY-C models (CY 285C, CY 205C), NOT any CY-A model, so there are no website 'Full Specs' for CY-A.
 *
 * Differences from the pasted model list (owner to confirm):
 *  - The datasheet lists SIX models: CY 205A (200 g, 0.01 mg), CY 225A (220 g, 0.1 mg), CY 285A (80 / 220 g, 0.01 / 0.1 mg),
 *    CY 255A (100 / 250 g, 0.01 / 0.1 mg), CY 165A (100 / 160 g, 0.01 / 0.1 mg), CY 65A (60 g, 0.01 mg).
 *  - The paste's CY 105A (100 g x 0.01 mg) is NOT in the datasheet and nowhere on aczet.com: NOT included.
 *  - The paste says CY 255A is 100 / 210 g; the datasheet says 100 / 250 g (datasheet used). CY 285A matches. Pan 80 mm, touch screen and motorised
 *    internal calibration match.
 *  - The datasheet does not say which of the two dual-range values belongs to which range; the cells show the two values as printed
 *    (the website's CY-C text calls them two ranges with two sensitivities).
 *  - Not in the datasheet, so left out: IP54 (the datasheet text layer has "IP54" behind an icon, but the printed icon only says "IP"), the EMFC sensor
 *    wording (only on the CY-C / general semi-micro page), any certificate. A reseller listing also shows a "CY 265A" (220 g) that is not in this datasheet.
 *  - The datasheet has no date or version, and the website's CY-A not being listed under Products and Specs means it may be an older or on-request range:
 *    ask Aczet whether the CY-A series is still current. "Interface": the text only names the USB interface; RS 232 / Windows Direct appear as printed
 *    icons, so 'USB, RS 232' is taken from those icons.
 *  - The data sheet text says the display is the "latest generation capacitive display" and 5.7 inch; "Excellence" on the photo is a front-panel label only.
 * Nothing about Agarwal Brothers is claimed.
 */

$category = 'Semi Micro Balance';
$brand    = 'Aczet';
$group    = 'CY-A Series';
$type     = 'Touch Screen Semi-Micro Balance';

$shortDescription = 'The Aczet CY-A Series touch screen semi-micro balances combine 0.01 mg or 0.1 mg readability with a 5.7 inch colour touch screen and touch-free infrared operation. Six models (CY 205A, CY 225A, CY 285A, CY 255A, CY 165A and CY 65A) cover 60 g to 250 g, some with dual range. Motorised internal calibration, an 80 mm grid pan, seven built-in databases, a 500,000-record ALIBI memory and GLP-ready time stamping suit pharmaceutical, chemical, food and research laboratories.';

$overview = '<p>The <strong>Aczet CY-A Series</strong> is a range of semi-micro balances built around a 5.7 inch touch screen. A semi-micro balance reads to 0.01 mg (10 µg) and weighs up to a few hundred grams, which suits small samples, reference standards and formulation work in the laboratory. The CY-A range adds a touch interface, infrared hands-free operation and built-in databases.</p>'
    . '<p><strong>Six models, 60 g to 250 g</strong></p>'
    . '<ul>'
    . '<li><p><strong>CY 205A:</strong> 200 g, 0.01 mg.</p></li>'
    . '<li><p><strong>CY 225A:</strong> 220 g, 0.1 mg.</p></li>'
    . '<li><p><strong>CY 285A:</strong> dual range, 80 / 220 g, 0.01 / 0.1 mg.</p></li>'
    . '<li><p><strong>CY 255A:</strong> dual range, 100 / 250 g, 0.01 / 0.1 mg.</p></li>'
    . '<li><p><strong>CY 165A:</strong> dual range, 100 / 160 g, 0.01 / 0.1 mg.</p></li>'
    . '<li><p><strong>CY 65A:</strong> 60 g, 0.01 mg.</p></li>'
    . '</ul>'
    . '<p>All models share an 80 mm grid pan, a response time of 5 to 8 seconds and motorised internal calibration.</p>'
    . '<p><strong>Touch screen and hands-free operation</strong></p>'
    . '<p>The latest-generation capacitive display is designed for everyday ease of use, with a clear menu and a home screen that can be arranged with widgets. Built-in infrared sensors let you tare, send a result to a printer or step through a process by simply moving a hand across the sensor. This helps when you work with valuable or toxic samples and want to avoid touching the balance.</p>'
    . '<p><strong>Databases and records</strong></p>'
    . '<p>The information system is based on seven databases: users (up to 25), products (up to 5,000), weighments (up to 50,000), packaging types (up to 100), formulas (up to 100), clients (up to 100) and an ALIBI memory for up to 500,000 weighments. You can create reports on formulas (up to 200 reports) and on density determination (up to 500 reports). USB makes it quick to copy results, reports and databases to other balances, and a built-in real-time clock supports GLP and GMP documentation.</p>'
    . '<p>Aczet lists semi-micro balances for pharmaceutical, chemical, food, petroleum and biotechnology testing, and for research, NABL and academic laboratories.</p>'
    . '<p>The table on this page shows ranges for the whole CY-A series. Tell our team the capacity and readability you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => '5.7 inch touch screen', 'text' => 'A capacitive colour touch display with a customisable home screen, so the results and information you use most are one touch away.'],
    ['title' => 'Touch-free infrared sensors', 'text' => 'Tare, print or move through a process with a wave of the hand, which helps with valuable or toxic samples.'],
    ['title' => 'Six models, up to dual range', 'text' => 'CY 65A to CY 255A cover 60 g to 250 g with 0.01 mg or 0.1 mg readability, and CY 285A, CY 255A and CY 165A offer two ranges.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Fully automatic calibration with built-in weights keeps the balance accurate without handling external weights.'],
    ['title' => 'Seven databases and ALIBI memory', 'text' => 'Store users, products, weighments, packaging, formulas and clients, with up to 500,000 weighments kept in the ALIBI memory.'],
];

$advantages = [
    ['title' => 'Several users, one balance', 'text' => 'Up to 25 users and 5,000 products can be stored, so a shared lab balance keeps each person\'s work organised.'],
    ['title' => 'Traceable results', 'text' => 'The built-in clock and ALIBI memory record weighments with time stamps, which supports GLP and GMP documentation.'],
    ['title' => 'Faster formulation and density work', 'text' => 'Application modes include formulation and automatic density determination, with reports for up to 200 formulas and 500 density runs.'],
    ['title' => 'Low sensitivity drift', 'text' => 'Sensitivity drift is stated as ±1 ppm per °C, so readings stay steady within the 18 to 30 °C operating range.'],
    ['title' => 'Easy data movement', 'text' => 'USB transfer copies results, reports and databases to other balances, and RS 232 is shown among the interfaces.'],
];

$faqs = [
    ['question' => 'Which models are in the CY-A series?', 'answer' => 'Aczet\'s CY-A data sheet lists six models: CY 205A (200 g, 0.01 mg), CY 225A (220 g, 0.1 mg), CY 285A (80 / 220 g), CY 255A (100 / 250 g), CY 165A (100 / 160 g) and CY 65A (60 g, 0.01 mg). The CY 285A, CY 255A and CY 165A are dual-range and read to 0.01 / 0.1 mg.'],
    ['question' => 'What does a dual-range semi-micro balance mean?', 'answer' => 'A dual-range balance has two capacity ranges with two readabilities, for example 0.01 mg for lighter loads and 0.1 mg for heavier ones. Aczet describes semi-micro balances as available in single-range and dual-range versions. Tell us your typical sample and container weights and our team will confirm the right model.'],
    ['question' => 'How is the balance operated?', 'answer' => 'It has a 5.7 inch touch screen. Built-in infrared sensors also let you tare, send a result to a printer or move through steps without touching the balance, by passing a hand across the sensor.'],
    ['question' => 'How is it calibrated?', 'answer' => 'It has fully automatic motorised internal calibration with built-in weights. The data sheet states a sensitivity drift of ±1 ppm per °C.'],
    ['question' => 'What data can it store?', 'answer' => 'Seven databases: users (up to 25), products (up to 5,000), weighments (up to 50,000), packaging types (up to 100), formulas (up to 100), clients (up to 100) and an ALIBI memory for up to 500,000 weighments. Reports can cover formulas (up to 200) and density determination (up to 500).'],
    ['question' => 'What pan size does it have?', 'answer' => 'All models have an 80 mm diameter grid pan (±10%), made of stainless steel.'],
    ['question' => 'What are the minimum sample weights?', 'answer' => 'The minimum sample weight as per USP is 30 mg for CY 205A, CY 285A, CY 255A, CY 165A and CY 65A, and 200 mg for the 0.1 mg CY 225A.'],
    ['question' => 'What operating conditions does it need?', 'answer' => 'Aczet gives the operating temperature as 18 to 30 °C and the power supply as a DC adapter, input 100 to 240 V, output 13 V / 1.5 A, 50 / 60 Hz. Place it on a stable bench, as with any semi-micro balance.'],
];

$documents = [
    ['title' => 'CY-A Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXF1AC1A9BWQ4T8ZH3NC6DGA.pdf'],
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
        ['model' => 'CY-A Touch Screen Semi-Micro', 'heading' => 'CY-A Touch Screen Semi-Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                                  => 'CY 205A, CY 225A, CY 285A, CY 255A, CY 165A, CY 65A',
            'Readability (mg)'                       => '0.01 to 0.1 (CY 205A / CY 65A: 0.01, CY 225A: 0.1, CY 285A / CY 255A / CY 165A: 0.01 / 0.1)',
            'Weighing capacity (g)'                  => '60 to 250 (CY 205A: 200, CY 225A: 220, CY 285A: 80 / 220, CY 255A: 100 / 250, CY 165A: 100 / 160, CY 65A: 60)',
            'Repeatability (mg)'                     => '0.01 to 0.1 (CY 205A: 0.01, CY 225A: 0.1, CY 285A / CY 255A / CY 165A: 0.02 / 0.1, CY 65A: 0.02)',
            'Linearity (mg)'                         => '0.04 to 0.2 (CY 205A: 0.1, CY 225A: 0.2, CY 285A: 0.04 / 0.2, CY 255A / CY 165A: 0.05 / 0.2, CY 65A: 0.04)',
            'Tare range'                             => 'Full capacity (subtractive)',
            'Minimum sample weight, as per USP (mg)' => '30 to 200 (CY 225A: 200, all other models: 30)',
            'Pan size (mm)'                          => 'Ø 80 grid pan (±10%)',
            'Response time (sec.)'                   => '5 to 8',
            'Display'                                => '5.7" touch screen',
            'Calibration and adjustment'             => 'Fully automatic motorised internal built-in weights calibration',
            'Units'                                  => 'g, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'             => '18 to 30',
            'Sensitivity drift (ppm/°C)'             => '± 1 (1 × 10⁻⁶ / °C)',
            'Application modes'                      => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation',
            'Databases'                              => 'Users (up to 25), products (up to 5,000), weighments (up to 50,000), packaging (up to 100), formulas (up to 100), clients (up to 100)',
            'ALIBI memory'                           => 'Up to 500,000 weighments',
            'Hands-free operation'                   => 'Built-in infrared sensors',
            'Interface'                              => 'USB, RS 232',
            'Power supply'                           => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                             => 'ISO / GLP / GMP compliance',
            'Balance size, W × D × H (mm)'           => '342.5 × 212 × 341 (chamber height 228.5)',
            'Weight, net / gross (kg)'               => '8 / 9.3',
        ]],
    ],
];
