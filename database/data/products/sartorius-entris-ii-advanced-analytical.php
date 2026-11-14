<?php

/**
 * Sartorius Entris II Advanced (BCA) Analytical - ONE product for the seven BCA analytical models.
 *
 * Values: Sartorius "Entris II Advanced Line Product Datasheet" (English, api.sartorius.com document 71459: page 4 models with internal
 * adjustment, page 3 features) and the Entris II brochure technical table (BCA64i, BCA64, BCA124i, BCA124, BCA224i, BCA224, BCA324i).
 * The owner's pasted list matched: there is no external-calibration BCA324 model. The datasheet states 13 built-in applications
 * (a French brochure says 12), so the datasheet figure is used.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'Entris II Advanced (BCA) Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius Entris II Advanced (BCA) analytical balances weigh 60 g, 120 g, 220 g or 320 g with 0.1 mg readability and a typical stabilization time of 1.5 seconds or less. A graphic touch display, real-time level support, integrated protection systems, CalAuditTrail and 13 built-in applications add value to the Entris II range; "i" models also offer isoCAL internal adjustment.';

$overview = '<p>The <strong>Sartorius Entris II Advanced (BCA) Analytical Balances</strong> are best-value laboratory balances for everyday analytical weighing at <strong>0.1 mg</strong>, with extra features that simplify daily work and documentation. Seven models cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>Internal adjustment (isoCAL):</strong> BCA324i-1S (320 g), BCA224i-1S (220 g), BCA124i-1S (120 g) and BCA64i-1S (60 g).</p></li>'
    . '<li><p><strong>External adjustment:</strong> BCA224-1S (220 g), BCA124-1S (120 g) and BCA64-1S (60 g).</p></li>'
    . '</ul>'
    . '<p><strong>What the Advanced line adds</strong></p>'
    . '<ul>'
    . '<li><p><strong>Real-time level support:</strong> an internal electronic level sensor monitors the level continuously, raises an alarm if it is not correct and guides you interactively through levelling.</p></li>'
    . '<li><p><strong>Integrated protection systems:</strong> three configurable levels decide which weighing data is valid, so only valid data is passed to external devices.</p></li>'
    . '<li><p><strong>CalAuditTrail:</strong> calibration processes are documented automatically with the calibration trigger and a date and time stamp.</p></li>'
    . '<li><p><strong>Graphic touch display:</strong> an intuitive graphic touch display and user management control access to the balance settings.</p></li>'
    . '</ul>'
    . '<p><strong>Quality built in</strong></p>'
    . '<p>A monolithic weigh cell, a fast stabilization time and parts made of hard-wearing PBT, stainless steel and glass give accurate, durable results that are easy to clean. Specially coated glass parts of the draft shield minimize errors caused by electrostatically charged samples. On the "i" models the fully automated isoCAL adjustment is controlled by internal temperature and time, with a notification if calibration is outside the normal range.</p>'
    . '<p><strong>Plug-and-play connectivity</strong></p>'
    . '<p>The balance detects Sartorius accessories such as a printer or a second display automatically. A USB Type-C interface, a 9-pin RS232 interface and the PC-Direct feature transfer weighing data straight into spreadsheets or documents. Thirteen built-in application programs and 13 languages are included, covering weighing, dosing, counting, percentage weighing, density determination, statistics, checkweighing and pipette smart test.</p>'
    . '<p>All models have a Ø 90 mm weighing pan and a weighing chamber 240 mm high, and measure 219 × 317 × 345 mm (W × D × H). Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => 'Graphic touch display', 'text' => 'An intuitive graphic touch display with 13 built-in application programs and 13 languages.'],
    ['title' => 'Real-time level support', 'text' => 'An internal electronic level sensor monitors the level continuously, raises an alarm and guides you through levelling.'],
    ['title' => 'Integrated protection systems', 'text' => 'Three configurable levels make sure only valid weighing data is transferred to external devices.'],
    ['title' => 'CalAuditTrail documentation', 'text' => 'Calibration processes are documented automatically with the trigger and a date and time stamp.'],
    ['title' => 'isoCAL internal adjustment (i models)', 'text' => 'Fully automated, temperature- and time-controlled calibration and adjustment, unique in this weighing class.'],
];

$advantages = [
    ['title' => 'Best value in its class', 'text' => 'Premium German engineering and a wide model range from 60 g to 320 g at an accessible price.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.5 seconds or less and typical measurement time is 2 seconds or less.'],
    ['title' => 'Controlled access', 'text' => 'User management protects the balance settings, and integrated protection ensures only valid data is processed.'],
    ['title' => 'Easy to clean', 'text' => 'Chemical-resistant PBT, stainless steel and glass parts and a wipe-clean design prevent cross-contamination.'],
    ['title' => 'Plug-and-play', 'text' => 'Sartorius accessories are detected automatically and PC-Direct sends results straight to your spreadsheet.'],
];

$faqs = [
    ['question' => 'What is the difference between the "i" and the non-"i" models?', 'answer' => 'Models with "i" in the code (for example BCA224i-1S) have internal isoCAL calibration and adjustment. Models without it (for example BCA224-1S) are adjusted with an external test weight.'],
    ['question' => 'Which capacities are available?', 'answer' => 'Analytical models of 60 g, 120 g and 220 g are offered with internal or external adjustment, and a 320 g model with internal adjustment (BCA324i-1S). All read to 0.1 mg.'],
    ['question' => 'What does the Advanced line add compared with the Essential line?', 'answer' => 'The Advanced (BCA) line adds real-time level support, integrated protection systems, CalAuditTrail, a graphic touch display and 13 built-in applications.'],
    ['question' => 'What is the recommended calibration weight?', 'answer' => 'For external calibration the recommended test weight is 200 g for the 324 and 224 models, 100 g for the 124 and 50 g for the 64.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'According to USP chapter 41 the optimum minimum initial weighing is 0.082 g and the typical minimum is 0.16 g.'],
    ['question' => 'Which interfaces does it have?', 'answer' => 'A USB Type-C interface, a PC-USB (USB device) interface and a 9-pin RS232 interface. The PC-Direct feature sends results to a computer without extra software.'],
    ['question' => 'Where should an analytical balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and electromagnetic fields.'],
];

$documents = [
    ['title' => 'Entris II Advanced Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGBD.pdf'],
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
        ['model' => 'Entris II Advanced Analytical', 'heading' => 'Entris II Advanced (BCA) Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'BCA324i-1S, BCA224i-1S, BCA224-1S, BCA124i-1S, BCA124-1S, BCA64i-1S, BCA64-1S',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 320 (BCA64: 60 / BCA124: 120 / BCA224: 220 / BCA324i: 320)',
            'Pan size (mm)'                  => 'Ø 90',
            'Repeatability at 5% load, typical (mg)' => '± 0.08',
            'Repeatability near Max, typical (mg)' => '± 0.1',
            'Linearity, typical (mg)'        => '± 0.06 (limit ± 0.2; ± 0.3 for BCA324i)',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '1',
            'Stabilization time, typical (sec.)' => '≤ 1.5',
            'Measurement time, typical (sec.)' => '≤ 2.0',
            'Minimum weight, USP, optimum (g)' => '0.082',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL) on "i" models / External on other models',
            'Recommended external test weight (g)' => '50 to 200 (class E2)',
            'Display'                        => 'Graphic touch',
            'Interfaces'                     => 'USB Type-C, PC-USB, RS232 (9-pin)',
            'Weighing chamber height (mm)'   => '240',
            'Dimensions, W × D × H (mm)'     => '219 × 317 × 345',
        ]],
    ],
];
