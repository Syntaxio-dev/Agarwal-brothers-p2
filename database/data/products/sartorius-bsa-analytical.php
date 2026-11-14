<?php

/**
 * Sartorius BSA / BSA II Analytical - ONE product covering both generations (specs written per generation).
 *
 * BSA II values: Sartorius "BSA II Basic Weighing. Done Right." brochure (English, api.sartorius.com document 297838), page 3, analytical
 * balances BSA224I-1x, BSA224-1x, BSA124I-1x, BSA124-1x, BSA64I-1x, BSA64-1x (x = country-specific model).
 * BSA values (BSA224S, BSA224S-CW, BSA124S, BSA124S-CW): the owner's BSA brochure, already loaded earlier (database/data/products/sartorius-bsa.php).
 * The owner's pasted list of models matched both sources. Note: the BSA II brochure prints the codes with "-1x" and a capital I, so the
 * Model row keeps that spelling.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'BSA / BSA II Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius BSA and BSA II analytical balances bring efficient, reliable routine weighing to the everyday lab: 60 g, 120 g or 220 g with 0.1 mg readability. A hybrid display, built-in application programs, USB Type-C and RS232 interfaces and automated internal calibration on the I-1x and -CW models make basic weighing simple, accurate and affordable.';

$overview = '<p>The <strong>Sartorius BSA and BSA II Analytical Balances</strong> are designed for the routine demands of laboratory work: quality control, education and everyday weighing at <strong>0.1 mg</strong>. German-designed and built for consistent readings, they let you weigh your sample and move on to the next task.</p>'
    . '<p><strong>Models</strong></p>'
    . '<ul>'
    . '<li><p><strong>BSA II (updated generation):</strong> BSA224I-1x and BSA224-1x (220 g), BSA124I-1x and BSA124-1x (120 g), BSA64I-1x and BSA64-1x (60 g). Models with I-1x have internal calibration and adjustment, models without it use external adjustment.</p></li>'
    . '<li><p><strong>BSA:</strong> BSA224S-CW and BSA224S (220 g), BSA124S-CW and BSA124S (120 g). The -CW models have a built-in motorized internal calibration weight, the others use external adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>Everyday weighing deserves the best technology</strong></p>'
    . '<ul>'
    . '<li><p>A <strong>hybrid display</strong> provides fast, accurate readings with up to 0.0001 g readability across the BSA II range of 60 g to 8,200 g.</p></li>'
    . '<li><p><strong>Integrated application programs</strong> support daily routines with clear instructions and intuitive indicators: weighing, dosing, counting, percentage weighing, mixing, components, animal weighing, calculation, density determination, statistics and mass unit conversions, plus an underfloor weighing feature for large samples.</p></li>'
    . '<li><p><strong>Automated calibration and adjustment</strong> on the I-1x models ensures maximum weighing accuracy and ease of use without manual test weights.</p></li>'
    . '</ul>'
    . '<p><strong>Connectivity and portability</strong></p>'
    . '<p>BSA II has a USB Type-C port that automatically detects Sartorius printers or displays, and an RS232 interface that keeps it compatible with existing systems. With the converter cable YCC-5V-15V the balance can also be used portably.</p>'
    . '<p><strong>Built to be dependable</strong></p>'
    . '<p>The BSA series uses a monolithic weigh cell that reduces drift and protects against overload, and a housing made of chemical-resistant PBT plastic. An ergonomic glass draft shield minimizes air-current interference and can be taken apart for thorough cleaning.</p>'
    . '<p>BSA II analytical models have a Ø 90 mm weighing pan and a weighing chamber 200 mm high, and measure 219 × 317 × 306 mm (W × D × H). Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => 'Hybrid display, 0.1 mg readability', 'text' => 'Fast, accurate readings on 60 g, 120 g and 220 g analytical models.'],
    ['title' => 'Automated internal calibration', 'text' => 'BSA II I-1x models and BSA -CW models calibrate with an internal motorized weight, with no manual test weights needed.'],
    ['title' => 'Integrated application programs', 'text' => 'Weighing, dosing, counting, percentage weighing, density determination, statistics and more guide your daily routines.'],
    ['title' => 'USB Type-C and RS232', 'text' => 'The USB Type-C port detects Sartorius printers and displays, and RS232 keeps the balance compatible with existing systems.'],
    ['title' => 'Portable design (BSA II)', 'text' => 'With the converter cable YCC-5V-15V the balance goes wherever you need it.'],
];

$advantages = [
    ['title' => 'Basic weighing, done right', 'text' => 'Consistent readings with each use, so you can weigh your sample and move on to the next task.'],
    ['title' => 'Monolithic weigh cell', 'text' => 'A single-block sensor gives long-term stability, fast response and high repeatability (BSA series).'],
    ['title' => 'Protected against overload', 'text' => 'Built-in overload protection safeguards the weigh cell from accidental force (BSA series).'],
    ['title' => 'Durable, easy to clean', 'text' => 'A chemical-resistant PBT housing and a glass draft shield that can be taken apart for cleaning.'],
    ['title' => 'Wide range in one family', 'text' => 'BSA II covers 60 g up to 8,200 g, so one family suits many weighing tasks.'],
];

$faqs = [
    ['question' => 'What is the difference between BSA and BSA II?', 'answer' => 'BSA II is the updated generation with a hybrid display, USB Type-C and RS232 interfaces and models with internal calibration marked I-1x. The earlier BSA analytical models are BSA224S, BSA224S-CW, BSA124S and BSA124S-CW.'],
    ['question' => 'What do I-1x and -CW mean?', 'answer' => 'BSA II models with I-1x in the code and BSA models ending in -CW have internal calibration and adjustment with a motorized internal weight. The others are calibrated with an external test weight.'],
    ['question' => 'Which capacities and readability are available?', 'answer' => 'Analytical models of 60 g (BSA II), 120 g and 220 g, all with 0.1 mg readability.'],
    ['question' => 'How fast does it stabilize?', 'answer' => 'The BSA II analytical models have a typical stabilization time of 2.0 seconds or less. The BSA analytical models have an average response time of 2.5 seconds.'],
    ['question' => 'How often should I calibrate the balance?', 'answer' => 'For routine daily use it is best practice to calibrate at least once a day and again if the room temperature changes significantly or the balance is moved to a new place.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'BSA II has a USB Type-C port that detects Sartorius printers and displays automatically, and an RS232 interface for existing systems.'],
    ['question' => 'How long should the balance warm up?', 'answer' => 'Keep the balance plugged in and let it warm up for at least 30 minutes before operation so that the internal components reach their optimum temperature.'],
];

$documents = [
    ['title' => 'BSA II Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGZD.pdf'],
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
        ['model' => 'BSA / BSA II Analytical', 'heading' => 'BSA / BSA II Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'BSA II: BSA224I-1x, BSA224-1x, BSA124I-1x, BSA124-1x, BSA64I-1x, BSA64-1x / BSA: BSA224S-CW, BSA224S, BSA124S-CW, BSA124S',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 220 (BSA II: 60 / 120 / 220; BSA: 120 / 220)',
            'Pan size (mm)'                  => 'Ø 90',
            'Repeatability, typical (mg)'    => '± 0.1',
            'Linearity (mg)'                 => '0.2 (BSA)',
            'Stabilization / response time (sec.)' => '≤ 2.0 typical (BSA II) / 2.5 average (BSA)',
            'Calibration and adjustment'     => 'Internal on I-1x and -CW models / External on other models',
            'Weighing chamber height (mm)'   => '200 (BSA II)',
            'Dimensions, W × D × H (mm)'     => '219 × 317 × 306 (BSA II)',
            'Interfaces'                     => 'USB Type-C and RS232 (BSA II)',
        ]],
    ],
];
