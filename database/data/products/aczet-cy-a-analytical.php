<?php

/**
 * Aczet CY-A Series Touch Screen Analytical Balance - ONE product for the whole CY-A line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet datasheet "CY-A Series Analytical Balances, Professional Level" (1 page, read and rendered)
 *    https://aczet.com/upload/product_documentation/CY-A_Series_Analytical_Balances_(1)_(1).pdf
 *    (listed on the Analytical Balance product page https://aczet.com/product/analytical-balance-1 as brochure "CY 224A Touch Screen Analytical Balance")
 *  - The product page only has 'Full Specs' tables for the CY-C and CY (standard) series, NOT for CY-A, so every spec value below comes from the datasheet.
 *
 * Models found: CY 64A, CY 124A, CY 224A, CY 304A, CY 324A, CY 404A = 6 models, same as the pasted list (all six confirmed by the datasheet).
 *
 * Differences from the paste (owner to confirm):
 *  - 'Internal calibration' in the paste is right: the datasheet says motorised internal calibration (with PSAC auto-calibration).
 *  - '21 CFR Part 11' is NOT stated for the CY-A anywhere. The CY-C datasheet only lists '21 CFR PART 11 PC Software' as an optional accessory
 *    software; the CY-A datasheet lists compliance as 'ISO, GLP / GMP & USP'. The claim is therefore NOT used here.
 *  - Repeatability and linearity differ by model (repeatability 0.1 mg up to CY 224A, 0.2 mg for CY 304A / 324A / 404A; linearity 0.2 mg up to
 *    CY 224A, 0.3 mg above): written per model in the cells.
 *  - IP 54 is NOT stated for the CY-A (only the CY-C brochure has it), so it is not used.
 * Not confirmed: the datasheet says 'backlit display 15 mm digits' in its feature text although this model has a touch screen (probably copied
 * from the CY-C text), so the 15 mm digits are NOT claimed; the size of the touch screen is not given; the datasheet prints the sensitivity drift as
 * '+/- 2ppm (2 x 10-6 / C)'. Order codes beyond the model names are unknown. Nothing is claimed about Agarwal Brothers.
 * The datasheet photo is a single product picture; the extra pictures are crops of it (no other CY-A touch analytical photo exists on aczet.com).
 */

$category = 'Analytical Balance';
$brand    = 'Aczet';
$group    = 'CY-A Series';
$type     = 'Touch Screen Analytical Balance';

$shortDescription = 'The Aczet CY-A Series Touch Screen Analytical Balances read to 0.1 mg and cover six capacities from 60 g to 400 g: CY 64A, CY 124A, CY 224A, CY 304A, CY 324A and CY 404A. A touch screen makes operation simple, motorised internal calibration and automatic PSAC recalibration keep results accurate, and a built-in clock supports GLP records. A glass draft shield, 90 mm pan and built-in weighing applications suit pharma, chemical, food and research laboratories.';

$overview = '<p>The <strong>Aczet CY-A Series Touch Screen Analytical Balance</strong> range is the professional-level analytical balance from Aczet for laboratories that weigh to 0.1 mg. The series has six models, from the CY 64A (60 g) to the CY 404A (400 g), so you can pick the capacity you need without changing the readability.</p>'
    . '<p><strong>Six models, one platform</strong></p>'
    . '<ul>'
    . '<li><p>CY 64A: 60 g, CY 124A: 120 g and CY 224A: 220 g, with 0.1 mg repeatability and 0.2 mg linearity.</p></li>'
    . '<li><p>CY 304A: 300 g, CY 324A: 320 g and CY 404A: 400 g, with 0.2 mg repeatability and 0.3 mg linearity.</p></li>'
    . '</ul>'
    . '<p>All six models share a 1 mg minimum load, a 90 mm diameter pan, a 2 to 3 second response time and the same body, so the footprint is the same whichever capacity you choose.</p>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>Aczet describes the weigh sensor as its Mono-Tech technology. Fully automatic motorised built-in weights provide internal calibration, and the Perfect Self Auto Calibration (PSAC) function repeats the calibration when the ambient temperature changes by a set value or after a defined time interval. A capacity tracker built into the display helps you monitor possible overloads, and a hook for weighing below the balance is provided.</p>'
    . '<p><strong>Touch screen operation and records</strong></p>'
    . '<p>Instead of a keypad, the balance is operated on a touch screen. It supports piece counting, percentage weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation and an automatic statistics calculator, and many units from grams and milligrams to carats and ounces. The built-in clock lets documents carry a time stamp for ISO / GLP / GMP record keeping. An RS 232C interface is provided, and USB (Windows direct communication) is optional.</p>'
    . '<p><strong>Where it is used</strong></p>'
    . '<p>An analytical balance with 0.1 mg readability is the everyday tool of pharmaceutical, chemical, food and beverage, cosmetic and research laboratories, quality control and academic labs.</p>'
    . '<p>The table on this page shows ranges for the whole CY-A series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Touch screen operation', 'text' => 'A touch screen replaces the keypad, so menus and applications are selected with a tap.'],
    ['title' => 'Six capacities, 0.1 mg readability', 'text' => 'CY 64A, 124A, 224A, 304A, 324A and 404A cover 60 g to 400 g, all reading to 0.1 mg.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised built-in weights plus PSAC recalibrate on a temperature change or after a set time.'],
    ['title' => 'Overload awareness', 'text' => 'A capacity tracker built into the display lets you monitor possible overloads.'],
    ['title' => 'GLP-ready records', 'text' => 'A built-in clock and RS 232C output (USB optional) support time-stamped, traceable documents.'],
];

$advantages = [
    ['title' => 'Pick the capacity you need', 'text' => 'Choose from 60 g to 400 g while keeping 0.1 mg readability and the same body.'],
    ['title' => 'Fewer manual calibrations', 'text' => 'PSAC repeats the internal calibration when temperature changes or a defined time has passed.'],
    ['title' => 'Many weighing tasks built in', 'text' => 'Counting, percentage, check weighing, density determination, formulation and statistics need no extra software.'],
    ['title' => 'Weigh below the balance', 'text' => 'A below-weighing hook lets you weigh samples that cannot sit on the pan.'],
    ['title' => 'Easy PC connection', 'text' => 'RS 232C is built in and USB with Windows direct communication is available as an option.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CY-A series?', 'answer' => 'Aczet lists six models: CY 64A (60 g), CY 124A (120 g), CY 224A (220 g), CY 304A (300 g), CY 324A (320 g) and CY 404A (400 g). All read to 0.1 mg.'],
    ['question' => 'What is the repeatability and linearity?', 'answer' => 'Repeatability is 0.1 mg on the CY 64A, 124A and 224A and 0.2 mg on the CY 304A, 324A and 404A. Linearity is 0.2 mg up to the CY 224A and 0.3 mg on the three larger models.'],
    ['question' => 'How is it different from the CY-C series?', 'answer' => 'The CY-A Series is operated on a touch screen, while the CY-C Series has an alphanumeric backlit LCD with a keypad. Both have motorised internal calibration and 0.1 mg readability.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with built-in weights. The Perfect Self Auto Calibration (PSAC) function repeats the calibration when the ambient temperature changes by a set value or after a defined time interval.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The minimum load is 1 mg. Aczet gives a typical minimum sample weight according to USP Chapter 41 of 160 mg for all six models.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. Aczet also lists a dot-matrix data printer, an interface cable and a remote display among its accessories for analytical balances.'],
    ['question' => 'Do I need an anti-vibration table or pad?', 'answer' => 'A stable bench away from draughts and vibration helps any 0.1 mg balance. Aczet lists an anti-vibration pad and test weights (E1, E2, F1) as accessories for its analytical balances.'],
    ['question' => 'What operating conditions does it need?', 'answer' => 'Aczet gives the operating temperature as 18 to 30 degrees C and the power supply as a DC adaptor (input 100 to 240 V, output 13 V, 50 / 60 Hz).'],
];

$documents = [
    ['title' => 'CY-A Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DA1.pdf'],
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
        ['model' => 'CY-A Touch Screen Analytical', 'heading' => 'CY-A Touch Screen Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CY 64A, CY 124A, CY 224A, CY 304A, CY 324A, CY 404A',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 400 (CY 64A: 60; CY 124A: 120; CY 224A: 220; CY 304A: 300; CY 324A: 320; CY 404A: 400)',
            'Minimum load (mg)'              => '1',
            'Repeatability, sd (mg)'         => '0.1 to 0.2 (CY 64A, CY 124A, CY 224A: 0.1; CY 304A, CY 324A, CY 404A: 0.2)',
            'Linearity (mg)'                 => '± 0.2 to ± 0.3 (CY 64A, CY 124A, CY 224A: 0.2; CY 304A, CY 324A, CY 404A: 0.3)',
            'Tare range, subtractive (g)'    => 'Up to -400 (equal to the capacity of each model)',
            'USP minimum weight (mg)'        => '160 (typical, USP Chapter 41)',
            'Pan size (mm)'                  => 'Ø 90',
            'Response time (sec.)'           => '2 to 3',
            'Display'                        => 'Touch screen',
            'Calibration and adjustment'     => 'Motorised internal calibration',
            'Units'                          => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'     => '18 to 30',
            'Sensitivity drift (ppm/°C)'     => '± 2',
            'Interface'                      => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes'              => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP & USP',
            'Balance size, W × D × H (mm)'   => '342.5 × 212 × 341 (chamber height: 228.5)',
            'Weight, approx. (kg)'           => '9.3',
        ]],
    ],
];
