<?php

/**
 * Sartorius Secura Precision - ONE product for the Secura milligram (1 mg) and precision (10 mg) models of the brochure.
 *
 * Values: Sartorius "Quintix and Secura Standard Laboratory Balances" brochure, pages 22-23: Milligram balances SECURA213-1x, SECURA313-1x,
 * SECURA513-1x, SECURA613-1x, SECURA1103-1x and Precision balances SECURA612-1x, SECURA1102-1x (x = country-specific model; "-1S" is the code
 * used by the Sartorius shop and dealers).
 *
 * Differences from the owner's pasted list (checked against the brochure and dealer listings):
 *  - SECURA1103-1S (1,100 g, 1 mg) is in the brochure but was not in the paste: added.
 *  - SECURA5102, SECURA3102, SECURA2102, SECURA6101 and SECURA3101 are NOT in the Sartorius brochure. Dealer / Sartorius-shop pages exist for some of
 *    them (several are listed as discontinued) but no specification could be read, so they are not loaded (owner to confirm which Secura
 *    precision models he really sells; the QUINTIX2102 / 3102 / 5102 / 6102 / 6101 numbers belong to the Quintix standard series).
 */

$category = 'Precision Lab Balances';
$brand    = 'Sartorius';
$group    = 'Secura Series';
$type     = 'Precision Balance';

$shortDescription = 'The Sartorius Secura precision balances weigh from 210 g up to 1,100 g: five milligram models with 1 mg readability and a draft shield, and two precision models with 10 mg readability and an open pan. isoCAL internal adjustment with a Cal Audit Trail, SQmin USP monitoring, a password-protected menu with safety levels and a touchscreen with alphanumeric input make them dependable standard balances for regulated laboratories.';

$overview = '<p>The <strong>Sartorius Secura Precision Balances</strong> are standard laboratory balances for regulated areas such as pharma and quality control where results must be traceable. Two groups cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>Milligram balances, 1 mg readability, with draft shield:</strong> SECURA213-1S (210 g), SECURA313-1S (310 g), SECURA513-1S (510 g), SECURA613-1S (610 g) and SECURA1103-1S (1,100 g).</p></li>'
    . '<li><p><strong>Precision balances, 10 mg readability, open pan:</strong> SECURA612-1S (610 g) and SECURA1102-1S (1,100 g).</p></li>'
    . '</ul>'
    . '<p><strong>Made for regulated work</strong></p>'
    . '<ul>'
    . '<li><p><strong>isoCAL</strong> adjusts the balance automatically, controlled by temperature and time, and the internal Cal Audit Trail documents every calibration and adjustment, giving a fully traceable audit trail.</p></li>'
    . '<li><p><strong>SQmin</strong> checks that your sample is within the balance operating range for USP compliance, warns you when the sample is below the minimum net weight and blocks data transfer until it is permissible.</p></li>'
    . '<li><p>Password-protected menu settings and selectable safety levels (high, standard or low) control who can change what and which data is processed further.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to operate and connect</strong></p>'
    . '<p>An interactive touchscreen with alphanumeric input makes entering sample IDs simple. The Start Assistant and SmartTouch interface let staff weigh straight away, and the mini USB port with Plug &amp; Play and PC-Direct sends results to a computer or lab printer without additional software. Sample and batch identification and monitored data transfer support reliable records, and Sartorius thermal printers for GxP printouts and an external rechargeable battery pack are available as accessories.</p>'
    . '<p><strong>Weighing details</strong></p>'
    . '<p>The milligram models have a typical repeatability of 1 mg, a typical stabilization time of 1.0 second or less, a Ø 120 mm weighing pan and a weighing chamber 209 mm high, and measure 218 × 359 × 319 mm (W × D × H). The precision models have a typical repeatability of 10 mg, a typical stabilization time of 0.9 seconds or less and a Ø 180 mm pan on an open weighing platform, and measure 218 × 359 × 94 mm. Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => '1 mg and 10 mg models up to 1,100 g', 'text' => 'Five milligram models from 210 g to 1,100 g and two 10 mg models of 610 g and 1,100 g.'],
    ['title' => 'isoCAL and Cal Audit Trail', 'text' => 'Temperature- and time-controlled internal adjustment, with every calibration and adjustment recorded for traceability.'],
    ['title' => 'SQmin USP monitoring', 'text' => 'Checks the minimum sample weight against the balance operating range and blocks data transfer for samples that are too small.'],
    ['title' => 'Protected menu and safety levels', 'text' => 'A password protects the menu settings, and high, standard or low safety levels control data transfer.'],
    ['title' => 'Touchscreen and Plug & Play', 'text' => 'Alphanumeric input for sample IDs and mini USB with PC-Direct for sending results to a computer without extra software.'],
];

$advantages = [
    ['title' => 'Designed for regulated areas', 'text' => 'Password-protected menu, safety levels, Cal Audit Trail and SQmin support controlled, traceable weighing.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.0 second or less on the milligram models and 0.9 seconds or less on the precision models.'],
    ['title' => 'Simple data transfer', 'text' => 'Plug & Play through the mini USB port and PC-Direct send results to a computer without additional software.'],
    ['title' => 'Quick to learn', 'text' => 'The Start Assistant, SmartTouch interface and touchscreen help staff get going straight away.'],
    ['title' => 'A model for every load', 'text' => 'Choose between 210 g and 1,100 g with 1 mg readability, or 610 g and 1,100 g with 10 mg readability.'],
];

$faqs = [
    ['question' => 'Which Secura precision models are available?', 'answer' => 'Milligram models SECURA213-1S (210 g), SECURA313-1S (310 g), SECURA513-1S (510 g), SECURA613-1S (610 g) and SECURA1103-1S (1,100 g) at 1 mg, and precision models SECURA612-1S (610 g) and SECURA1102-1S (1,100 g) at 10 mg.'],
    ['question' => 'Which models have a draft shield?', 'answer' => 'The five 1 mg milligram models have a draft shield and a weighing chamber 209 mm high. The two 10 mg precision models have an open weighing platform with a Ø 180 mm pan.'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal calibration and adjustment function. Each adjustment is documented by the internal Cal Audit Trail.'],
    ['question' => 'What is SQmin?', 'answer' => 'SQmin monitors the minimum sample weight so that results comply with the USP operating range. If the sample is below the minimum net weight, the balance warns you and temporarily blocks data transfer.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'Use the mini USB interface port with Plug & Play. The PC-Direct feature transfers results to a computer without extra software, and Sartorius lab printers can be connected through the interface port.'],
    ['question' => 'Can the settings be protected?', 'answer' => 'Yes. A password prevents changes to the menu settings, and you can choose a high, standard or low safety level for controlled data transfer.'],
];

$documents = [
    ['title' => 'Quintix and Secura Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC8N6V7EZR8X2ZN1TF6FGSD.pdf'],
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
        ['model' => 'Secura Precision', 'heading' => 'Secura Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => '1 mg: SECURA213-1S, SECURA313-1S, SECURA513-1S, SECURA613-1S, SECURA1103-1S / 10 mg: SECURA612-1S, SECURA1102-1S',
            'Readability (mg)'               => '1 (milligram models) / 10 (precision models)',
            'Weighing capacity (g)'          => '210 to 1,100 (SECURA213: 210 / 313: 310 / 513: 510 / 613: 610 / 1103: 1,100 / 612: 610 / 1102: 1,100)',
            'Pan size (mm)'                  => 'Ø 120 (milligram models) / Ø 180 (precision models)',
            'Repeatability, typical (mg)'    => '1 (milligram models) / 10 (precision models)',
            'Stabilization time, typical (sec.)' => '≤ 1.0 (milligram models) / ≤ 0.9 (precision models)',
            'Weighing chamber height (mm)'   => '209 (milligram models) / open pan (precision models)',
            'Dimensions, W × D × H (mm)'     => '218 × 359 × 319 (milligram models) / 218 × 359 × 94 (precision models)',
            'Weighing system'                => 'EMC',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Display'                        => 'Touchscreen with alphanumeric input',
            'Interfaces'                     => 'Mini USB (Plug & Play, PC-Direct)',
        ]],
    ],
];
