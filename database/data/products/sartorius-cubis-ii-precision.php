<?php

/**
 * Sartorius Cubis II Precision - ONE product for the whole precision line, specs as RANGES.
 *
 * Covers the precision weighing modules of the Sartorius "Cubis II Datasheet" (Version 12, 2023), pages 10-13:
 * 1 mg group (5203S, 5203P, 3203S, 2203S, 2203P, 1203S, 623S, 623P, 323S), 10 mg group (14202S, 14202P, 10202S, 8202S, 6202S, 6202P,
 * 5202S, 4202S, 2202S, 1202S) and 100 mg group (12201S, 8201S, 5201S). Typical values are used for repeatability, linearity,
 * stabilization and measurement time.
 *
 * The owner's pasted list was a subset: it left out the P (polyrange) modules and the 5202S and 1202S modules, which the datasheet has.
 * Full order codes (MCA5203S-2S00-E, MCA14202S-2S00-0 ...) could not be checked, so the product names modules, not order codes.
 * The datasheet draft shield codes: E = manual glass draft shield for precision balances, R = flat stainless steel pan draft shield,
 * 0 = flat stainless steel pan without draft shield.
 */

$category = 'Precision Lab Balances';
$brand    = 'Sartorius';
$group    = 'Cubis II Series';
$type     = 'Precision Balance';

$shortDescription = 'The Sartorius Cubis II precision balance range weighs from 150 g up to 14.2 kg with readability from 1 mg to 100 mg, and polyrange P modules that switch to coarser steps at higher loads. One modular premium platform lets you choose the weighing module, the display (MCA 7-inch colour touchscreen or MCE touchscreen) and the draft shield. Stabilization in 0.8 to 1 second, isoCAL internal adjustment and built-in data integrity controls make it ready for demanding research, pharma and quality control weighing.';

$overview = '<p>The <strong>Sartorius Cubis II Precision Balances</strong> are modular premium balances for precision weighing from <strong>150 g up to 14,200 g</strong>, with readability from <strong>1 mg to 100 mg</strong>: formulation, dosing, filling control, quality control and production support in laboratories. Dozens of weighing modules share one platform, so you choose the module that fits your sample size and readability.</p>'
    . '<p><strong>1 mg modules</strong> (pan 140 × 140 mm)</p>'
    . '<ul>'
    . '<li><p><strong>5203S:</strong> 5,200 g. <strong>3203S:</strong> 3,200 g. <strong>2203S:</strong> 2,200 g. <strong>1203S:</strong> 1,200 g. <strong>623S:</strong> 620 g. <strong>323S:</strong> 320 g.</p></li>'
    . '<li><p><strong>5203P (polyrange):</strong> 1,200 g at 1 mg, 2,400 g at 2 mg and 5,200 g at 5 mg. <strong>623P:</strong> 150 g at 1 mg, 300 g at 2 mg and 620 g at 5 mg. <strong>2203P:</strong> 1,010 g at 1 mg and 2,200 g at 10 mg.</p></li>'
    . '</ul>'
    . '<p><strong>10 mg modules</strong></p>'
    . '<ul>'
    . '<li><p><strong>14202S:</strong> 14,200 g. <strong>10202S:</strong> 10,200 g. <strong>8202S:</strong> 8,200 g. <strong>6202S:</strong> 6,200 g. <strong>5202S:</strong> 5,200 g. <strong>4202S:</strong> 4,200 g. <strong>2202S:</strong> 2,200 g. <strong>1202S:</strong> 1,200 g.</p></li>'
    . '<li><p><strong>14202P (polyrange):</strong> 3,500 g at 10 mg, 7,000 g at 20 mg and 14,200 g at 50 mg. <strong>6202P:</strong> 1,500 g at 10 mg, 3,000 g at 20 mg and 6,200 g at 50 mg.</p></li>'
    . '</ul>'
    . '<p><strong>100 mg modules</strong></p>'
    . '<ul>'
    . '<li><p><strong>12201S:</strong> 12,200 g. <strong>8201S:</strong> 8,200 g. <strong>5201S:</strong> 5,200 g.</p></li>'
    . '</ul>'
    . '<p><strong>Display and software</strong></p>'
    . '<p>The <strong>MCA</strong> has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp packages. The <strong>MCE</strong> has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.</p>'
    . '<p><strong>Draft shields and pans</strong></p>'
    . '<p>Precision modules can be fitted with a manual glass draft shield for precision balances, a flat stainless steel weighing pan draft shield that is removable and has no glass parts, or a flat stainless steel pan without a draft shield. Weighing pans are 140 × 140 mm or 206 × 206 mm depending on the module.</p>'
    . '<p><strong>Built for fast, traceable results</strong></p>'
    . '<ul>'
    . '<li><p>Typical stabilization time of 0.8 to 1 second and typical measurement time of 1 to 2 seconds.</p></li>'
    . '<li><p>Automatic internal adjustment (isoCAL) and automated motorized levelling.</p></li>'
    . '<li><p>Technical controls for 21 CFR Part 11 compliance, an integrated audit trail and user management on the balance.</p></li>'
    . '</ul>'
    . '<p>Exact values depend on the module, so the table on this page shows ranges for the whole line. Tell our team what you weigh and how much, and we will recommend the right module, display and draft shield and share the exact figures from the datasheet.</p>';

$features = [
    ['title' => '1 mg to 100 mg readability, 150 g to 14.2 kg', 'text' => 'A wide family of precision modules covering small formulation work up to heavy samples on one platform.'],
    ['title' => 'Polyrange (P) modules', 'text' => 'Modules such as 5203P, 623P and 14202P give fine readability at low load and coarser steps at higher load in a single balance.'],
    ['title' => 'Modular premium platform', 'text' => 'Choose the MCA 7-inch colour touchscreen with optional QApp software, or the MCE touchscreen, and the draft shield or open pan that suits your samples.'],
    ['title' => 'Fast and stable', 'text' => 'Typical stabilization time of 0.8 to 1 second and typical measurement time of 1 to 2 seconds keep precision weighing efficient.'],
    ['title' => 'isoCAL and data integrity', 'text' => 'Automatic internal adjustment after a temperature change of 1.5 to 2 K, plus audit trail, user management and 21 CFR Part 11 controls.'],
];

$advantages = [
    ['title' => 'One platform, many options', 'text' => 'Pick the module, display and draft shield that fit your samples, from 150 g to 14.2 kg.'],
    ['title' => 'Easy to clean', 'text' => 'The removable stainless steel pan draft shield has no glass parts, which suits routine cleaning.'],
    ['title' => 'Easy to operate', 'text' => 'Guided workflows (QApps) and automated motorized levelling reduce mistakes and training time.'],
    ['title' => 'Made for regulated labs', 'text' => 'Audit trail with 300,000 data points and alibi memory for 300,000 records support quality and compliance work.'],
    ['title' => 'Built to last', 'text' => 'Die-cast aluminium, stainless steel and Optiwhite float glass construction suited to a precision laboratory.'],
];

$faqs = [
    ['question' => 'What capacities and readability do the Cubis II Precision balances cover?', 'answer' => 'Capacities run from 150 g to 14,200 g. Readability is 1 mg on the 1 mg modules, 10 mg on the 10 mg modules and 100 mg on the 100 mg modules, and polyrange P modules also offer 2, 5, 20 or 50 mg steps at higher loads.'],
    ['question' => 'What does the P in 5203P, 623P or 14202P mean?', 'answer' => 'These modules have two or three weighing ranges in one balance. For example the 6202P weighs up to 1,500 g at 10 mg, up to 3,000 g at 20 mg and up to 6,200 g at 50 mg.'],
    ['question' => 'What is the difference between the MCA and MCE versions?', 'answer' => 'The MCA has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp software packages. The MCE has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.'],
    ['question' => 'Which draft shield or pan options are available?', 'answer' => 'A manual glass draft shield for precision balances, a removable flat stainless steel pan draft shield with no glass parts, or a flat stainless steel pan without a draft shield.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It uses automatic internal adjustment (isoCAL), triggered by a temperature change of 1.5 to 2 K or after 6 or 12 hours depending on the module. The recommended external test load ranges from 200 g to 14,000 g.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'According to USP chapter 41 the optimum minimum weight ranges from 0.82 g on the 1 mg modules to 8.2 g on the 10 mg modules and 82 g on the 100 mg modules.'],
    ['question' => 'Does it support regulated laboratory work?', 'answer' => 'Yes. The balance offers an integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance directly on the balance.'],
];

$documents = [
    ['title' => 'Cubis II Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC8N6V7EZR8X2ZN1TF6FGCD.pdf'],
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
        ['model' => 'Cubis II Precision', 'heading' => 'Cubis II Precision Balance', 'batch' => 1, 'specs' => [
            'Weighing module'                => '1 mg: 5203S, 5203P, 3203S, 2203S, 2203P, 1203S, 623S, 623P, 323S / 10 mg: 14202S, 14202P, 10202S, 8202S, 6202S, 6202P, 5202S, 4202S, 2202S, 1202S / 100 mg: 12201S, 8201S, 5201S',
            'Readability (mg)'               => '1 to 100 (P modules also 2, 5, 20 and 50)',
            'Weighing capacity (g)'          => '150 to 14,200',
            'Pan size (mm)'                  => '140 × 140 or 206 × 206 (depending on module)',
            'Repeatability up to 5% load, typical (mg)' => '0.4 to 20',
            'Repeatability near Max, typical (mg)' => '0.5 to 20',
            'Linearity, typical (mg)'        => '0.6 to 30',
            'Stabilization time, typical (sec.)' => '0.8 to 1',
            'Measurement time, typical (sec.)' => '1 to 2',
            'Minimum weight, USP, optimum (g)' => '0.82 to 82',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Recommended external test load (g)' => '200 to 14,000 (class E2)',
            'Display'                        => 'MCA: 7" colour touch TFT / MCE: TFT touchscreen',
            'Draft shield'                   => 'E: manual glass / R: removable stainless steel pan shield / 0: none',
            'Weighing module size, L × W × H (mm)' => '425 × 240 × 95 (open pan); 122, 284 or 373 high with draft shield',
        ]],
    ],
];
