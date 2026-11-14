<?php

/**
 * Sartorius Cubis II High-Capacity - ONE product for the whole high-capacity line, specs as RANGES.
 *
 * Covers the high-capacity weighing modules of the Sartorius "Cubis II Datasheet" (Version 12, 2023), pages 14-15:
 * 70201S, 50201S, 36201S, 36201P, 20201S, 11201S (0.1 g), 70200S, 36200S (1 g) and 32202P (0.01 g / 0.1 g, round pan).
 * Typical values are used for repeatability, linearity, stabilization and measurement time.
 *
 * The owner's pasted list matched the datasheet for eight modules (capacities, readability, repeatability and linearity values) and it
 * missed module 32202P (4,200 g at 10 mg and 32,200 g at 100 mg, round Ø 233 mm pan), which the datasheet has: it is added.
 * Order codes such as MCE36201S-2S00-0, MCA20201S-2S00-0 and MCA32202P were seen in dealer picture file names (code 0 = flat stainless steel pan
 * without a draft shield, per the datasheet). The balance is shown with MCA or MCE displays.
 */

$category = 'High-Capacity Lab Balances';
$brand    = 'Sartorius';
$group    = 'Cubis II Series';
$type     = 'High-Capacity Balance';

$shortDescription = 'The Sartorius Cubis II high-capacity balance range weighs from 4.2 kg up to 70.2 kg with readability from 10 mg to 1 g on a robust stainless steel pan (400 × 300 mm on most modules). One modular premium platform lets you choose the weighing module and the display (MCA 7-inch colour touchscreen or MCE touchscreen). Stabilization in 1 to 2 seconds, isoCAL internal adjustment and built-in data integrity controls make it ready for demanding weighing in laboratories and production support.';

$overview = '<p>The <strong>Sartorius Cubis II High-Capacity Balances</strong> are modular premium balances for heavy samples and large loads: from <strong>4,200 g up to 70,200 g</strong>, with readability from <strong>10 mg to 1 g</strong>. They are built for formulation of large batches, incoming goods control, dosing and quality control where laboratory accuracy meets industrial capacity.</p>'
    . '<p><strong>Weighing modules</strong> (flat stainless steel pan 400 × 300 mm unless stated)</p>'
    . '<ul>'
    . '<li><p><strong>11201S:</strong> 11,200 g with 0.1 g (100 mg) readability. <strong>20201S:</strong> 20,200 g at 0.1 g. <strong>36201S:</strong> 36,200 g at 0.1 g. <strong>50201S:</strong> 50,200 g at 0.1 g. <strong>70201S:</strong> 70,200 g at 0.1 g.</p></li>'
    . '<li><p><strong>36201P (polyrange):</strong> 10,200 g at 0.1 g and 36,200 g at 1 g.</p></li>'
    . '<li><p><strong>36200S:</strong> 36,200 g with 1 g readability. <strong>70200S:</strong> 70,200 g at 1 g.</p></li>'
    . '<li><p><strong>32202P (polyrange, round Ø 233 mm pan):</strong> 4,200 g at 0.01 g (10 mg) and 32,200 g at 0.1 g.</p></li>'
    . '</ul>'
    . '<p><strong>Display and software</strong></p>'
    . '<p>The <strong>MCA</strong> has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp packages; for high-capacity balances it is offered with a short distance adapter. The <strong>MCE</strong> has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.</p>'
    . '<p><strong>Robust and open</strong></p>'
    . '<p>The modules have a flat stainless steel weighing pan without a draft shield, which makes loading heavy containers quick and easy. The weighing module measures 412 × 400 × 126 mm (412 × 400 × 159 mm for the 32202P) and weighs about 15.8 kg (17.1 kg for the 32202P).</p>'
    . '<p><strong>Built for fast, traceable results</strong></p>'
    . '<ul>'
    . '<li><p>Typical stabilization time of 1 to 2 seconds and typical measurement time of 1.2 to 2 seconds.</p></li>'
    . '<li><p>Automatic internal adjustment (isoCAL), triggered by a temperature change of 2 to 4 K, and automated motorized levelling.</p></li>'
    . '<li><p>Technical controls for 21 CFR Part 11 compliance, an integrated audit trail and user management on the balance.</p></li>'
    . '</ul>'
    . '<p><strong>Connectivity</strong></p>'
    . '<p>The balances have an RS232 serial port, a USB-A port for Sartorius printers and USB sticks, and a USB-B port for a virtual COM port and direct PC communication, so weighing data reaches your printer or computer easily.</p>'
    . '<p>Exact values depend on the module, so the table on this page shows ranges for the whole line. Tell our team what you weigh and how much, and we will recommend the right module and display and share the exact figures from the datasheet.</p>';

$features = [
    ['title' => '4.2 kg to 70.2 kg capacity', 'text' => 'Nine weighing modules for large samples, with readability from 10 mg to 1 g.'],
    ['title' => 'Polyrange (P) modules', 'text' => 'The 36201P and 32202P read to a finer step at lower loads and a coarser step up to full capacity in one balance.'],
    ['title' => 'Open stainless steel pan', 'text' => 'A flat, robust pan (400 × 300 mm, or Ø 233 mm on the 32202P) makes loading heavy containers quick and easy.'],
    ['title' => 'Modular premium platform', 'text' => 'Choose the MCA 7-inch colour touchscreen with optional QApp software, or the MCE touchscreen.'],
    ['title' => 'isoCAL and data integrity', 'text' => 'Automatic internal adjustment, plus audit trail, user management and 21 CFR Part 11 controls.'],
];

$advantages = [
    ['title' => 'Laboratory accuracy at large capacity', 'text' => 'Typical repeatability of 20 to 200 mg across loads of 4.2 kg to 70.2 kg.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1 to 2 seconds depending on the module.'],
    ['title' => 'Easy to operate', 'text' => 'Guided workflows (QApps) and automated motorized levelling reduce mistakes and training time.'],
    ['title' => 'Made for regulated labs', 'text' => 'Audit trail with 300,000 data points and alibi memory for 300,000 records support quality and compliance work.'],
    ['title' => 'Built to last', 'text' => 'Die-cast aluminium and stainless steel construction suited to demanding environments.'],
];

$faqs = [
    ['question' => 'What capacities and readability do the Cubis II High-Capacity balances cover?', 'answer' => 'Capacities run from 4,200 g to 70,200 g. Readability is 0.1 g on the 11201S, 20201S, 36201S, 50201S and 70201S modules, 1 g on the 36200S and 70200S, and the polyrange modules offer two steps (36201P: 0.1 g and 1 g; 32202P: 0.01 g and 0.1 g).'],
    ['question' => 'What does the P in 36201P and 32202P mean?', 'answer' => 'These modules have two weighing ranges in one balance. For example the 36201P weighs up to 10,200 g at 0.1 g and up to 36,200 g at 1 g.'],
    ['question' => 'What is the pan size?', 'answer' => 'A flat stainless steel pan of 400 × 300 mm on most modules, and a round Ø 233 mm pan on the 32202P.'],
    ['question' => 'What is the difference between the MCA and MCE versions?', 'answer' => 'The MCA has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp software packages. The MCE has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It uses automatic internal adjustment (isoCAL), triggered by a temperature change of 2 to 4 K or after 12 hours. The recommended external test load ranges from 10,000 g to 70,000 g depending on the module.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'According to USP chapter 41 the optimum minimum weight is 8.2 g for the 32202P, 82 g for the 0.1 g modules and 820 g for the 1 g modules.'],
    ['question' => 'Does it support regulated laboratory work?', 'answer' => 'Yes. The balance offers an integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance directly on the balance.'],
];

$documents = [
    ['title' => 'Cubis II Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC9P7W8FAS9Y3ZP2UG7FGCD.pdf'],
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
        ['model' => 'Cubis II High-Capacity', 'heading' => 'Cubis II High-Capacity Balance', 'batch' => 1, 'specs' => [
            'Weighing module'                => '70201S, 50201S, 36201S, 36201P, 20201S, 11201S, 70200S, 36200S, 32202P',
            'Readability (mg)'               => '10 to 1,000 (100 on most modules; 1,000 on 70200S and 36200S; polyrange 36201P 100 and 1,000, 32202P 10 and 100)',
            'Weighing capacity (g)'          => '4,200 to 70,200',
            'Pan size (mm)'                  => '400 × 300 (Ø 233 on 32202P)',
            'Repeatability up to 5% load, typical (mg)' => '20 to 200',
            'Repeatability near Max, typical (mg)' => '20 to 200',
            'Linearity, typical (mg)'        => '60 to 200',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '2 to 3',
            'Stabilization time, typical (sec.)' => '1 to 2',
            'Measurement time, typical (sec.)' => '1.2 to 2',
            'Minimum weight, USP, optimum (g)' => '8.2 to 820',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Recommended external test load (g)' => '10,000 to 70,000 (class F1)',
            'Display'                        => 'MCA: 7" colour touch TFT / MCE: TFT touchscreen',
            'Draft shield'                   => '0: flat stainless steel pan, no draft shield',
            'Weighing module size, L × W × H (mm)' => '412 × 400 × 126 (412 × 400 × 159 for 32202P)',
        ]],
    ],
];
