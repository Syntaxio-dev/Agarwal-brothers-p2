<?php

/**
 * Sartorius Secura Semi-Micro - ONE product for the two Secura semi-micro models (specs as ranges / per-model text).
 *
 * Values: Sartorius "Quintix and Secura Standard Laboratory Balances" brochure, page 22, Technical Specifications - Semi-micro balances
 * (SECURA125-1x and SECURA225D-1x, x = country-specific model) and the Secura feature pages (5, 9, 10, 11). The "-1S" code is the one used by
 * the Sartorius shop and dealers.
 *
 * Corrections to the owner's pasted summary (checked against the brochure and dealer listings):
 *  - SECURA225D is dual-range 120 g at 0.01 mg and 220 g at 0.1 mg (two ranges), NOT 60 / 120 / 220 g.
 *  - SECURA26P-1OIN: no Sartorius brochure, shop page or dealer listing was found for any SECURA26P code, so it is NOT loaded.
 */

$category = 'Semi-Micro Lab Balances';
$brand    = 'Sartorius';
$group    = 'Secura Series';
$type     = 'Semi-Micro Balance';

$shortDescription = 'The Sartorius Secura semi-micro balances weigh up to 120 g (SECURA125-1S) or in two ranges, 120 g at 0.01 mg and 220 g at 0.1 mg (SECURA225D-1S dual-range). A built-in temperature management system, the One-Touch draft shield, isoCAL internal adjustment and SQmin USP monitoring make them dependable standard balances for regulated laboratories, with a touchscreen and Plug & Play data transfer.';

$overview = '<p>The <strong>Sartorius Secura Semi-Micro Balances</strong> are standard laboratory balances with five-decimal-place readability (<strong>0.01 mg</strong>), built for regulated areas such as pharma and quality control. Two models cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>SECURA125-1S:</strong> single range, capacity 120 g, readability 0.01 mg.</p></li>'
    . '<li><p><strong>SECURA225D-1S (dual-range):</strong> 120 g with 0.01 mg readability, and up to 220 g with 0.1 mg readability.</p></li>'
    . '</ul>'
    . '<p><strong>Accurate in everyday conditions</strong></p>'
    . '<ul>'
    . '<li><p>A built-in <strong>temperature management system</strong> keeps the climate inside the weighing chamber stable.</p></li>'
    . '<li><p>The <strong>One-Touch draft shield</strong> is easy to use and compensates for even the slightest ambient effects.</p></li>'
    . '<li><p>The draft shield can be taken apart in a few steps and the rimmed base plate removed completely, and the plate is designed to catch all spills, which makes cleaning and preventing cross-contamination easy.</p></li>'
    . '</ul>'
    . '<p><strong>Made for regulated work</strong></p>'
    . '<ul>'
    . '<li><p><strong>isoCAL</strong> adjusts the balance automatically, controlled by temperature and time, and the internal Cal Audit Trail documents every calibration and adjustment.</p></li>'
    . '<li><p><strong>SQmin</strong> checks that your sample is within the balance operating range for USP compliance, warns you when the sample is below the minimum net weight and blocks data transfer until it is permissible.</p></li>'
    . '<li><p>Password-protected menu settings and selectable safety levels (high, standard or low) control who can change what and which data is processed further.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to operate and connect</strong></p>'
    . '<p>An interactive touchscreen with alphanumeric input makes entering sample IDs simple. The Start Assistant and SmartTouch interface let staff weigh straight away, and the mini USB port with Plug &amp; Play and PC-Direct sends results to a computer or lab printer without additional software.</p>'
    . '<p><strong>Performance and accessories</strong></p>'
    . '<p>Typical repeatability is 0.03 mg (0.03 mg and 0.07 mg on the dual-range model) and typical stabilization time is 4 seconds or less (4 seconds or less and 1.5 seconds or less on the dual-range model). Sartorius thermal printers for GxP printouts and an external rechargeable battery pack are available as accessories.</p>'
    . '<p>Both models have a Ø 80 mm weighing pan and a weighing chamber 218 mm high, and measure 218 × 376 × 316 mm (W × D × H). Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => '0.01 mg readability, up to 220 g', 'text' => 'SECURA125-1S weighs 120 g at 0.01 mg; the dual-range SECURA225D-1S adds 220 g at 0.1 mg.'],
    ['title' => 'Temperature management and One-Touch draft shield', 'text' => 'Keeps the weighing chamber stable and compensates for slight ambient effects, with a draft shield that is easy to use and clean.'],
    ['title' => 'isoCAL internal adjustment and Cal Audit Trail', 'text' => 'Temperature- and time-controlled internal adjustment, with every calibration and adjustment recorded for traceability.'],
    ['title' => 'SQmin USP monitoring', 'text' => 'Checks the minimum sample weight against the balance operating range and blocks data transfer for samples that are too small.'],
    ['title' => 'Protected menu and safety levels', 'text' => 'A password protects the menu settings, and high, standard or low safety levels control data transfer.'],
];

$advantages = [
    ['title' => 'Designed for regulated areas', 'text' => 'Password-protected menu, safety levels, Cal Audit Trail and SQmin support controlled, traceable weighing.'],
    ['title' => 'Stable in difficult conditions', 'text' => 'Temperature management and the One-Touch draft shield keep results accurate with ambient influences.'],
    ['title' => 'Simple data transfer', 'text' => 'Plug & Play through the mini USB port and PC-Direct send results to a computer without additional software.'],
    ['title' => 'Easy to clean', 'text' => 'The draft shield dismantles easily and the rimmed base plate catches spills and lifts out completely.'],
    ['title' => 'Quick to learn', 'text' => 'The Start Assistant, SmartTouch interface and touchscreen with alphanumeric input help staff get going straight away.'],
];

$faqs = [
    ['question' => 'What is the difference between SECURA125-1S and SECURA225D-1S?', 'answer' => 'SECURA125-1S weighs up to 120 g at 0.01 mg. SECURA225D-1S is a dual-range balance: 120 g at 0.01 mg, and up to 220 g at 0.1 mg.'],
    ['question' => 'What is the pan size?', 'answer' => 'Both models have a Ø 80 mm weighing pan, a weighing chamber 218 mm high and dimensions of 218 × 376 × 316 mm (W × D × H).'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal calibration and adjustment function. Each adjustment is documented by the internal Cal Audit Trail.'],
    ['question' => 'What is SQmin?', 'answer' => 'SQmin monitors the minimum sample weight so that results comply with the USP operating range. If the sample is below the minimum net weight, the balance warns you and temporarily blocks data transfer.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'Use the mini USB interface port with Plug & Play. The PC-Direct feature transfers results to a computer without extra software, and Sartorius lab printers can be connected through the interface port.'],
    ['question' => 'How do I keep the weighing chamber clean?', 'answer' => 'The draft shield can be dismantled in a few steps and the rimmed base plate removed completely. The plate is designed to catch all spills.'],
    ['question' => 'Where should a semi-micro balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and heavy foot traffic.'],
];

$documents = [
    ['title' => 'Quintix and Secura Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC6N3F2PHB8VWD5QJ7ZRTXB.pdf'],
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
        ['model' => 'Secura Semi-Micro', 'heading' => 'Secura Semi-Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'SECURA125-1S, SECURA225D-1S',
            'Readability (mg)'               => '0.01 (SECURA125-1S) / 0.01 and 0.1 (SECURA225D-1S)',
            'Weighing capacity (g)'          => '120 (SECURA125-1S) / 120 and 220 (SECURA225D-1S)',
            'Pan size (mm)'                  => 'Ø 80',
            'Repeatability, typical (mg)'    => '0.03 (SECURA125-1S) / 0.03 and 0.07 (SECURA225D-1S)',
            'Stabilization time, typical (sec.)' => '≤ 4.0 (SECURA125-1S) / ≤ 4.0 and ≤ 1.5 (SECURA225D-1S)',
            'Weighing chamber height (mm)'   => '218',
            'Dimensions, W × D × H (mm)'     => '218 × 376 × 316',
            'Weighing system'                => 'EMC',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Display'                        => 'Touchscreen with alphanumeric input',
            'Interfaces'                     => 'Mini USB (Plug & Play, PC-Direct)',
        ]],
    ],
];
