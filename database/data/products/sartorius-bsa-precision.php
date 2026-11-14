<?php

/**
 * Sartorius BSA / BSA II Precision - ONE product covering both generations (specs written per generation).
 *
 * BSA II values: Sartorius "BSA II Basic Weighing. Done Right." brochure (English, api.sartorius.com document 297838), page 3: milligram balances
 * BSA623I-1x / BSA623-1x, 423, 323, 223; precision balances BSA6202, 4202, 3202, 2202, 822 (10 mg) and BSA8201, 5201, 2201 (100 mg), each with an
 * internal (I-1x) and an external adjustment version (x = country-specific model).
 * BSA values (BSA623S ... BSA8201, each with a -CW version): the owner's BSA brochure, already loaded earlier (database/data/products/sartorius-bsa.php,
 * batch 2: 24 models). The owner's pasted list of models matched both sources.
 */

$category = 'Precision Lab Balances';
$brand    = 'Sartorius';
$group    = 'BSA / BSA II Series';
$type     = 'Precision Balance';

$shortDescription = 'The Sartorius BSA and BSA II precision balances bring efficient, reliable routine weighing to the everyday lab: from 220 g up to 8,200 g with 1 mg, 10 mg or 100 mg readability. A hybrid display, built-in application programs, USB Type-C and RS232 interfaces and automated internal calibration on the I-1x and -CW models make basic weighing simple, accurate and affordable.';

$overview = '<p>The <strong>Sartorius BSA and BSA II Precision Balances</strong> are designed for the routine demands of laboratory work: quality control, education and everyday precision weighing. German-designed and built for consistent readings, they let you weigh your sample and move on to the next task.</p>'
    . '<p><strong>BSA II (updated generation)</strong>: each model comes with internal calibration (I-1x) or external adjustment (no I)</p>'
    . '<ul>'
    . '<li><p><strong>1 mg, with draft shield (Ø 120 mm pan):</strong> BSA623 (620 g), BSA423 (420 g), BSA323 (320 g) and BSA223 (220 g).</p></li>'
    . '<li><p><strong>10 mg, open pan (182 × 182 mm):</strong> BSA6202 (6,200 g), BSA4202 (4,200 g), BSA3202 (3,200 g), BSA2202 (2,200 g) and BSA822 (820 g).</p></li>'
    . '<li><p><strong>100 mg, open pan (182 × 182 mm):</strong> BSA8201 (8,200 g), BSA5201 (5,200 g) and BSA2201 (2,200 g).</p></li>'
    . '</ul>'
    . '<p><strong>BSA (earlier generation)</strong>: each model comes with a built-in motorized internal weight (-CW) or external adjustment</p>'
    . '<ul>'
    . '<li><p><strong>1 mg:</strong> BSA623S, BSA423S, BSA323S and BSA223S. <strong>10 mg:</strong> BSA822 and BSA6202S, BSA4202S, BSA3202S, BSA2202S. <strong>100 mg:</strong> BSA8201, BSA5201 and BSA2201, each also as -CW.</p></li>'
    . '</ul>'
    . '<p><strong>Everyday weighing deserves the best technology</strong></p>'
    . '<ul>'
    . '<li><p>A <strong>hybrid display</strong> provides fast, accurate readings across the BSA II range of 60 g up to 8,200 g.</p></li>'
    . '<li><p><strong>Integrated application programs</strong> support daily routines with clear instructions and intuitive indicators: weighing, dosing, counting, percentage weighing, mixing, components, animal weighing, calculation, density determination, statistics and mass unit conversions, plus an underfloor weighing feature for large samples.</p></li>'
    . '<li><p><strong>Automated calibration and adjustment</strong> on the I-1x and -CW models ensures maximum weighing accuracy and ease of use without manual test weights.</p></li>'
    . '</ul>'
    . '<p><strong>Connectivity and portability</strong></p>'
    . '<p>BSA II has a USB Type-C port that automatically detects Sartorius printers or displays, and an RS232 interface that keeps it compatible with existing systems. With the converter cable YCC-5V-15V the balance can also be used portably.</p>'
    . '<p><strong>Built to be dependable</strong></p>'
    . '<p>The BSA series uses a monolithic weigh cell that reduces drift and protects against overload, and a housing made of chemical-resistant PBT plastic. BSA II milligram models have a Ø 120 mm pan and a weighing chamber 200 mm high and measure 219 × 317 × 306 mm (W × D × H); the 10 mg and 100 mg models have a 182 × 182 mm open pan and measure 219 × 317 × 94 mm. Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => 'Hybrid display for fast readings', 'text' => 'Fast, accurate readings on 1 mg, 10 mg and 100 mg precision models.'],
    ['title' => 'Automated internal calibration', 'text' => 'BSA II I-1x models and BSA -CW models calibrate with an internal motorized weight, with no manual test weights needed.'],
    ['title' => 'Integrated application programs', 'text' => 'Weighing, dosing, counting, percentage weighing, density determination, statistics and more guide your daily routines.'],
    ['title' => 'USB Type-C and RS232', 'text' => 'The USB Type-C port detects Sartorius printers and displays, and RS232 keeps the balance compatible with existing systems.'],
    ['title' => 'Portable design (BSA II)', 'text' => 'With the converter cable YCC-5V-15V the balance goes wherever you need it.'],
];

$advantages = [
    ['title' => 'Basic weighing, done right', 'text' => 'Consistent readings with each use, so you can weigh your sample and move on to the next task.'],
    ['title' => 'Fast response', 'text' => 'Typical stabilization time of 1.5 seconds or less on BSA II 1 mg models and 1.0 second or less on the 10 mg and 100 mg models.'],
    ['title' => 'Monolithic weigh cell', 'text' => 'A single-block sensor gives long-term stability, fast response and high repeatability (BSA series).'],
    ['title' => 'Durable, easy to clean', 'text' => 'A chemical-resistant PBT housing and, on 1 mg models, a draft shield that can be taken apart for cleaning.'],
    ['title' => 'Wide range in one family', 'text' => 'BSA II covers 60 g up to 8,200 g, so one family suits many weighing tasks.'],
];

$faqs = [
    ['question' => 'What is the difference between BSA and BSA II?', 'answer' => 'BSA II is the updated generation with a hybrid display, USB Type-C and RS232 interfaces and models with internal calibration marked I-1x. The earlier BSA precision models are marked S, with -CW versions that have an internal motorized weight.'],
    ['question' => 'What do I-1x and -CW mean?', 'answer' => 'BSA II models with I-1x in the code and BSA models ending in -CW have internal calibration and adjustment. The others are calibrated with an external test weight.'],
    ['question' => 'Which capacities and readability are available?', 'answer' => '1 mg models from 220 g to 620 g, 10 mg models from 820 g to 6,200 g and 100 mg models from 2,200 g to 8,200 g.'],
    ['question' => 'Which models have a draft shield?', 'answer' => 'The 1 mg models have a draft shield. The 10 mg and 100 mg models have an open pan.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'BSA II has a USB Type-C port that detects Sartorius printers and displays automatically, and an RS232 interface for existing systems.'],
    ['question' => 'How long should the balance warm up?', 'answer' => 'Keep the balance plugged in and let it warm up for at least 30 minutes before operation so that the internal components reach their optimum temperature.'],
];

$documents = [
    ['title' => 'BSA II Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC8N6V7EZR8X2ZN1TF6FGZD.pdf'],
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
        ['model' => 'BSA / BSA II Precision', 'heading' => 'BSA / BSA II Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'BSA II: BSA623, 423, 323, 223 (1 mg); BSA6202, 4202, 3202, 2202, 822 (10 mg); BSA8201, 5201, 2201 (100 mg), each as I-1x and -1x / BSA: BSA623S, 423S, 323S, 223S; BSA822, 6202S, 4202S, 3202S, 2202S; BSA8201, 5201, 2201, each also as -CW',
            'Readability (mg)'               => '1 / 10 / 100',
            'Weighing capacity (g)'          => '220 to 8,200',
            'Pan size (mm)'                  => 'BSA II: Ø 120 (1 mg) / 182 × 182 (10 mg, 100 mg) / BSA: Ø 115 (1 mg), Ø 150 (BSA822), 180 × 180 (others)',
            'Repeatability, typical (mg)'    => '± 1 / ± 10 / ± 100 (BSA II); 1 / 10 or 20 / 100 (BSA)',
            'Stabilization / response time (sec.)' => 'BSA II: ≤ 1.5 (1 mg) / ≤ 1.0 (10 mg, 100 mg) typical; BSA: 1.0 to 1.3 average',
            'Calibration and adjustment'     => 'Internal on I-1x and -CW models / External on other models',
            'Weighing chamber height (mm)'   => '200 (BSA II 1 mg models) / open pan',
            'Dimensions, W × D × H (mm)'     => '219 × 317 × 306 (BSA II 1 mg) / 219 × 317 × 94 (BSA II 10 mg, 100 mg)',
            'Interfaces'                     => 'USB Type-C and RS232 (BSA II)',
        ]],
    ],
];
