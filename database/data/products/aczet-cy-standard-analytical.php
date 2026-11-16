<?php

/**
 * Aczet CY Series Standard Analytical Balance - ONE product for the whole CY standard line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet product page https://aczet.com/product/analytical-balance-1 ('Standard Level CY Series' tables: CY64, CY124, CY 224, CY 324 only)
 *  - Aczet datasheet "Standard Series" https://aczet.com/upload/product_documentation/DataSheet-_Analytical-Standard_(1).pdf
 *    (1 page, six models: CY 64, CY 124, CY 224, CY 304, CY 324, CY 404). The page was rendered and read. Spec values are taken from the
 *    DATASHEET (as agreed). The same datasheet is the document attached below.
 *  - Aczet has NO Standard analytical brochure: the 'Standard Analytical Balance' brochure link on the page is empty.
 *
 * Models found: CY 64, CY 124, CY 224, CY 304, CY 324, CY 404 = 6 models = the pasted list (all six confirmed by the datasheet).
 *
 * Differences between the Aczet product page and the datasheet (datasheet used):
 *  - Minimum load: page says 1 mg, datasheet says 10 mg (datasheet used).
 *  - CY 224 linearity: page says 0.3 mg, datasheet says 0.2 mg. CY 324 repeatability: page says 0.2 mg, datasheet says 0.1 mg for every model.
 *  - Chamber height: page says 228.5 mm, datasheet says 239.5 mm (this file uses 239.5; the page value is the CY-C one).
 *  - CY 304 and CY 404 are NOT on the product page, only in the datasheet.
 *  - The page's feature text (IP 54, three-side opening glass draft shield, USP chapters 41 / 1251, 10 built-in applications, USB and RS232
 *    as standard) describes the whole 'Analytical Balance' category and mixes in the CY-C Professional series, so it is NOT used here. The
 *    datasheet says 'RS 232C, USB optional'.
 *  - The page's 'Professional Level CY-C Series' (CY 64C ... CY 314C, motorised internal calibration) is a different product and is not
 *    in this file.
 * Not confirmed: whether this standard line has IP 54 or a three-side opening shield (the datasheet drawing shows a glass draft shield with
 * a chamber height of 239.5 mm, nothing more); order codes beyond the model names; the 21 CFR Part 11 PC software details (only its name is listed).
 * Also noted: other helper folders may be preparing single CY 224 / CY 124 products; this file is the whole standard line.
 * Nothing is claimed about Agarwal Brothers.
 */

$category = 'Analytical Balance';
$brand    = 'Aczet';
$group    = 'CY Series';
$type     = 'Standard Analytical Balance';

$shortDescription = 'The Aczet CY Series Standard Analytical Balances give you 0.1 mg readability for everyday precise weighing in the laboratory. Six models cover 60 g to 400 g: CY 64, CY 124, CY 224, CY 304, CY 324 and CY 404. An alphanumeric backlit LCD, external calibration with a test weight, an RS 232C interface (USB optional), a built-in set of application modes such as piece counting, percentage and check weighing, and a glass draft shield make them a practical choice for pharma, chemical, food and research labs that need reliable 0.1 mg weighing.';

$overview = '<p>The <strong>Aczet CY Series Standard Analytical Balance</strong> range is built for routine laboratory weighing where a readability of 0.1 mg is needed. All six models read to 0.1 mg and differ in capacity, from 60 g to 400 g, so you can choose the balance that fits your samples and containers without paying for features you do not need.</p>'
    . '<p><strong>Six models</strong></p>'
    . '<ul>'
    . '<li><p><strong>CY 64:</strong> 60 g capacity.</p></li>'
    . '<li><p><strong>CY 124:</strong> 120 g capacity.</p></li>'
    . '<li><p><strong>CY 224:</strong> 220 g capacity.</p></li>'
    . '<li><p><strong>CY 304 and CY 324:</strong> 300 g and 320 g capacity.</p></li>'
    . '<li><p><strong>CY 404:</strong> 400 g capacity.</p></li>'
    . '</ul>'
    . '<p>Every model has a minimum load of 10 mg, a 90 mm diameter pan, a response time of 2 to 3 seconds and a repeatability of 0.1 mg. Linearity is ± 0.2 mg up to 220 g, ± 0.3 mg for the 300 g and 320 g models and ± 0.6 mg for the 400 g model.</p>'
    . '<p><strong>Operation and calibration</strong></p>'
    . '<p>The alphanumeric backlit LCD is easy to read. The Standard Series is calibrated externally: you place a known test weight on the pan and follow the calibration routine, so the balance can be adjusted whenever your quality procedure requires it. Aczet lists test weights (E1, E2, F1), a density kit and an anti-vibration pad among the accessories.</p>'
    . '<p><strong>Built-in applications and records</strong></p>'
    . '<p>The balance supports piece counting, percentage weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation and an automatic statistics calculator, with many units of measure from grams and milligrams to carats and ounces. An RS 232C interface is provided, with USB (Windows direct communication) as an option, and the balance is listed with ISO, GLP / GMP and USP compliance. A data printer, remote display, battery backup, interface cable, anti-theft lock and 21 CFR Part 11 PC software are available as accessories.</p>'
    . '<p>The table on this page shows ranges for the whole CY series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => '0.1 mg readability, six capacities', 'text' => 'CY 64, CY 124, CY 224, CY 304, CY 324 and CY 404 cover 60 g to 400 g, all reading to 0.1 mg.'],
    ['title' => 'External calibration', 'text' => 'Calibrate with a known test weight whenever your procedure needs it. Aczet offers E1, E2 and F1 test weights as accessories.'],
    ['title' => 'Clear backlit display', 'text' => 'An alphanumeric backlit LCD is easy to read, with a response time of 2 to 3 seconds.'],
    ['title' => 'Built-in application modes', 'text' => 'Piece counting, percentage, check weighing, animal weighing, density determination, formulation and statistics are included.'],
    ['title' => 'RS 232C interface, USB optional', 'text' => 'Connect to a PC or printer; USB with Windows direct communication is available as an option.'],
];

$advantages = [
    ['title' => 'Right capacity, same readability', 'text' => 'Pick the capacity you need from 60 g to 400 g and keep 0.1 mg readability.'],
    ['title' => 'Simple, low-maintenance set-up', 'text' => 'External calibration needs only a test weight, with no built-in motorised weight to look after.'],
    ['title' => 'Many weighing tasks in one balance', 'text' => 'Counting, percentage, check weighing and density determination are built in, so one balance covers many routines.'],
    ['title' => 'Lab records and compliance', 'text' => 'Listed with ISO, GLP / GMP and USP compliance and RS 232C output for printing and data capture.'],
    ['title' => 'Plenty of accessories', 'text' => 'Printer, density kit, remote display, battery backup, anti-vibration pad, anti-theft lock and validation documents are available.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CY Standard series?', 'answer' => 'Aczet lists six models: CY 64 (60 g), CY 124 (120 g), CY 224 (220 g), CY 304 (300 g), CY 324 (320 g) and CY 404 (400 g). All read to 0.1 mg.'],
    ['question' => 'What is the difference between the CY Standard and CY-C Professional series?', 'answer' => 'Both read to 0.1 mg. The Standard series is calibrated externally with a test weight. The CY-C Professional series has motorised internal calibration. Ask our team if you want both compared for your application.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'The Standard Series uses external calibration: you calibrate manually with a predetermined test weight. Aczet offers E1, E2 and F1 test weights as accessories.'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability is 0.1 mg on every model. Linearity is ± 0.2 mg for the 60 g, 120 g and 220 g models, ± 0.3 mg for the 300 g and 320 g models and ± 0.6 mg for the 400 g model.'],
    ['question' => 'What is the minimum load and the minimum sample weight?', 'answer' => 'The minimum load is 10 mg. Aczet gives a typical minimum weight of 160 mg (USP, 0.1 %) and 16 mg (U = 1 %), the same for all six models.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. Aczet also lists a CPR 02 dot-matrix data printer and an interface cable among its accessories.'],
    ['question' => 'What pan size does it have and what are the dimensions?', 'answer' => 'The pan is 90 mm in diameter. The balance measures 342.5 x 212 x 341 mm (W x D x H) with a chamber height of 239.5 mm and weighs about 9.3 kg.'],
    ['question' => 'What operating conditions does it need?', 'answer' => 'Aczet gives the operating temperature as 18 to 30 °C. Place it on a stable bench; a Vibrosorb VRD-03 anti-vibration pad is available as an accessory.'],
];

$documents = [
    ['title' => 'CY Series Standard Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DS1.pdf'],
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
        ['model' => 'CY Standard Analytical', 'heading' => 'CY Series Standard Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CY 64, CY 124, CY 224, CY 304, CY 324, CY 404',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 400 (CY 64: 60; CY 124: 120; CY 224: 220; CY 304: 300; CY 324: 320; CY 404: 400)',
            'Minimum load (mg)'              => '10',
            'Repeatability (mg)'             => '0.1',
            'Linearity (mg)'                 => '± 0.2 to ± 0.6 (CY 64, CY 124, CY 224: 0.2; CY 304, CY 324: 0.3; CY 404: 0.6)',
            'Tare range, subtractive (g)'    => 'Up to -400 (CY 64: -60; CY 124: -120; CY 224: -220; CY 304: -300; CY 324: -320; CY 404: -400)',
            'Minimum weight, USP 0.1 %, typical (mg)' => '160',
            'Minimum weight, U = 1 %, typical (mg)'   => '16',
            'Pan size (mm)'                  => 'Ø 90',
            'Response time (sec.)'           => '2 to 3',
            'Display'                        => 'Alphanumeric backlit LCD',
            'Calibration and adjustment'     => 'External calibration',
            'Units'                          => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'     => '18 to 30',
            'Sensitivity drift (ppm/°C)'     => '± 2',
            'Interface'                      => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes'              => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP and USP',
            'Balance size, W × D × H (mm)'   => '342.5 × 212 × 341 (chamber height: 239.5)',
            'Weight, approx. (kg)'           => '9.3',
        ]],
    ],
];
