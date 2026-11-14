<?php

/**
 * Sartorius Cubis II Semi-Micro - ONE product for the whole semi-micro line (owner's rule), specs as RANGES.
 *
 * Covers the weighing modules 226S, 225S, 225P, 125S and 125P of the Sartorius "Cubis II Datasheet" (Version 12, 2023),
 * page 8 "Semi-Micro Balances 0.01 mg". specs_general = true, so the page says the values are ranges for the whole line.
 * Typical values are used for repeatability, linearity, stabilization and measurement time.
 *
 * Corrections to the owner's pasted summary (checked against the datasheet):
 *  - Module 225P is 120 g at 0.01 mg and 220 g at 0.1 mg (two ranges), NOT 60 / 120 / 220 g at 0.01 / 0.02 / 0.05 mg.
 *  - Module 125P is 60 g at 0.01 mg and 120 g at 0.1 mg (as pasted).
 *  - Module 226S (220 g, 0.005 mg) IS in the datasheet's Semi-Micro table (it was left out of the Micro product for that reason).
 * Order codes: only MCA225S-I-3-MTS (with the QP99 software package, per a dealer listing), MCA225S-2S00-A, MCA125S-2S00-A and MCA125P-2S00-A
 * were found; the other MTS codes of the paste could not be confirmed, so they are not listed. "MTS" is not expanded.
 */

$category = 'Semi-Micro Lab Balances';
$brand    = 'Sartorius';
$group    = 'Cubis II Series';
$type     = 'Semi-Micro Balance';

$shortDescription = 'The Sartorius Cubis II semi-micro balance range weighs from 60 g up to 220 g with readability from 0.005 mg (5 µg) to 0.01 mg, and dual-range P modules that switch to 0.1 mg for the higher load. One modular premium platform lets you choose the weighing module (226S, 225S, 225P, 125S, 125P), the display (MCA 7-inch colour touchscreen or MCE touchscreen) and the draft shield. isoCAL internal adjustment and built-in data integrity controls make it a precise, compliant partner for research, pharma and quality control laboratories.';

$overview = '<p>The <strong>Sartorius Cubis II Semi-Micro Balances</strong> are modular premium balances for weighing at <strong>0.01 mg</strong> and below, from small samples and reference standards to tablets, filters and gravimetric analysis. Five weighing modules cover capacities from <strong>60 g to 220 g</strong>, so one platform fits many applications.</p>'
    . '<p><strong>Weighing modules</strong></p>'
    . '<ul>'
    . '<li><p><strong>226S:</strong> 220 g with 0.005 mg (5 µg) readability, the finest semi-micro module.</p></li>'
    . '<li><p><strong>225S:</strong> 220 g with 0.01 mg readability.</p></li>'
    . '<li><p><strong>225P (dual-range):</strong> 120 g with 0.01 mg, or 220 g with 0.1 mg.</p></li>'
    . '<li><p><strong>125S:</strong> 120 g with 0.01 mg readability.</p></li>'
    . '<li><p><strong>125P (dual-range):</strong> 60 g with 0.01 mg, or 120 g with 0.1 mg.</p></li>'
    . '</ul>'
    . '<p><strong>Display and software</strong></p>'
    . '<p>The <strong>MCA</strong> has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp packages. The <strong>MCE</strong> has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages. A ready-configured example is the MCA225S-I-3-MTS, offered with the QP99 software package.</p>'
    . '<p><strong>Draft shields</strong></p>'
    . '<p>Choose an automatic, motorized glass draft shield with a learning function (A), the same shield with a built-in ionizer to remove electrostatic charge from samples (I), or a manual glass analytical draft shield (U). For semi-micro balances the motorized draft shield function or the ionizer can also be activated after purchase.</p>'
    . '<p><strong>Built for stable, traceable results</strong></p>'
    . '<ul>'
    . '<li><p>Typical stabilization time of 1.5 to 2 seconds and typical measurement time of 4 to 6 seconds.</p></li>'
    . '<li><p>Automatic internal adjustment (isoCAL) and automated motorized levelling.</p></li>'
    . '<li><p>Technical controls for 21 CFR Part 11 compliance, an integrated audit trail and user management on the balance.</p></li>'
    . '</ul>'
    . '<p><strong>Minimum weight, connectivity and fleet management</strong></p>'
    . '<p>According to USP chapter 41 the optimum minimum weight is 4.1 mg for the 226S and 8.2 mg for the other modules, with a typical minimum weight of 8 to 13 mg. The balances have an RS232 serial port, a USB-A port for Sartorius printers and USB sticks, and a USB-B port for a virtual COM port and direct PC communication. For MCA models the Ingenix Suite can manage a whole fleet of Cubis II balances across the laboratories of a network.</p>'
    . '<p>Exact values depend on the module, so the table on this page shows ranges for the whole line. Tell our team what you weigh and how much, and we will recommend the right module, display and draft shield and share the exact figures from the datasheet.</p>';

$features = [
    ['title' => '0.005 to 0.01 mg readability', 'text' => 'Five weighing modules from 60 g to 220 g, with 0.01 mg readability and an ultra-fine 0.005 mg (5 µg) on the 226S.'],
    ['title' => 'Dual-range (P) modules', 'text' => 'The 225P and 125P read to 0.01 mg at the lower load and 0.1 mg up to full capacity in one balance.'],
    ['title' => 'Modular premium platform', 'text' => 'Choose the MCA 7-inch colour touchscreen with optional QApp software, or the MCE touchscreen, and an automatic, ionizer or manual glass draft shield.'],
    ['title' => 'Upgradeable after purchase', 'text' => 'The motorized draft shield function or a built-in ionizer can be activated later on semi-micro balances, so the balance grows with your needs.'],
    ['title' => 'isoCAL and data integrity', 'text' => 'Automatic internal adjustment after a 1.5 K temperature change or 12 hours, plus audit trail, user management and 21 CFR Part 11 controls.'],
];

$advantages = [
    ['title' => 'One platform, many options', 'text' => 'Pick the module, display and draft shield that fit your samples, from 60 g to 220 g.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.5 to 2 seconds and typical measurement time is 4 to 6 seconds, depending on the module.'],
    ['title' => 'Protection from charged samples', 'text' => 'Conductive-coated glass draft shields and the optional built-in ionizer reduce electrostatic effects on the result.'],
    ['title' => 'Easy to operate', 'text' => 'Guided workflows (QApps), automated motorized levelling and a teachable motorized draft shield reduce mistakes and training time.'],
    ['title' => 'Made for regulated labs', 'text' => 'Audit trail with 300,000 data points and alibi memory for 300,000 records support quality and compliance work.'],
];

$faqs = [
    ['question' => 'What capacity and readability do the Cubis II Semi-Micro balances cover?', 'answer' => 'The modules cover 60 g to 220 g with readability from 0.005 mg (226S) and 0.01 mg (225S, 125S) up to 0.1 mg on the higher range of the dual-range 225P and 125P modules.'],
    ['question' => 'What does the P in 225P and 125P mean?', 'answer' => 'These modules have two ranges in one balance. For example the 125P weighs up to 60 g at 0.01 mg, and up to 120 g at 0.1 mg.'],
    ['question' => 'What is the difference between the MCA and MCE versions?', 'answer' => 'The MCA has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp software packages. The MCE has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.'],
    ['question' => 'Which draft shield should I choose?', 'answer' => 'The A shield is an automatic, motorized glass shield with a learning function. The I shield adds a built-in ionizer for electrostatic samples. The U shield is a manual glass analytical shield. The motorized function or the ionizer can also be activated after purchase on semi-micro balances.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It uses automatic internal adjustment (isoCAL), triggered by a temperature change of 1.5 K or after 12 hours. The recommended external test load is 100 g for the 125 modules and 200 g for the 225 and 226 modules.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'According to USP chapter 41 the optimum minimum weight is 4.1 mg for the 226S and 8.2 mg for the other semi-micro modules; the typical minimum weight is 8 to 13 mg.'],
    ['question' => 'Where should a semi-micro balance be installed?', 'answer' => 'Indoors on a stable bench, away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations, heavy foot traffic, electromagnetic fields and very dry air.'],
    ['question' => 'Does it support regulated laboratory work?', 'answer' => 'Yes. The balance offers an integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance directly on the balance.'],
];

$documents = [
    ['title' => 'Cubis II Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC6N3F2PHB8VWD5QJ7ZRTXA.pdf'],
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
        ['model' => 'Cubis II Semi-Micro', 'heading' => 'Cubis II Semi-Micro Balance', 'batch' => 1, 'specs' => [
            'Weighing module'                => '226S, 225S, 225P, 125S, 125P',
            'Readability (mg)'               => '0.005 to 0.1 (0.01 on single-range modules; dual-range P modules 0.01 and 0.1)',
            'Weighing capacity (g)'          => '60 to 220',
            'Pan size (mm)'                  => 'Ø 50 (226S) / Ø 90 (225S, 225P, 125S) / 85 × 85 (125P)',
            'Repeatability up to 5% load, typical (mg)' => '0.004 to 0.007',
            'Repeatability near Max, typical (mg)' => '0.015 to 0.02',
            'Linearity, typical (mg)'        => '0.03 to 0.1',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '1',
            'Stabilization time, typical (sec.)' => '1.5 to 2',
            'Measurement time, typical (sec.)' => '4 to 6',
            'Minimum weight, USP, optimum (mg)' => '4.1 to 8.2',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Recommended external test load (g)' => '100 to 200 (class E2)',
            'Display'                        => 'MCA: 7" colour touch TFT / MCE: TFT touchscreen',
            'Draft shield'                   => 'A: automatic motorized glass / I: automatic with ionizer / U: manual glass',
            'Weighing module size, L × W × H (mm)' => '301 × 240 × 301 / 404 × 240 × 373 (125P)',
        ]],
    ],
];
