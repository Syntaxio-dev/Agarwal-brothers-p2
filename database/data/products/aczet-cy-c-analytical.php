<?php

/**
 * Aczet CY-C Series Professional Analytical Balance - ONE product for the whole CY-C line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet product page https://aczet.com/product/analytical-balance-1 (CY-C 'Full Specs' tables)
 *  - Aczet datasheet "Professional Series" (CY-C, 1 page) https://aczet.com/upload/product_documentation/DataSheet-_Analytical-Professional_(1)1.pdf
 *  - Aczet brochure "Analytical Balance CY-C Series" (4 pages, the 4th is the same datasheet)
 *    https://aczet.com/upload/product_documentation/Analytical-Professional_Series_(4)-compressed.pdf  <- attached as the document
 * Both PDFs were rendered and read. Spec values are taken from the DATASHEET (as agreed).
 *
 * Models found: CY 64C, CY 124C, CY 224C, CY 304C, CY 324C, CY 404C = 6 models, same as the pasted list (all confirmed by the datasheet).
 *
 * Differences from the paste / between the sources (owner to confirm):
 *  - IP 54: stated for the CY-C in the Aczet brochure and on the product page ('IP 54 protection class'), so it is kept (brochure wording:
 *    protection against dust and water splashes). It is NOT claimed for the touch-screen CY-A.
 *  - The product page's table lists CY 64C, 124C, 224C and a CY 314C (310 g, 0.1 mg, repeatability 0.2 mg, linearity 0.3 mg). The CY 314C is NOT in
 *    the datasheet or the brochure (which list 304C, 324C and 404C instead), so it is left out. Ask Aczet whether CY 314C exists.
 *  - Minimum load: the datasheet prints 10 mg for every model, the product page prints 1 mg. The datasheet (10 mg) is used.
 *  - Linearity: the datasheet prints 0.2 mg for CY 224C, the product page 0.3 mg. The datasheet is used. Datasheet linearity: 0.2 mg (64C, 124C, 224C),
 *    0.3 mg (304C, 324C), 0.6 mg (404C). Repeatability is 0.1 mg for all six in the datasheet (page: 0.1 mg for 64C / 124C / 224C, 0.2 mg for 314C).
 *  - Chamber height: 239.5 mm in the datasheet, 228.5 mm on the product page. The datasheet is used.
 *  - Built-in applications: the brochure says 11 (piece counting, % weighing, formulation, pipette check, animal weighing, custom unit, check weighing,
 *    totalization, density, statistics, below hook), the product page text says 10. The brochure's 11 are used; the brochure also mentions
 *    '15 significant features' and a 'monitored ambient conditions' feature (not repeated here).
 *  - Interface: the page text says 'Standard USB and RS232', the datasheet 'RS 232C, USB optional'. The datasheet is used.
 *  - 21 CFR Part 11 is only listed as an optional PC software accessory (not a certificate of the balance), so it is not claimed.
 * Not confirmed: order codes beyond the model names; the size of the pan material; the exact meaning of the datasheet's two minimum-weight lines
 * (USP 0.1% typical 160 mg, U=1% typical 16 mg, same for all models). Nothing is claimed about Agarwal Brothers.
 */

$category = 'Analytical Balance';
$brand    = 'Aczet';
$group    = 'CY-C Series';
$type     = 'Professional Analytical Balance';

$shortDescription = 'The Aczet CY-C Series Professional Analytical Balances read to 0.1 mg and cover six capacities from 60 g to 400 g: CY 64C, CY 124C, CY 224C, CY 304C, CY 324C and CY 404C. Motorised internal calibration with PSAC automatic recalibration, a backlit LCD, eleven built-in weighing applications and an IP 54 rated body with a glass draft shield make them dependable for routine weighing in pharma, chemical, food and research laboratories.';

$overview = '<p>The <strong>Aczet CY-C Series Professional Analytical Balance</strong> range is built for everyday laboratory weighing to 0.1 mg. Aczet positions analytical balances as the first choice for measuring the smallest sample quantities, and the CY-C Series offers six capacities, from the CY 64C (60 g) to the CY 404C (400 g), on the same compact body.</p>'
    . '<p><strong>Six models, one body</strong></p>'
    . '<ul>'
    . '<li><p>CY 64C: 60 g, CY 124C: 120 g and CY 224C: 220 g, with 0.2 mg linearity.</p></li>'
    . '<li><p>CY 304C: 300 g and CY 324C: 320 g, with 0.3 mg linearity.</p></li>'
    . '<li><p>CY 404C: 400 g, with 0.6 mg linearity.</p></li>'
    . '</ul>'
    . '<p>All six models read 0.1 mg with 0.1 mg repeatability, have a 90 mm diameter pan and a response time of 2 to 3 seconds.</p>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>The Mono-Tech weigh sensor is based on electromagnetic force compensation (EMFC). Motorised built-in weights provide internal calibration, and the Perfect Self Auto Calibration (PSAC) function calibrates automatically when time or temperature changes, so accuracy is kept in changing conditions. A capacity tracker on the display helps prevent overloading the weighing cell. Aczet states that the balances follow the USP requirements of chapters 41 and 1251.</p>'
    . '<p><strong>Built for daily use</strong></p>'
    . '<p>The glass draft shield opens on three sides and the body has an IP 54 protection class against dust and water splashes. The draft shield parts can be taken apart for cleaning, a round bubble level and adjustable feet make levelling quick, and a protective cover is supplied. The large backlit LCD stays readable in any room lighting.</p>'
    . '<p><strong>Applications and records</strong></p>'
    . '<p>Eleven built-in applications include piece counting, percentage weighing, formulation, pipette check, animal weighing, custom unit, check weighing, totalization, density, statistics and below-balance weighing. A built-in clock supports GLP documentation, an RS 232C interface is provided, and USB (Windows direct communication) is optional.</p>'
    . '<p>The table on this page shows ranges for the whole CY-C series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Six capacities, 0.1 mg readability', 'text' => 'CY 64C, 124C, 224C, 304C, 324C and 404C cover 60 g to 400 g, all reading to 0.1 mg.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised built-in weights plus PSAC calibrate automatically when time or temperature changes.'],
    ['title' => 'IP 54 body, glass draft shield', 'text' => 'Protection against dust and water splashes, with a spacious three-side opening draft shield.'],
    ['title' => 'Eleven built-in applications', 'text' => 'Counting, percentage, formulation, pipette check, check weighing, density, statistics and more.'],
    ['title' => 'Backlit LCD and GLP records', 'text' => 'A large backlit LCD and a built-in clock support clear readings and time-stamped GLP documents.'],
];

$advantages = [
    ['title' => 'Right capacity, same readability', 'text' => 'Pick the capacity you need from 60 g to 400 g and keep 0.1 mg readability.'],
    ['title' => 'Overload awareness', 'text' => 'The built-in capacity tracker helps prevent overloading the weighing cell.'],
    ['title' => 'Stays accurate in changing conditions', 'text' => 'PSAC recalibrates on time or temperature changes without a manual step.'],
    ['title' => 'Easy to clean and level', 'text' => 'Removable draft shield parts, a bubble level and large adjustable feet save time.'],
    ['title' => 'Weigh below the balance', 'text' => 'The below-balance weighing function suits oversized, irregular, hot, cold or magnetic samples.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CY-C series?', 'answer' => 'The Aczet datasheet lists six models: CY 64C (60 g), CY 124C (120 g), CY 224C (220 g), CY 304C (300 g), CY 324C (320 g) and CY 404C (400 g). All read to 0.1 mg.'],
    ['question' => 'What is the repeatability and linearity?', 'answer' => 'Repeatability is 0.1 mg on all six models. Linearity is 0.2 mg on the CY 64C, 124C and 224C, 0.3 mg on the CY 304C and 324C, and 0.6 mg on the CY 404C.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The minimum load is 10 mg. Aczet gives a typical minimum weight of 160 mg according to USP (0.1 percent) and 16 mg for U = 1 percent, the same for all models.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with built-in weights. The Perfect Self Auto Calibration (PSAC) function performs the calibration automatically when time or temperature changes.'],
    ['question' => 'How is it different from the CY-A touch-screen series?', 'answer' => 'The CY-C Series has an alphanumeric backlit LCD, while the CY-A Series is operated on a touch screen. Both read 0.1 mg and have motorised internal calibration.'],
    ['question' => 'Is the balance protected against dust and water?', 'answer' => 'Aczet states an IP 54 protection class for the CY-C Series, which protects against dust and splashes of water. It is not made for washing down or immersion.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. Aczet lists a CPR 02 dot-matrix data printer, an interface cable and a remote display among its accessories.'],
    ['question' => 'What accessories are available?', 'answer' => 'Aczet lists a data printer, density kit, E1 / E2 / F1 test weights, an anti-vibration pad, battery backup, remote display, IQ / OQ / PQ documents, an interface cable, an anti-thief lock and a specific gravity stand.'],
];

$documents = [
    ['title' => 'CY-C Series Brochure and Datasheet', 'type' => 'Brochure', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DC1.pdf'],
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
        ['model' => 'CY-C Professional Analytical', 'heading' => 'CY-C Professional Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CY 64C, CY 124C, CY 224C, CY 304C, CY 324C, CY 404C',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 400 (CY 64C: 60; CY 124C: 120; CY 224C: 220; CY 304C: 300; CY 324C: 320; CY 404C: 400)',
            'Minimum load (mg)'              => '10',
            'Repeatability, sd (mg)'         => '0.1',
            'Linearity (mg)'                 => '± 0.2 to ± 0.6 (CY 64C, CY 124C, CY 224C: 0.2; CY 304C, CY 324C: 0.3; CY 404C: 0.6)',
            'Tare range, subtractive (g)'    => 'Up to -400 (equal to the capacity of each model)',
            'USP minimum weight (mg)'        => '160 (USP, 0.1%, typical)',
            'Minimum weight, U = 1% (mg)'    => '16 (typical)',
            'Pan size (mm)'                  => 'Ø 90',
            'Response time (sec.)'           => '2 to 3',
            'Display'                        => 'Alphanumeric backlit LCD',
            'Calibration and adjustment'     => 'Motorised internal calibration',
            'Units'                          => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'     => '18 to 30',
            'Sensitivity drift (ppm/°C)'     => '± 2',
            'Interface'                      => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes'              => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Protection class'               => 'IP 54',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP & USP',
            'Balance size, W × D × H (mm)'   => '342.5 × 212 × 341 (chamber height: 239.5)',
            'Weight, approx. (kg)'           => '9.3',
        ]],
    ],
];
