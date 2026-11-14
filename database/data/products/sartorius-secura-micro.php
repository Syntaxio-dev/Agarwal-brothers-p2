<?php

/**
 * Sartorius Secura Micro Balance, model SECURA26-1S (21 g x 0.002 mg), exact values (specs_general = false).
 *
 * Values: Sartorius "Quintix and Secura Standard Laboratory Balances" brochure (page 22, Technical Specifications - Micro balances,
 * model SECURA26-1x, where x = country-specific model) and its feature pages (5, 9, 10, 12, 13). Typical repeatability is printed as 0.004 mg in
 * the brochure (some resellers print 0.003 typical / 0.004 tolerance): the brochure value is used.
 *
 * Left out on purpose: the "SECURA26P" polyrange models from the owner's note. No Sartorius brochure, shop page or retailer listing
 * could be found for any SECURA26P code, so nothing is invented for it (owner to confirm the source).
 */

$category = 'Micro Lab Balances';
$brand    = 'Sartorius';
$group    = 'Secura Series';
$type     = 'Micro Balance';

$shortDescription = 'The Sartorius Secura micro balance weighs up to 21 g with a readability of 2 µg (0.002 mg) over the entire weighing range, giving six-decimal-place accuracy for the smallest quantities. A built-in temperature management system, the One-Touch draft shield, a titanium weighing pan with two titanium pan draft shield rings and isoCAL internal adjustment make it a dependable standard balance for regulated laboratories.';

$overview = '<p>The <strong>Sartorius Secura26-1S Micro Balance</strong> is a standard laboratory micro balance with a capacity of <strong>21 g</strong> and a readability of <strong>2 µg (0.002 mg)</strong> over the entire weighing range. It is designed for analytical weighing where six decimal places matter, for example weighing small samples, preparing standards and quality control in pharma, chemical and research laboratories.</p>'
    . '<p><strong>Built for accurate results in everyday conditions</strong></p>'
    . '<ul>'
    . '<li><p>A built-in <strong>temperature management system</strong> keeps the climate inside the weighing chamber stable.</p></li>'
    . '<li><p>The <strong>One-Touch draft shield</strong> is easy to use and compensates for even the slightest ambient effects.</p></li>'
    . '<li><p>The weighing chamber base plate comes off easily, so cleaning is simple.</p></li>'
    . '<li><p>A <strong>titanium weighing pan</strong> and two <strong>titanium pan draft shield rings</strong> are supplied as standard. The raised rim of the ring supports precise measurements even under a laboratory fume hood with interfering air flow, and titanium is non-magnetic and inert to aggressive substances.</p></li>'
    . '</ul>'
    . '<p><strong>Made for regulated work</strong></p>'
    . '<ul>'
    . '<li><p><strong>isoCAL</strong> adjusts the balance automatically, controlled by temperature and time, and the internal Cal Audit Trail documents every calibration and adjustment.</p></li>'
    . '<li><p><strong>SQmin</strong> checks that your sample is within the balance operating range for USP compliance, warns you when the sample is below the minimum net weight and blocks data transfer until it is permissible.</p></li>'
    . '<li><p>Password-protected menu settings and selectable safety levels (high, standard or low) control who can change what and which data is processed further.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to operate and connect</strong></p>'
    . '<p>An interactive touchscreen display with alphanumeric input makes it easy to enter complicated sample IDs, and the Start Assistant and SmartTouch interface let you start weighing straight away. Connect to a computer or lab printer through the mini USB port with Plug &amp; Play and the PC-Direct feature, without additional software.</p>'
    . '<p><strong>At a glance</strong></p>'
    . '<p>The balance has a Ø 50 mm weighing pan, a weighing chamber 218 mm high and measures 218 × 376 × 316 mm (W × D × H). Typical repeatability is 0.004 mg and typical stabilization time is 6 seconds or less. Our team can help you choose accessories and plan the installation.</p>';

$features = [
    ['title' => '2 µg readability, 21 g capacity', 'text' => 'Six-decimal-place weighing over the entire range, with a typical stabilization time of 6 seconds or less.'],
    ['title' => 'Titanium weighing pan and shield rings', 'text' => 'A non-magnetic, inert titanium pan and two pan draft shield rings are supplied as standard, so you can weigh protected even under a fume hood.'],
    ['title' => 'One-Touch draft shield and temperature management', 'text' => 'A convenient draft shield and a built-in temperature management system keep conditions in the weighing chamber stable.'],
    ['title' => 'isoCAL internal adjustment and Cal Audit Trail', 'text' => 'Temperature- and time-controlled internal adjustment, with every calibration and adjustment recorded for traceability.'],
    ['title' => 'SQmin USP monitoring', 'text' => 'Checks the minimum sample weight against the balance operating range and blocks data transfer for samples that are too small.'],
];

$advantages = [
    ['title' => 'Designed for regulated areas', 'text' => 'Password-protected menu, safety levels, Cal Audit Trail and SQmin support controlled, traceable weighing.'],
    ['title' => 'Stable in difficult conditions', 'text' => 'Temperature management, the One-Touch draft shield and titanium shield rings keep results accurate even with interfering air flow.'],
    ['title' => 'Simple data transfer', 'text' => 'Plug & Play through the mini USB port and PC-Direct send results to a computer without additional software.'],
    ['title' => 'Easy to clean', 'text' => 'The weighing chamber base plate is easy to remove and the titanium parts are inert.'],
    ['title' => 'Quick to learn', 'text' => 'The Start Assistant, SmartTouch interface and touchscreen with alphanumeric input help staff get going straight away.'],
];

$faqs = [
    ['question' => 'What is the capacity and readability of the Secura26-1S?', 'answer' => 'It weighs up to 21 g with a readability of 0.002 mg (2 µg) over the entire weighing range.'],
    ['question' => 'What is the pan size?', 'answer' => 'The weighing pan is Ø 50 mm, made of titanium. Two titanium pan draft shield rings are supplied with the balance.'],
    ['question' => 'How does the balance calibrate itself?', 'answer' => 'It uses isoCAL, a temperature- and time-controlled internal calibration and adjustment function. Each adjustment is documented by the internal Cal Audit Trail.'],
    ['question' => 'What is SQmin?', 'answer' => 'SQmin monitors the minimum sample weight so that results comply with the USP operating range. If the sample is below the minimum net weight, the balance warns you and temporarily blocks data transfer.'],
    ['question' => 'How do I connect it to a computer or printer?', 'answer' => 'Use the mini USB interface port with Plug & Play. The PC-Direct feature transfers results to a computer without extra software, and Sartorius lab printers can be connected through the interface port.'],
    ['question' => 'Can it be used under a fume hood?', 'answer' => 'The titanium pan draft shield rings supplied with the balance have a raised rim that supports precise measurements even when the balance is exposed to interfering air flow under a laboratory fume hood.'],
    ['question' => 'Where should a micro balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and heavy foot traffic.'],
];

$documents = [
    ['title' => 'Quintix and Secura Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC4MB7R3WZ9TQ5D8NH2VGXB.pdf'],
];

return [
    'category'          => $category,
    'brand'             => $brand,
    'model_group'       => $group,
    'product_type'      => $type,
    'specs_general'     => false,
    'short_description' => $shortDescription,
    'overview'          => $overview,
    'features'          => $features,
    'advantages'        => $advantages,
    'faqs'              => $faqs,
    'documents'         => $documents,

    'models' => [
        ['model' => 'SECURA26-1S', 'batch' => 1, 'specs' => [
            'Model'                          => 'SECURA26-1S',
            'Readability (mg)'               => '0.002 (2 µg)',
            'Weighing capacity (g)'          => '21',
            'Pan size (mm)'                  => 'Ø 50 (titanium)',
            'Repeatability, typical (mg)'    => '0.004',
            'Stabilization time, typical (sec.)' => '≤ 6.0',
            'Weighing chamber height (mm)'   => '218',
            'Dimensions, W × D × H (mm)'     => '218 × 376 × 316',
            'Weighing system'                => 'EMC',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Display'                        => 'Touchscreen with alphanumeric input',
            'Interfaces'                     => 'Mini USB (Plug & Play, PC-Direct)',
        ]],
    ],
];
