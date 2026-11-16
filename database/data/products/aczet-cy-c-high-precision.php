<?php

/**
 * Aczet CY-C Series High Precision Balance 0.1 g - ONE product for the whole line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Product page https://aczet.com/product/high-precision-balance ('Full Specs' tables, overview text)
 *  - Aczet datasheet https://aczet.com/upload/product_documentation/High_Precision_Balance-Internal.pdf (1 page, rendered and read)  <- attached as the document
 *  Spec values are taken from the DATASHEET.
 *
 * Models found: 6 in the datasheet (CY 3101C, CY 4101C, CY 6101C, CY 8101C, CY 10001C, CY 12001C) = the same as the pasted list.
 * The product page has tables for only four of them (3101, 6101, 8101, 10001); the 4101 and the 12001 are in the datasheet only.
 *
 * Differences between the sources (datasheet used; owner to confirm):
 *  - Minimum load: datasheet 10 g, page 5 g.
 *  - Pan size: datasheet 198 x 205 mm, page 190 x 205 mm.
 *  - Minimum sample weight: the datasheet gives only the USP value 82 g; the page adds U = 1%: 8.2 g (not used, web only).
 *  - The page table prints the tare range of CY 6101C without a minus sign; the datasheet -6100 g is used.
 *  - Interface: page text says 'Standard USB and RS232'; datasheet 'RS 232C, USB optional' (datasheet used).
 *  - IP 54: the page states it for the whole page, not the datasheet, so it is NOT claimed in this product.
 *  - 'Grade 304 stainless steel, non-magnetic pan' is page text (whole page) and is used in the overview and one feature.
 *  - Below-balance hook, 15 mm digits and automatic shut-off are from the datasheet text.
 * Not confirmed: order codes beyond the model names; the 10 kg / 12 kg models' own pan size (one pan size is printed for all).
 * Nothing is claimed about Agarwal Brothers.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CY-C Series';
$type     = 'High Precision Balance';

$shortDescription = 'The Aczet CY-C Series High Precision Balances read to 0.1 g and cover six capacities from 3.1 kg to 12 kg: CY 3101C, CY 4101C, CY 6101C, CY 8101C, CY 10001C and CY 12001C. Motorised internal calibration with PSAC automatic recalibration, a large 198 x 205 mm stainless-steel pan and a backlit LCD suit heavier samples in quality control, research and production areas.';

$overview = '<p>The <strong>Aczet CY-C Series High Precision Balance</strong> range is made for heavier samples that still need 0.1 g readability. Aczet describes these balances as versatile and accurate for educational laboratories, quality control, manufacturing, research and fieldwork, with a wide choice of capacities.</p>'
    . '<p><strong>Six capacities, one body</strong></p>'
    . '<ul><li><p>CY 3101C (3100 g), CY 4101C (4100 g), CY 6101C (6100 g) and CY 8101C (8100 g).</p></li><li><p>CY 10001C (10 kg) and CY 12001C (12 kg).</p></li></ul>'
    . '<p>All six models read 0.1 g with 0.1 g repeatability and 0.2 g linearity, have a 198 x 205 mm pan, a 10 g minimum load and respond in 2 to 3 seconds. Aczet describes the pan as a large grade 304 stainless-steel pan with non-magnetic features.</p>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>The sensor is based on electromagnetic force compensation (EMFC). Motorised built-in weights provide fully automatic internal calibration, and the Perfect Self Auto Calibration (PSAC) function performs it again when the ambient temperature changes by a set value or a defined time interval has passed.</p>'
    . '<p>A capacity tracker built into the display helps prevent overloading the weighing cell, and Aczet states that the balances follow the USP requirements of chapters 41 and 1251. The typical minimum sample weight is 82 g according to USP chapter 41.</p>'
    . '<p><strong>Built for daily use</strong></p>'
    . '<p>The backlit display has large 15 mm digits, a built-in clock supports GLP documentation, a hook allows weighing below the balance, and an automatic shut-off saves power. A round bubble level and large adjustable feet make levelling quick, and a protective cover is supplied as standard.</p>'
    . '<p><strong>Applications and connections</strong></p>'
    . '<p>Built-in applications include piece counting, percentage weighing, animal (dynamic) weighing, check weighing, automatic density determination, formulation and an automatic statistic calculator. An RS 232C interface is provided, with USB (Windows direct communication) as an option.</p>'
    . '<p>The table on this page shows ranges for the whole CY-C series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Six capacities, 0.1 g readability', 'text' => '3.1 kg to 12 kg in one series, every model reading to 0.1 g with 0.1 g repeatability.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Built-in weights calibrate the balance automatically, and PSAC repeats it when temperature or time changes.'],
    ['title' => 'Large stainless-steel pan', 'text' => 'A 198 x 205 mm pan, described by Aczet as grade 304 stainless steel with non-magnetic features.'],
    ['title' => 'Built-in applications', 'text' => 'Counting, percentage, animal weighing, check weighing, density, formulation and statistics.'],
    ['title' => 'Hook, backlit display and GLP clock', 'text' => 'A below-balance weighing hook, 15 mm backlit digits and a built-in clock for GLP records.'],
];

$advantages = [
    ['title' => 'Heavy samples, 0.1 g reading', 'text' => 'Capacities up to 12 kg while still resolving 0.1 g.'],
    ['title' => 'Overload awareness', 'text' => 'The capacity tracker on the display helps prevent overloading the weighing cell.'],
    ['title' => 'Power saving', 'text' => 'An automatic shut-off mode saves power when the balance is idle.'],
    ['title' => 'Weigh below the balance', 'text' => 'A built-in hook allows weighing below the balance.'],
    ['title' => 'Windows-friendly output', 'text' => 'RS 232C with optional USB for direct communication to Excel and other Windows applications.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CY-C series?', 'answer' => 'The Aczet datasheet lists six models: CY 3101C (3100 g), CY 4101C (4100 g), CY 6101C (6100 g), CY 8101C (8100 g), CY 10001C (10 kg) and CY 12001C (12 kg). All read to 0.1 g.'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability (sd) is 0.1 g and linearity is 0.2 g on all six models.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'The CY-C Series has fully automatic motorised internal calibration with built-in weights. The Perfect Self Auto Calibration (PSAC) function repeats it when the ambient temperature changes by a set value or a defined time interval has elapsed.'],
    ['question' => 'What is the difference between the CY-C and the CY series?', 'answer' => 'Both read 0.1 g with the same six capacities and pan size. The CY-C Series is the Professional level with motorised internal calibration and PSAC; the standard CY Series is calibrated manually with an external test weight.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The minimum load is 10 g according to the datasheet. The typical minimum sample weight according to USP chapter 41 is 82 g.'],
    ['question' => 'Is there a draft shield?', 'answer' => 'No. The datasheet shows one open 198 x 205 mm pan for all six models, so items can be placed directly on it.'],
    ['question' => 'Can I weigh below the balance?', 'answer' => 'Yes. Aczet lists a below-weighing hook for weighing below the balance.'],
    ['question' => 'How do I connect it to a PC?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option, for direct communication to Excel or other Windows applications.'],
];

$documents = [
    ['title' => 'CY-C Series High Precision Datasheet', 'type' => 'Brochure', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DH1.pdf'],
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
        ['model' => 'CY-C Series High Precision Balance 0.1 g', 'heading' => 'CY-C Series High Precision Balance (0.1 g)', 'batch' => 1, 'specs' => [
            'Model'                                => 'CY 3101C, CY 4101C, CY 6101C, CY 8101C, CY 10001C, CY 12001C',
            'Readability (g)'                      => '0.1',
            'Weighing capacity (g)'                => '3,100 to 12,000 (CY 3101C: 3,100; CY 4101C: 4,100; CY 6101C: 6,100; CY 8101C: 8,100; CY 10001C: 10,000 (10 kg); CY 12001C: 12,000 (12 kg))',
            'Minimum load (g)'                     => '10',
            'Repeatability, sd (g)'                => '0.1',
            'Linearity (g)'                        => '± 0.2',
            'Tare range, subtractive (g)'          => 'Up to the capacity of each model (-3100 to -12000)',
            'Minimum weight, USP (g)'              => '82 (typical, USP chapter 41)',
            'Pan size (mm)'                        => '198 × 205',
            'Response time (sec.)'                 => '2 to 3',
            'Display'                              => 'Alphanumeric backlit LCD',
            'Calibration and adjustment'           => 'Motorised internal calibration',
            'Units'                                => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'           => '15 to 35',
            'Sensitivity drift (ppm/°C)'           => '± 2',
            'Interface'                            => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes'                    => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply'                         => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                           => 'ISO, GLP / GMP & USP',
            'Balance size, W × D × H (mm)'         => '342.5 × 212 × 89.5',
            'Weight, approx. (kg)'                 => '7.3',
        ]],
    ],
];
