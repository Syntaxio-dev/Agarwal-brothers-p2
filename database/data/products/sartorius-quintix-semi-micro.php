<?php

/**
 * Sartorius Quintix Semi-Micro - ONE product for the three Quintix semi-micro models (specs as ranges / per-model text).
 *
 * Values: Sartorius "Quintix and Secura Standard Laboratory Balances" brochure, page 22 (Technical Specifications - Semi-micro balances:
 * QUINTIX35-1x, QUINTIX65-1x, QUINTIX125D-1x, x = country-specific model) and the Quintix feature pages (2, 3, 5, 9, 10, 11).
 * The "-1S" code is the one used by the Sartorius shop and dealers.
 *
 * Corrections to the owner's pasted summary (checked against the brochure and dealer listings):
 *  - QUINTIX65 is 60 g at 0.01 mg (NOT 40 / 60 g).
 *  - QUINTIX125D is dual-range 60 g at 0.01 mg and 120 g at 0.1 mg (NOT 40 / 60 / 120 g).
 *  - QUINTIX35 is 30 g at 0.01 mg (as pasted).
 */

$category = 'Semi-Micro Lab Balances';
$brand    = 'Sartorius';
$group    = 'Quintix Series';
$type     = 'Semi-Micro Balance';

$shortDescription = 'The Sartorius Quintix semi-micro balances give 0.01 mg (five-decimal-place) readability in three models: QUINTIX35-1S (30 g), QUINTIX65-1S (60 g) and the dual-range QUINTIX125D-1S (60 g at 0.01 mg and 120 g at 0.1 mg). A touchscreen, the Start Assistant, isoCAL automatic internal adjustment, a built-in temperature management system and the One-Touch draft shield make precise weighing quick and easy in everyday laboratory work.';

$overview = '<p>The <strong>Sartorius Quintix Semi-Micro Balances</strong> are standard laboratory balances with <strong>0.01 mg</strong> readability for everyday precision weighing in research, education, chemical and quality control laboratories. Three models cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>QUINTIX35-1S:</strong> capacity 30 g, readability 0.01 mg.</p></li>'
    . '<li><p><strong>QUINTIX65-1S:</strong> capacity 60 g, readability 0.01 mg.</p></li>'
    . '<li><p><strong>QUINTIX125D-1S (dual-range):</strong> 60 g with 0.01 mg readability, and up to 120 g with 0.1 mg readability.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to use from day one</strong></p>'
    . '<p>The Start Assistant and the SmartTouch user interface let you operate the balance straight away. The convenient operating design with its easy-to-understand menu and the ergonomic balance design have already won several awards. Built-in standard applications guide you in simple steps through special lab tasks.</p>'
    . '<p><strong>Accurate in everyday conditions</strong></p>'
    . '<ul>'
    . '<li><p>A built-in <strong>temperature management system</strong> keeps the climate inside the weighing chamber stable.</p></li>'
    . '<li><p>The <strong>One-Touch draft shield</strong> is easy to use and compensates for even the slightest ambient effects.</p></li>'
    . '<li><p><strong>isoCAL</strong> adjusts the balance automatically, controlled by temperature and time, so results stay reliable.</p></li>'
    . '<li><p>The draft shield can be taken apart in a few steps and the rimmed base plate removed completely, which makes cleaning and preventing cross-contamination easy.</p></li>'
    . '</ul>'
    . '<p><strong>Simple data transfer</strong></p>'
    . '<p>Connect the balance to a computer or lab printer through the mini USB interface port with Plug &amp; Play. The PC-Direct feature sends results to a computer without additional software. A tamper-proof supervisor lock protects the system settings from unintentional changes.</p>'
    . '<p><strong>Performance and accessories</strong></p>'
    . '<p>Typical repeatability is 0.03 mg (0.03 mg and 0.07 mg on the dual-range model) and typical stabilization time is 4 seconds or less (4 seconds or less and 1.5 seconds or less on the dual-range model). Sartorius thermal printers for GxP printouts and an external rechargeable battery pack are available as accessories.</p>'
    . '<p>All three models have a Ø 80 mm weighing pan and a weighing chamber 218 mm high, and measure 218 × 376 × 316 mm (W × D × H). Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => '0.01 mg readability in three models', 'text' => 'QUINTIX35-1S (30 g), QUINTIX65-1S (60 g) and the dual-range QUINTIX125D-1S (60 g at 0.01 mg, 120 g at 0.1 mg).'],
    ['title' => 'Start Assistant and SmartTouch interface', 'text' => 'An award-winning, easy-to-understand menu structure lets you start weighing straight away.'],
    ['title' => 'Temperature management and One-Touch draft shield', 'text' => 'Keeps the weighing chamber stable and compensates for slight ambient effects, with a draft shield that is easy to use and clean.'],
    ['title' => 'isoCAL internal adjustment', 'text' => 'Temperature- and time-controlled calibration and adjustment keep results reliable without manual routines.'],
    ['title' => 'Plug & Play data transfer', 'text' => 'The mini USB port and PC-Direct send results to a computer or lab printer without additional software.'],
];

$advantages = [
    ['title' => 'Quick to learn', 'text' => 'Built-in applications guide staff in simple steps through special lab tasks.'],
    ['title' => 'Stable in difficult conditions', 'text' => 'Temperature management and the One-Touch draft shield keep results accurate with ambient influences.'],
    ['title' => 'Easy to clean', 'text' => 'The draft shield dismantles easily and the rimmed base plate catches spills and lifts out completely.'],
    ['title' => 'Protected settings', 'text' => 'A tamper-proof supervisor lock stops unintentional changes to the system settings.'],
    ['title' => 'A model for every need', 'text' => 'Choose 30 g, 60 g or the dual-range 60 g / 120 g model, all at 0.01 mg.'],
];

$faqs = [
    ['question' => 'Which Quintix semi-micro model should I choose?', 'answer' => 'QUINTIX35-1S weighs up to 30 g and QUINTIX65-1S up to 60 g, both at 0.01 mg. QUINTIX125D-1S is dual-range: 60 g at 0.01 mg and up to 120 g at 0.1 mg. Tell us your sample weights and our team will recommend one.'],
    ['question' => 'What is the pan size?', 'answer' => 'All three models have a Ø 80 mm weighing pan, a weighing chamber 218 mm high and dimensions of 218 × 376 × 316 mm (W × D × H).'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal calibration and adjustment function.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'Use the mini USB interface port with Plug & Play. The PC-Direct feature transfers results to a computer without extra software, and Sartorius lab printers can be connected through the interface port.'],
    ['question' => 'How do I keep the weighing chamber clean?', 'answer' => 'The draft shield can be dismantled in a few steps and the rimmed base plate removed completely. The plate is designed to catch all spills.'],
    ['question' => 'Can the settings be protected?', 'answer' => 'Yes. A tamper-proof supervisor lock protects the system settings of the balance from unintentional changes.'],
    ['question' => 'Where should a semi-micro balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and heavy foot traffic.'],
];

$documents = [
    ['title' => 'Quintix and Secura Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC6N3F2PHB8VWD5QJ7ZRTXC.pdf'],
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
        ['model' => 'Quintix Semi-Micro', 'heading' => 'Quintix Semi-Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'QUINTIX35-1S, QUINTIX65-1S, QUINTIX125D-1S',
            'Readability (mg)'               => '0.01 (QUINTIX35-1S, QUINTIX65-1S) / 0.01 and 0.1 (QUINTIX125D-1S)',
            'Weighing capacity (g)'          => '30 (QUINTIX35-1S) / 60 (QUINTIX65-1S) / 60 and 120 (QUINTIX125D-1S)',
            'Pan size (mm)'                  => 'Ø 80',
            'Repeatability, typical (mg)'    => '0.03 (QUINTIX35-1S, QUINTIX65-1S) / 0.03 and 0.07 (QUINTIX125D-1S)',
            'Stabilization time, typical (sec.)' => '≤ 4.0 (QUINTIX35-1S, QUINTIX65-1S) / ≤ 4.0 and ≤ 1.5 (QUINTIX125D-1S)',
            'Weighing chamber height (mm)'   => '218',
            'Dimensions, W × D × H (mm)'     => '218 × 376 × 316',
            'Weighing system'                => 'EMC',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Interfaces'                     => 'Mini USB (Plug & Play, PC-Direct)',
        ]],
    ],
];
