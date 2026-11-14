<?php

/**
 * Sartorius Entris II Advanced (BCA) Precision - ONE product for the BCA 1 mg, 10 mg and 100 mg models.
 *
 * Values: Sartorius "Entris II Advanced Line Product Datasheet" (English, api.sartorius.com document 71459, pages 4-7) and the Entris II brochure
 * technical table (French, document 71467). Models with i have internal adjustment (isoCAL), models without i use external adjustment.
 * The owner's pasted list matched (subset, some variants without i were not listed): 1 mg BCA223, 323, 423, 623 (i and non-i) and BCA1203i
 * (internal only); 10 mg BCA822, 1202, 2202, 3202, 4202, 6202 (i and non-i); 100 mg BCA2201, 5201, 8201, 10201, 12201 (i and non-i).
 */

$category = 'Precision Lab Balances';
$brand    = 'Sartorius';
$group    = 'Entris II Advanced (BCA) Series';
$type     = 'Precision Balance';

$shortDescription = 'The Sartorius Entris II Advanced (BCA) precision balances weigh from 220 g up to 12,200 g with 1 mg, 10 mg or 100 mg readability and a typical stabilization time of 1.0 second or less. A graphic touch display, real-time level support, integrated protection systems, CalAuditTrail and 13 built-in applications add value to the Entris II range; "i" models also offer isoCAL internal adjustment.';

$overview = '<p>The <strong>Sartorius Entris II Advanced (BCA) Precision Balances</strong> are best-value laboratory balances for everyday precision weighing, with extra features that simplify daily work and documentation. Models with internal adjustment are marked <strong>i</strong> (for example BCA623i-1S); the others are adjusted with an external test weight.</p>'
    . '<p><strong>1 mg models with draft shield</strong> (Ø 120 mm pan)</p>'
    . '<ul>'
    . '<li><p>BCA223 (220 g), BCA323 (320 g), BCA423 (420 g) and BCA623 (620 g), each with internal (i) and external adjustment, and BCA1203i (1,200 g) with internal adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>10 mg models with open pan</strong> (182 × 182 mm pan)</p>'
    . '<ul>'
    . '<li><p>BCA822 (820 g), BCA1202 (1,200 g), BCA2202 (2,200 g), BCA3202 (3,200 g), BCA4202 (4,200 g) and BCA6202 (6,200 g), each with internal (i) and external adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>100 mg models with open pan</strong> (182 × 182 mm pan)</p>'
    . '<ul>'
    . '<li><p>BCA2201 (2,200 g), BCA5201 (5,200 g), BCA8201 (8,200 g), BCA10201 (10,200 g) and BCA12201 (12,200 g), each with internal (i) and external adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>What the Advanced line adds</strong></p>'
    . '<ul>'
    . '<li><p><strong>Real-time level support:</strong> an internal electronic level sensor monitors the level continuously, raises an alarm if it is not correct and guides you interactively through levelling.</p></li>'
    . '<li><p><strong>Integrated protection systems:</strong> three configurable levels decide which weighing data is valid, so only valid data is passed to external devices.</p></li>'
    . '<li><p><strong>CalAuditTrail:</strong> calibration processes are documented automatically with the calibration trigger and a date and time stamp.</p></li>'
    . '<li><p><strong>Graphic touch display:</strong> an intuitive graphic touch display and user management control access to the balance settings.</p></li>'
    . '</ul>'
    . '<p><strong>Quality built in and easy to connect</strong></p>'
    . '<p>A monolithic weigh cell, a fast stabilization time and parts made of hard-wearing PBT, stainless steel and glass give accurate, durable results that are easy to clean. The balance detects Sartorius accessories such as a printer or a second display automatically, and a USB Type-C interface, a 9-pin RS232 interface and the PC-Direct feature transfer weighing data straight into spreadsheets or documents. Thirteen built-in application programs and 13 languages are included. Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => 'Graphic touch display', 'text' => 'An intuitive graphic touch display with 13 built-in application programs and 13 languages.'],
    ['title' => 'Real-time level support', 'text' => 'An internal electronic level sensor monitors the level continuously, raises an alarm and guides you through levelling.'],
    ['title' => 'Integrated protection systems', 'text' => 'Three configurable levels make sure only valid weighing data is transferred to external devices.'],
    ['title' => 'CalAuditTrail documentation', 'text' => 'Calibration processes are documented automatically with the trigger and a date and time stamp.'],
    ['title' => 'isoCAL internal adjustment (i models)', 'text' => 'Fully automated, temperature- and time-controlled calibration and adjustment, unique in this weighing class.'],
];

$advantages = [
    ['title' => 'Best value in its class', 'text' => 'Premium German engineering and a wide model range from 220 g to 12,200 g at an accessible price.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.0 second or less on the 1 mg models and 0.9 seconds or less on the 10 mg and 100 mg models.'],
    ['title' => 'Controlled access', 'text' => 'User management protects the balance settings, and integrated protection ensures only valid data is processed.'],
    ['title' => 'Easy to clean', 'text' => 'Chemical-resistant PBT, stainless steel and glass parts and a wipe-clean design prevent cross-contamination.'],
    ['title' => 'Plug-and-play', 'text' => 'Sartorius accessories are detected automatically and PC-Direct sends results straight to your spreadsheet.'],
];

$faqs = [
    ['question' => 'What is the difference between the "i" and the non-"i" models?', 'answer' => 'Models with "i" in the code (for example BCA6202i-1S) have internal isoCAL calibration and adjustment. Models without it (for example BCA6202-1S) are adjusted with an external test weight.'],
    ['question' => 'Which capacities and readability are available?', 'answer' => '1 mg models from 220 g to 1,200 g, 10 mg models from 820 g to 6,200 g and 100 mg models from 2,200 g to 12,200 g.'],
    ['question' => 'Which models have a draft shield?', 'answer' => 'The 1 mg models have a draft shield, a Ø 120 mm pan and a weighing chamber 240 mm high. The 10 mg and 100 mg models have an open 182 × 182 mm pan.'],
    ['question' => 'What does the Advanced line add compared with the Essential line?', 'answer' => 'The Advanced (BCA) line adds real-time level support, integrated protection systems, CalAuditTrail, a graphic touch display and 13 built-in applications.'],
    ['question' => 'Which interfaces does it have?', 'answer' => 'A USB Type-C interface, a PC-USB (USB device) interface and a 9-pin RS232 interface. The PC-Direct feature sends results to a computer without extra software.'],
    ['question' => 'Where should a precision balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and electromagnetic fields.'],
];

$documents = [
    ['title' => 'Entris II Advanced Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC8N6V7EZR8X2ZN1TF6FGBD.pdf'],
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
        ['model' => 'Entris II Advanced Precision', 'heading' => 'Entris II Advanced (BCA) Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => '1 mg: BCA223, BCA323, BCA423, BCA623 (i and non-i), BCA1203i / 10 mg: BCA822, BCA1202, BCA2202, BCA3202, BCA4202, BCA6202 (i and non-i) / 100 mg: BCA2201, BCA5201, BCA8201, BCA10201, BCA12201 (i and non-i) (all -1S)',
            'Readability (mg)'               => '1 / 10 / 100',
            'Weighing capacity (g)'          => '220 to 12,200',
            'Pan size (mm)'                  => 'Ø 120 (1 mg models) / 182 × 182 (10 mg and 100 mg models)',
            'Repeatability at 5% load, typical (mg)' => '± 0.5 (1 mg) / ± 5 (10 mg) / ± 50 (100 mg)',
            'Linearity, typical (mg)'        => '± 0.6 (1 mg) / ± 6 (10 mg) / ± 60 (100 mg)',
            'Stabilization time, typical (sec.)' => '≤ 1.0 (1 mg models) / ≤ 0.9 (10 mg and 100 mg models)',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL) on "i" models / External on other models',
            'Display'                        => 'Graphic touch',
            'Interfaces'                     => 'USB Type-C, PC-USB, RS232 (9-pin)',
            'Weighing chamber height (mm)'   => '240 (1 mg models) / open pan (10 mg and 100 mg models)',
            'Dimensions, W × D × H (mm)'     => '219 × 317 × 345 (1 mg models) / 219 × 317 × 94 (10 mg and 100 mg models)',
        ]],
    ],
];
