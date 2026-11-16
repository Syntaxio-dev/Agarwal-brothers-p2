<?php

/**
 * Aczet CY Series Top-Pan Precision Balance - ONE product for the whole line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Product page https://aczet.com/product/top-pan-precision-balance ('Full Specs' tables, overview text)
 *  - Aczet datasheet https://aczet.com/upload/product_documentation/Datasheet-_Top_Pan_-Standard.pdf (1 page, rendered and read)  <- attached as the document
 *  Spec values are taken from the DATASHEET.
 *
 * Models found: 7 in the datasheet (CY 312, CY 612, CY 1202, CY 2202, CY 3102, CY 4102, CY 6102) = the same as the pasted list.
 * The product page has tables for only five of them (1202 to 6102). The 310 g and 610 g models (128 x 128 mm pan with draft shield) are in the
 * datasheet and mentioned in the page text, but have no table on the page.
 *
 * Differences between the sources (datasheet used; owner to confirm):
 *  - Pan size of the open-pan models: datasheet 198 x 205 mm, page 190 x 205 mm (198 x 205 for the 6100 g model).
 *  - Operating temperature: datasheet 18 to 30 C, page 15 to 35 C.
 *  - Weight: datasheet 7.5 kg, page 6.8 kg. Balance size: datasheet 342.5 x 212 x 169.5 mm (draft shield) or 89.5 mm (open pan), page 89.5 mm only.
 *  - Linearity: 0.02 g, 0.03 g for the 6100 g model (datasheet and page agree).
 *  - USP minimum weight: datasheet 1 g (U = 1%: 100 mg); the page prints 10 g / 1 g for this series too, i.e. the page repeats the Professional values.
 *  - Interface: page text says 'Standard USB and RS232'; datasheet 'RS 232C, USB optional' (datasheet used).
 *  - The page says '10 built-in applications'; only the ones named on the datasheet / page are listed.
 *  - IP 54: the page says 'The balance is IP 54 protection qualified' for the whole page (both series; not in the datasheet). NOT claimed here for the standard CY series, because the datasheet does not state it for that series.
 *  - 21 CFR Part 11 is only an optional PC software accessory, not a certificate of the balance, so it is not claimed.
 * Not confirmed: order codes beyond the model names; the minimum weights are 'typical' values.
 * Nothing is claimed about Agarwal Brothers.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CY Series';
$type     = 'Top-Pan Precision Balance';

$shortDescription = 'The Aczet CY Series Top-Pan Precision Balances read to 0.01 g and cover seven capacities from 310 g to 6100 g: CY 312, CY 612, CY 1202, CY 2202, CY 3102, CY 4102 and CY 6102. External calibration with a test weight, an alphanumeric backlit LCD and a stainless-steel pan make them practical everyday balances for quality control, research and teaching laboratories.';

$overview = '<p>The <strong>Aczet CY Series Top-Pan Precision Balance</strong> range is made for routine weighing where 0.01 g readability is enough. Aczet describes these top-loading balances as covering a wide range of capacities for educational laboratories, quality control, manufacturing, research and fieldwork, from 310 g up to 6100 g.</p>'
    . '<p><strong>Seven models, two pan styles</strong></p>'
    . '<ul><li><p>CY 312 (310 g) and CY 612 (610 g) have a 128 x 128 mm pan inside a glass draft shield, which slides open wide so large vessels can be placed easily.</p></li><li><p>CY 1202 (1200 g), CY 2202 (2200 g), CY 3102 (3100 g), CY 4102 (4100 g) and CY 6102 (6100 g) have an open 198 x 205 mm pan without a draft shield, so items can be put on directly.</p></li></ul>'
    . '<p>All seven models read 0.01 g with 0.01 g repeatability, have a minimum load of 0.5 g and respond in 2 to 3 seconds.</p>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>The sensor is based on electromagnetic force compensation (EMFC). The Standard Level CY Series uses external calibration: the balance is adjusted manually with a defined test weight, and an external test weight is needed (E1, E2 and F1 test weights are listed among Aczet accessories).</p>'
    . '<p>A capacity tracker built into the display helps prevent overloading the weighing cell, and Aczet states that the balances follow the USP requirements of chapters 41 and 1251.</p>'
    . '<p><strong>Built for daily use</strong></p>'
    . '<p>The bright backlit LCD has large digits, a built-in real-time clock supports GLP documentation, a round bubble level and large adjustable feet make levelling quick, and a protective cover is supplied as standard. Aczet states that the washable parts come apart without tools.</p>'
    . '<p><strong>Applications and connections</strong></p>'
    . '<p>Built-in applications include piece counting, percentage weighing, animal (dynamic) weighing, check weighing, automatic density determination, formulation and an automatic statistic calculator. An RS 232C interface is provided, with USB (Windows direct communication) as an option; Aczet lists a dot-matrix data printer, a density kit and a remote display among its accessories.</p>'
    . '<p>The table on this page shows ranges for the whole CY series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Seven capacities, 0.01 g readability', 'text' => '310 g to 6100 g in one series, every model reading to 0.01 g with 0.01 g repeatability.'],
    ['title' => 'External calibration', 'text' => 'The balance is adjusted manually with a defined test weight, so no internal calibration mechanism is needed.'],
    ['title' => 'Draft shield or open pan', 'text' => 'The 310 g and 610 g models have a 128 x 128 mm pan in a glass draft shield; the larger models have an open 198 x 205 mm pan.'],
    ['title' => 'Built-in applications', 'text' => 'Counting, percentage, animal weighing, check weighing, density, formulation and statistics.'],
    ['title' => 'Backlit LCD and GLP clock', 'text' => 'Large backlit digits and a built-in real-time clock for time-stamped, traceable records.'],
];

$advantages = [
    ['title' => 'One series from 310 g to 6100 g', 'text' => 'Choose the capacity you need and keep the same 0.01 g readability.'],
    ['title' => 'Overload awareness', 'text' => 'The capacity tracker on the display helps prevent overloading the weighing cell.'],
    ['title' => 'Wide pan, easy loading', 'text' => 'The draft shield doors slide open widely, and the larger models have a fully open pan.'],
    ['title' => 'Quick to set up', 'text' => 'A bubble level, large adjustable feet and a supplied protective cover.'],
    ['title' => 'Windows-friendly output', 'text' => 'RS 232C with optional USB for direct communication to Excel and other Windows applications.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CY series?', 'answer' => 'The Aczet datasheet lists seven models: CY 312 (310 g), CY 612 (610 g), CY 1202 (1200 g), CY 2202 (2200 g), CY 3102 (3100 g), CY 4102 (4100 g) and CY 6102 (6100 g). All read to 0.01 g.'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability is 0.01 g on all seven models. Linearity is 0.02 g, except 0.03 g on the CY 6102.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'The CY Series uses external calibration: you place a defined test weight on the pan to adjust the balance. Aczet lists E1, E2 and F1 test weights among its accessories.'],
    ['question' => 'What is the difference between the CY and the CY-C series?', 'answer' => 'Both read 0.01 g with the same capacities and pan sizes. The standard CY Series is calibrated manually with an external test weight, while the Professional CY-C Series has motorised internal calibration and PSAC.'],
    ['question' => 'Which models have a draft shield?', 'answer' => 'The CY 312 and CY 612 have a 128 x 128 mm pan inside a glass draft shield. The 1200 g to 6100 g models have an open 198 x 205 mm pan with no draft shield.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The minimum load is 0.5 g. Aczet gives a typical minimum weight of 1 g according to USP (0.1 percent) and 100 mg for U = 1 percent.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. Aczet lists a CPR 02 dot-matrix data printer, an interface cable and a remote display among its accessories.'],
    ['question' => 'What accessories are available?', 'answer' => 'Aczet lists a data printer, density kit, E1 / E2 / F1 test weights, an anti-vibration pad, battery backup, remote display, IQ / OQ / PQ documents, an interface cable, an anti-thief lock and a specific gravity stand.'],
];

$documents = [
    ['title' => 'CY Series Top-Pan Datasheet', 'type' => 'Brochure', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DT2.pdf'],
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
        ['model' => 'CY Series Top-Pan Precision Balance', 'heading' => 'CY Series Top-Pan Precision Balance (0.01 g)', 'batch' => 1, 'specs' => [
            'Model'                                => 'CY 312, CY 612, CY 1202, CY 2202, CY 3102, CY 4102, CY 6102',
            'Readability (g)'                      => '0.01',
            'Weighing capacity (g)'                => '310 to 6100 (CY 312: 310; CY 612: 610; CY 1202: 1200; CY 2202: 2200; CY 3102: 3100; CY 4102: 4100; CY 6102: 6100)',
            'Minimum load (g)'                     => '0.5',
            'Repeatability (g)'                    => '0.01',
            'Linearity (g)'                        => '± 0.02 (CY 6102: ± 0.03)',
            'Tare range, subtractive (g)'          => 'Up to the capacity of each model (-310 to -6100)',
            'Minimum weight, USP (g)'              => '1 (typical, USP, 0.1%)',
            'Minimum weight, U = 1%'               => '100 mg (typical, U = 1%)',
            'Pan size (mm)'                        => '128 × 128 with draft shield (CY 312, CY 612); 198 × 205 open pan (CY 1202, CY 2202, CY 3102, CY 4102, CY 6102)',
            'Response time (sec.)'                 => '2 to 3',
            'Display'                              => 'Alphanumeric backlit LCD',
            'Calibration and adjustment'           => 'External calibration',
            'Units'                                => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'           => '18 to 30',
            'Sensitivity drift (ppm/°C)'           => '± 2',
            'Interface'                            => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes'                    => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply'                         => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                           => 'ISO, GLP / GMP & USP',
            'Balance size, W × D × H (mm)'         => '342.5 × 212 × 169.5 (draft shield models); 342.5 × 212 × 89.5 (open pan models)',
            'Weight, approx. (kg)'                 => '7.5',
        ]],
    ],
];
