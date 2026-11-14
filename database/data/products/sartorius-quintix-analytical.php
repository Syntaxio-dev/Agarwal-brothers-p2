<?php

/**
 * Sartorius Quintix Analytical - ONE product for the three Quintix analytical models (specs as ranges / per-model text).
 *
 * Values: Sartorius "Quintix and Secura Standard Laboratory Balances" brochure, page 22, Technical Specifications - Analytical balances
 * (QUINTIX64-1x, QUINTIX124-1x, QUINTIX224-1x, x = country-specific model) and the Quintix feature pages (2, 3, 5, 9). The "-1S" code is the one
 * used for the other Quintix models by the Sartorius shop and dealers. The owner's pasted list of three models matched the brochure.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'Quintix Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius Quintix analytical balances give 0.1 mg readability in three capacities: 60 g, 120 g and 220 g, with a typical stabilization time of 1.5 seconds or less. A touchscreen, the Start Assistant, isoCAL automatic internal adjustment, built-in applications and Plug & Play data transfer make precise weighing quick and easy in everyday laboratory work.';

$overview = '<p>The <strong>Sartorius Quintix Analytical Balances</strong> are standard laboratory balances with <strong>0.1 mg</strong> readability for everyday analytical weighing in research, education, chemical and quality control laboratories. Three models cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>QUINTIX64-1S:</strong> capacity 60 g.</p></li>'
    . '<li><p><strong>QUINTIX124-1S:</strong> capacity 120 g.</p></li>'
    . '<li><p><strong>QUINTIX224-1S:</strong> capacity 220 g.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to use from day one</strong></p>'
    . '<p>The Start Assistant and the SmartTouch user interface let you operate the balance straight away and feel at ease with all the functions available. The easy-to-understand menu structure and the ergonomic design speed up your workflows right from the start, for error-free results. The convenient operating design with its easy-to-understand menu and the ergonomic balance design have already won several awards. Built-in standard applications turn special, often complicated, lab tasks into efficient routines and guide you in simple steps.</p>'
    . '<p><strong>Reliable results</strong></p>'
    . '<ul>'
    . '<li><p><strong>isoCAL</strong> adjusts the balance automatically, controlled by temperature and time, so temperature fluctuations do not distort your results.</p></li>'
    . '<li><p>A tamper-proof supervisor lock protects the system settings from unintentional changes.</p></li>'
    . '</ul>'
    . '<p><strong>Simple data transfer</strong></p>'
    . '<p>Connect the balance to a computer or lab printer through the mini USB interface port with Plug &amp; Play. The PC-Direct feature sends results to a computer without additional software, and monitored data transfer keeps your records reliable.</p>'
    . '<p><strong>Documentation and accessories</strong></p>'
    . '<p>Sample and batch identification, monitored data transfer and the PC-Direct feature support reliable records and let you transfer, evaluate and save weighing results in line with legal requirements without extra software or complicated settings. Sartorius thermal printers for GxP printouts on standard paper or label rolls and an external rechargeable battery pack are available as accessories.</p>'
    . '<p><strong>Weighing details</strong></p>'
    . '<p>All three models have a typical repeatability of 0.1 mg, a typical stabilization time of 1.5 seconds or less, a Ø 90 mm weighing pan and a weighing chamber 209 mm high. They measure 218 × 359 × 319 mm (W × D × H). Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => '0.1 mg readability in three models', 'text' => 'QUINTIX64-1S (60 g), QUINTIX124-1S (120 g) and QUINTIX224-1S (220 g), all with a stabilization time of 1.5 seconds or less.'],
    ['title' => 'Start Assistant and SmartTouch interface', 'text' => 'An award-winning, easy-to-understand menu structure lets you start weighing straight away.'],
    ['title' => 'isoCAL internal adjustment', 'text' => 'Temperature- and time-controlled calibration and adjustment keep results reliable without manual routines.'],
    ['title' => 'Built-in applications', 'text' => 'Standard applications guide you in simple steps through special lab tasks.'],
    ['title' => 'Plug & Play data transfer', 'text' => 'The mini USB port and PC-Direct send results to a computer or lab printer without additional software.'],
];

$advantages = [
    ['title' => 'Quick to learn', 'text' => 'Built-in applications and the Start Assistant guide staff in simple steps.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.5 seconds or less for every model.'],
    ['title' => 'Protected settings', 'text' => 'A tamper-proof supervisor lock stops unintentional changes to the system settings.'],
    ['title' => 'Reliable data', 'text' => 'Monitored data transfer and PC-Direct help keep your weighing records correct.'],
    ['title' => 'A model for every load', 'text' => 'Choose 60 g, 120 g or 220 g, all at 0.1 mg.'],
];

$faqs = [
    ['question' => 'Which Quintix analytical models are available?', 'answer' => 'QUINTIX64-1S (60 g), QUINTIX124-1S (120 g) and QUINTIX224-1S (220 g), all with 0.1 mg readability.'],
    ['question' => 'What is the pan size and chamber height?', 'answer' => 'All three models have a Ø 90 mm weighing pan and a weighing chamber 209 mm high, and measure 218 × 359 × 319 mm (W × D × H).'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal calibration and adjustment function.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'Use the mini USB interface port with Plug & Play. The PC-Direct feature transfers results to a computer without extra software, and Sartorius lab printers can be connected through the interface port.'],
    ['question' => 'Can the settings be protected?', 'answer' => 'Yes. A tamper-proof supervisor lock protects the system settings of the balance from unintentional changes.'],
    ['question' => 'Where should an analytical balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and heavy foot traffic.'],
];

$documents = [
    ['title' => 'Quintix and Secura Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGXD.pdf'],
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
        ['model' => 'Quintix Analytical', 'heading' => 'Quintix Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'QUINTIX64-1S, QUINTIX124-1S, QUINTIX224-1S',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 220 (QUINTIX64: 60 / QUINTIX124: 120 / QUINTIX224: 220)',
            'Pan size (mm)'                  => 'Ø 90',
            'Repeatability, typical (mg)'    => '0.1',
            'Stabilization time, typical (sec.)' => '≤ 1.5',
            'Weighing chamber height (mm)'   => '209',
            'Dimensions, W × D × H (mm)'     => '218 × 359 × 319',
            'Weighing system'                => 'EMC',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Interfaces'                     => 'Mini USB (Plug & Play, PC-Direct)',
        ]],
    ],
];
