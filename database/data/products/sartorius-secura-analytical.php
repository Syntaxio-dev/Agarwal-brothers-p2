<?php

/**
 * Sartorius Secura Analytical - ONE product for the three Secura analytical models (specs as ranges / per-model text).
 *
 * Values: Sartorius "Quintix and Secura Standard Laboratory Balances" brochure, page 22, Technical Specifications - Analytical balances
 * (SECURA124-1x, SECURA224-1x, SECURA324-1x, x = country-specific model) and the Secura feature pages (3, 5, 9). The "-1S" code is the one
 * used by the Sartorius shop and dealers. The owner's pasted list of three models matched the brochure.
 *
 * Left out on purpose: the "APC Pharma Compliance" label of the paste is not used anywhere in the Sartorius brochure, so it is not written.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'Secura Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius Secura analytical balances weigh 120 g, 220 g or 320 g with 0.1 mg readability and a typical stabilization time of 1.5 seconds or less. isoCAL internal adjustment with a Cal Audit Trail, SQmin USP monitoring, a password-protected menu with safety levels and a touchscreen with alphanumeric input make them dependable standard balances for regulated laboratories.';

$overview = '<p>The <strong>Sartorius Secura Analytical Balances</strong> are standard laboratory balances with <strong>0.1 mg</strong> readability, built for regulated areas such as pharma and quality control where results must be traceable. Three models cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>SECURA124-1S:</strong> capacity 120 g.</p></li>'
    . '<li><p><strong>SECURA224-1S:</strong> capacity 220 g.</p></li>'
    . '<li><p><strong>SECURA324-1S:</strong> capacity 320 g.</p></li>'
    . '</ul>'
    . '<p><strong>Made for regulated work</strong></p>'
    . '<ul>'
    . '<li><p><strong>isoCAL</strong> adjusts the balance automatically, controlled by temperature and time, and the internal Cal Audit Trail documents every calibration and adjustment, giving a fully traceable audit trail.</p></li>'
    . '<li><p><strong>SQmin</strong> checks that your sample is within the balance operating range for USP compliance, warns you when the sample is below the minimum net weight and blocks data transfer until it is permissible.</p></li>'
    . '<li><p>Password-protected menu settings and selectable safety levels (high, standard or low) control who can change what and which data is processed further.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to operate and connect</strong></p>'
    . '<p>An interactive touchscreen with alphanumeric input makes entering sample IDs simple. The Start Assistant and SmartTouch interface let staff weigh straight away, and the mini USB port with Plug &amp; Play and PC-Direct sends results to a computer or lab printer without additional software. Built-in applications turn special lab tasks into guided routines.</p>'
    . '<p><strong>Documentation and accessories</strong></p>'
    . '<p>Sample and batch identification, monitored data transfer and the PC-Direct feature support reliable records: results can be transferred, evaluated, saved and documented in line with legal requirements just by using Plug &amp; Play technology, without extra software or complicated settings. Sartorius thermal printers for GxP printouts on standard paper or label rolls and an external rechargeable battery pack are available as accessories.</p>'
    . '<p><strong>Weighing details</strong></p>'
    . '<p>Typical repeatability is 0.1 mg and typical stabilization time is 1.5 seconds or less. SECURA124-1S and SECURA224-1S have a Ø 90 mm weighing pan, a weighing chamber 209 mm high and measure 218 × 359 × 319 mm (W × D × H). SECURA324-1S has the same pan, a weighing chamber 218 mm high and measures 218 × 376 × 316 mm. Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => '0.1 mg readability up to 320 g', 'text' => 'Three models of 120 g, 220 g and 320 g with 0.1 mg readability and a stabilization time of 1.5 seconds or less.'],
    ['title' => 'isoCAL and Cal Audit Trail', 'text' => 'Temperature- and time-controlled internal adjustment, with every calibration and adjustment recorded for traceability.'],
    ['title' => 'SQmin USP monitoring', 'text' => 'Checks the minimum sample weight against the balance operating range and blocks data transfer for samples that are too small.'],
    ['title' => 'Protected menu and safety levels', 'text' => 'A password protects the menu settings, and high, standard or low safety levels control data transfer.'],
    ['title' => 'Touchscreen and Plug & Play', 'text' => 'Alphanumeric input for sample IDs and mini USB with PC-Direct for sending results to a computer without extra software.'],
];

$advantages = [
    ['title' => 'Designed for regulated areas', 'text' => 'Password-protected menu, safety levels, Cal Audit Trail and SQmin support controlled, traceable weighing.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.5 seconds or less for every model.'],
    ['title' => 'Simple data transfer', 'text' => 'Plug & Play through the mini USB port and PC-Direct send results to a computer without additional software.'],
    ['title' => 'Quick to learn', 'text' => 'The Start Assistant, SmartTouch interface and touchscreen help staff get going straight away.'],
    ['title' => 'A model for every load', 'text' => 'Choose 120 g, 220 g or 320 g, all at 0.1 mg.'],
];

$faqs = [
    ['question' => 'Which Secura analytical models are available?', 'answer' => 'SECURA124-1S (120 g), SECURA224-1S (220 g) and SECURA324-1S (320 g), all with 0.1 mg readability.'],
    ['question' => 'What is the pan size and chamber height?', 'answer' => 'All three have a Ø 90 mm weighing pan. The weighing chamber is 209 mm high on the 124 and 224 models and 218 mm high on the 324 model.'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal calibration and adjustment function. Each adjustment is documented by the internal Cal Audit Trail.'],
    ['question' => 'What is SQmin?', 'answer' => 'SQmin monitors the minimum sample weight so that results comply with the USP operating range. If the sample is below the minimum net weight, the balance warns you and temporarily blocks data transfer.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'Use the mini USB interface port with Plug & Play. The PC-Direct feature transfers results to a computer without extra software, and Sartorius lab printers can be connected through the interface port.'],
    ['question' => 'Can the settings be protected?', 'answer' => 'Yes. A password prevents changes to the menu settings, and you can choose a high, standard or low safety level for controlled data transfer.'],
    ['question' => 'Where should an analytical balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and heavy foot traffic.'],
];

$documents = [
    ['title' => 'Quintix and Secura Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGSD.pdf'],
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
        ['model' => 'Secura Analytical', 'heading' => 'Secura Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'SECURA124-1S, SECURA224-1S, SECURA324-1S',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '120 to 320 (SECURA124: 120 / SECURA224: 220 / SECURA324: 320)',
            'Pan size (mm)'                  => 'Ø 90',
            'Repeatability, typical (mg)'    => '0.1',
            'Stabilization time, typical (sec.)' => '≤ 1.5',
            'Weighing chamber height (mm)'   => '209 (SECURA124, SECURA224) / 218 (SECURA324)',
            'Dimensions, W × D × H (mm)'     => '218 × 359 × 319 (SECURA124, SECURA224) / 218 × 376 × 316 (SECURA324)',
            'Weighing system'                => 'EMC',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Display'                        => 'Touchscreen with alphanumeric input',
            'Interfaces'                     => 'Mini USB (Plug & Play, PC-Direct)',
        ]],
    ],
];
