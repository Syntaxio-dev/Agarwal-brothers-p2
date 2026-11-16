<?php

/**
 * Aczet CY-C Series Professional Semi-Micro Balance - ONE product for the whole CY-C line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet product page https://aczet.com/product/semi-micro-balance (its 'Full Specs' tables list only CY 285C and CY 205C)
 *  - Aczet datasheet "Single Range Semi Micro Series" https://aczet.com/upload/product_documentation/Semi_micro_Balance_Professional_Series.1.pdf
 *    (1 page, CY-C Series Semi Micro Balances, Professional Level: CY 205C only)
 *  - Aczet datasheet "Dual Range Semi Micro Series" https://aczet.com/upload/product_documentation/Dual_Range_Semi_Micro_Series1.pdf
 *    (1 page, same title: CY 265C, CY 285C, CY 165C, CY 255C)
 * Both datasheets were rendered and read. Spec values are taken from the DATASHEETS (as agreed); the two PDFs are merged into one
 * 2-page document (page 1 dual range, page 2 single range) for the 'documents' entry below.
 *
 * Models found: CY 205C (single range), CY 165C, CY 265C, CY 285C, CY 255C (dual range) = 5 models.
 *
 * Differences from the pasted model list (owner to confirm):
 *  - CY 105C (100 g x 0.01 mg) is NOT on aczet.com (not on the product page, not in either datasheet), so it is NOT included.
 *    Several Indian dealers list a CY 105C (100 g, 0.01 mg, 80 mm pan), but no Aczet source was found: ask Aczet before adding it.
 *  - CY 205C = 210 g x 0.01 mg in the datasheet (paste was right), but the product page says 0.1 mg readability (see below).
 *  - CY 255C = 100 / 250 g x 0.01 / 0.1 mg (paste said 100 / 210 g). CY 285C = 80 / 220 g x 0.01 / 0.1 mg (matches).
 *  - CY 165C (100 / 160 g) and CY 265C (60 / 220 g) are in the dual-range datasheet but were not in the paste: added.
 *  - The paste says 'backlit LCD keypad'; the datasheet says alphanumeric backlit LCD with a push-button keypad (no touch screen).
 *    Motorised internal calibration and 80 mm grid pan match the dual-range datasheet (CY 205C has a 91 mm grid pan).
 * Differences between the Aczet product page and the datasheets (datasheet used):
 *  - CY 205C: page says readability 0.1 mg, repeatability 0.1 mg, linearity 2 mg, pan 80 mm, response 2-8 s, 15-35 C, drift +/-1 ppm, tare -210 g;
 *    the single-range datasheet says 0.01 mg, 0.01 mg, 0.1 mg, 91 mm, 2-4 s, 5-40 C, +/-2 ppm, -200 g. The page row for CY 205C looks wrong (it is
 *    a semi-micro balance with 0.01 mg in the datasheet), but only Aczet can confirm.
 *  - CY 285C: linearity is +/-0.03 / 0.1 mg on the page vs 0.04 / 0.2 mg in the datasheet; tare range -220 g vs -80 / 220 g; page also prints a
 *    minimum sample weight (U=1%, K=2) of 2 mg that the datasheet does not have. Repeatability, USP minimum weight (20 mg) and the rest match.
 *  - Interface: the page's overview says 'Standard USB and RS232'; the datasheets say 'RS 232C, USB optional'. The datasheets are used.
 *  - The page's feature text (IP 54, 304 stainless steel pan, three-side opening shield, USP chapters 41 / 1251, 10 applications) describes the
 *    whole 'Semi Microbalance' category and is not specific to the CY-C datasheets, so these claims are NOT used here (the 'application modes'
 *    row is the datasheet's own list).
 * Not confirmed: which of the two numbers of a dual range (for example 80 / 220 g) belongs to which readability (the datasheet prints them as
 * pairs and does not say); what the two weights '8 Kg / 9.3 kg' mean (printed that way on both datasheets); order codes beyond the model names;
 * the CY 105C. Nothing is claimed about Agarwal Brothers.
 */

$category = 'Semi Micro Balance';
$brand    = 'Aczet';
$group    = 'CY-C Series';
$type     = 'Professional Semi-Micro Balance';

$shortDescription = 'The Aczet CY-C Series Professional Semi-Micro Balances give you 0.01 mg readability for weighing small samples in the laboratory. Five models cover 60 g to 250 g: the single-range CY 205C (210 g) and the dual-range CY 165C, CY 265C, CY 285C and CY 255C, which switch between 0.01 mg and 0.1 mg. A backlit LCD with 15 mm digits, motorised internal calibration with automatic recalibration, a grid pan under a glass draft shield and a built-in clock for GLP records make them practical for pharma, chemical, food and research labs.';

$overview = '<p>The <strong>Aczet CY-C Series Professional Semi-Micro Balance</strong> range is built for routine laboratory weighing where a readability of 0.01 mg (10 µg) is needed. Aczet describes a semi-micro balance as one that measures small sample sizes with 0.01 mg readability and a capacity of up to about 220 g, and offers it as a single-range balance with one fixed range or as a dual-range balance with two ranges and two sensitivities. The CY-C Series covers both types.</p>'
    . '<p><strong>Five models, two types</strong></p>'
    . '<ul>'
    . '<li><p><strong>Single range:</strong> CY 205C, 210 g capacity, 0.01 mg readability.</p></li>'
    . '<li><p><strong>Dual range:</strong> CY 165C (100 / 160 g), CY 265C (60 / 220 g), CY 285C (80 / 220 g) and CY 255C (100 / 250 g), each reading 0.01 mg and 0.1 mg.</p></li>'
    . '</ul>'
    . '<p>All five models share a minimum load of 1 mg, a stainless steel grid pan (80 mm on the dual-range models, 91 mm on the CY 205C) under a glass draft shield, and a response time of 2 to 4 seconds (CY 205C) or 2 to 8 seconds (dual-range models).</p>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>Aczet calls the weigh sensor its Mono-Tech technology. Fully automatic motorised built-in weights provide internal calibration, and the Perfect Self Auto Calibration (PSAC) function repeats the calibration when the ambient temperature changes by a set value or after a defined time interval. A capacity tracker on the display helps you spot possible overloads, and a below-balance weighing hook lets you weigh under the balance.</p>'
    . '<p><strong>Operation and records</strong></p>'
    . '<p>The high-contrast backlit LCD has 15 mm digits that stay easy to read under any room lighting, and operation is by push buttons on the front panel. The balance supports piece counting, percentage weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation and an automatic statistics calculator, and many units from grams and milligrams to carats and ounces. The built-in clock lets documents carry a time stamp for ISO / GLP / GMP record keeping, an RS 232C interface is provided (USB optional, with Windows direct communication), and an automatic shut-off helps save power.</p>'
    . '<p>The table on this page shows ranges for the whole CY-C series. Tell our team the capacity and readability you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => '0.01 mg readability, five models', 'text' => 'The CY 205C, CY 165C, CY 265C, CY 285C and CY 255C cover 60 g to 250 g, all reading to 0.01 mg.'],
    ['title' => 'Single or dual range', 'text' => 'Choose the single-range CY 205C, or a dual-range model that gives 0.01 mg and 0.1 mg readability in two ranges.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised built-in weights plus PSAC recalibrate on a temperature change or after a set time.'],
    ['title' => 'Clear backlit display', 'text' => 'A high-contrast backlit LCD with 15 mm digits is easy to read in any room lighting, with push-button operation.'],
    ['title' => 'GLP-ready records', 'text' => 'A built-in clock, RS 232C output (USB optional) and an automatic statistics calculator support traceable documentation.'],
];

$advantages = [
    ['title' => 'Right capacity, same readability', 'text' => 'Pick the capacity you need from 60 g to 250 g and keep 0.01 mg readability.'],
    ['title' => 'Overload awareness', 'text' => 'The built-in capacity tracker on the display shows possible overloads before they reach the weighing cell.'],
    ['title' => 'Fewer manual calibrations', 'text' => 'PSAC repeats the internal calibration when temperature changes or a defined time has passed.'],
    ['title' => 'Many weighing tasks in one balance', 'text' => 'Counting, percentage, check weighing, density determination, formulation and statistics are built in.'],
    ['title' => 'Weigh below the balance', 'text' => 'A below-weighing hook lets you weigh samples that cannot sit on the pan.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CY-C series?', 'answer' => 'Aczet lists five models: the single-range CY 205C (210 g) and the dual-range CY 165C (100 / 160 g), CY 265C (60 / 220 g), CY 285C (80 / 220 g) and CY 255C (100 / 250 g). All read to 0.01 mg.'],
    ['question' => 'What is the difference between single range and dual range?', 'answer' => 'A single-range balance has one fixed range and one readability. A dual-range balance has two ranges with two readabilities. In the CY-C series the dual-range models read 0.01 mg and 0.1 mg. The datasheet prints the capacity, repeatability and linearity as two values for these models.'],
    ['question' => 'What is the minimum load and the USP minimum weight?', 'answer' => 'The minimum load is 1 mg on every model. Aczet gives a typical minimum sample weight according to USP Chapter 41 of 20 mg for the CY 205C, CY 165C, CY 265C and CY 285C, and 30 mg for the CY 255C.'],
    ['question' => 'What pan size does it have?', 'answer' => 'The dual-range models (CY 165C, CY 265C, CY 285C and CY 255C) have an 80 mm diameter grid pan and the CY 205C has a 91 mm diameter grid pan. The pan sits under a glass draft shield.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with built-in weights. The Perfect Self Auto Calibration (PSAC) function repeats the calibration when the ambient temperature changes by a set value or after a defined time interval.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. Aczet also lists a dot-matrix data printer as an accessory on its product page.'],
    ['question' => 'Does it have a touch screen?', 'answer' => 'No. The CY-C Series has an alphanumeric backlit LCD with a push-button keypad. Aczet offers a separate touch-screen semi-micro balance.'],
    ['question' => 'What operating conditions does it need?', 'answer' => 'Aczet gives the operating temperature as 15 to 35 °C for the dual-range models and 5 to 40 °C for the CY 205C. Place it on a stable bench; an anti-vibration weighing table is available as an accessory.'],
];

$documents = [
    ['title' => 'CY-C Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXF1AC1A9BWQ4T8ZH3NC6DGC.pdf'],
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
        ['model' => 'CY-C Professional Semi-Micro', 'heading' => 'CY-C Professional Semi-Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CY 205C, CY 165C, CY 265C, CY 285C, CY 255C',
            'Readability (mg)'               => '0.01 to 0.1 (CY 205C: 0.01; CY 165C, CY 265C, CY 285C, CY 255C: 0.01 / 0.1)',
            'Weighing capacity (g)'          => '60 to 250 (CY 205C: 210; CY 165C: 100 / 160; CY 265C: 60 / 220; CY 285C: 80 / 220; CY 255C: 100 / 250)',
            'Minimum load (mg)'              => '1',
            'Repeatability, sd (mg)'         => '0.01 to 0.1 (CY 205C: 0.01; CY 165C, CY 265C, CY 285C: 0.02 / 0.1; CY 255C: 0.05 / 0.1)',
            'Linearity (mg)'                 => '± 0.04 to ± 0.2 (CY 205C: 0.1; CY 165C: 0.05 / 0.2; CY 265C, CY 285C: 0.04 / 0.2; CY 255C: 0.06)',
            'Tare range, subtractive (g)'    => 'Up to -250 (CY 205C: -200; CY 165C: -100 / 160; CY 265C: -60 / 220; CY 285C: -80 / 220; CY 255C: -250)',
            'USP minimum weight (mg)'        => '20 to 30 (CY 255C: 30; all other models: 20)',
            'Pan size (mm)'                  => 'Ø 80 to Ø 91 grid pan (CY 205C: Ø 91; dual-range models: Ø 80)',
            'Response time (sec.)'           => '2 to 8 (CY 205C: 2 to 4; dual-range models: 2 to 8)',
            'Display'                        => 'Alphanumeric backlit LCD (15 mm digits)',
            'Calibration and adjustment'     => 'Motorised internal calibration',
            'Units'                          => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'     => '5 to 40 (CY 205C: 5 to 40; dual-range models: 15 to 35)',
            'Sensitivity drift (ppm/°C)'     => '± 1 to ± 2 (CY 205C: ± 2; dual-range models: ± 1)',
            'Interface'                      => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes'              => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP compliance',
            'Balance size, W × D × H (mm)'   => '342.5 × 212 × 341 (chamber height: 228.5)',
            'Weight, approx. (kg)'           => '8 / 9.3',
        ]],
    ],
];
