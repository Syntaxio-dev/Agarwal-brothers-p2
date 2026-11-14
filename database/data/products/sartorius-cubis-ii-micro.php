<?php

/**
 * Sartorius Cubis II Micro - ONE product for the whole micro line (owner's rule, 13 Nov 2026), specs as RANGES.
 *
 * Covers the standard micro modules (10.6S, 6.6S, 3.6P) and the high-capacity micro modules (36S, 36P, 66S, 66P, 116S).
 * Every value comes from the Sartorius "Cubis II Datasheet" (Version 12, 2023): page 5 "Micro Balances 0.001 mg"
 * and pages 6-7 "High-Capacity Micro Balances 0.001 - 0.002 mg". specs_general = true, so the product page says the values are
 * ranges for the whole line. Typical values are used for repeatability, linearity, stabilization and measurement time.
 *
 * Left out on purpose (owner to confirm):
 *  - Module 226S: the datasheet lists it under "Semi-Micro Balances 0.01 mg" (page 8: 220 g, 0.005 mg), so it belongs in the Semi-Micro category.
 *  - Full order codes such as MCA10.6S-2S00-M / MCA36S-3S00-D: only MCA6.6S-2S00-M/-F and MCA36S-3S00-MDS were found in shop listings,
 *    the others could not be confirmed, so the product names modules, not order codes.
 */

$category = 'Micro Lab Balances';
$brand    = 'Sartorius';
$group    = 'Cubis II Series';
$type     = 'Micro Balance';

$shortDescription = 'The Sartorius Cubis II micro balance range reads from 1 µg (0.001 mg) to 10 µg across capacities from 1.1 g up to 111 g. Standard micro modules (10.6S, 6.6S and the dual-range 3.6P) and high-capacity micro modules (36S, 36P, 66S, 66P and 116S) share one modular premium platform: you choose the display (MCA 7-inch colour touchscreen or MCE touchscreen), the draft shield and the software. Automatic internal adjustment (isoCAL) and built-in data integrity controls make it ready for demanding research, pharma and quality control weighing.';

$overview = '<p>The <strong>Sartorius Cubis II Micro Balances</strong> are modular premium balances for work where microgram readability matters: weighing small samples and filters, preparing standards, gravimetric and particulate analysis and low-dose pharmaceutical testing. One platform covers a wide range of capacities, from <strong>1.1 g to 111 g</strong>, with readability from <strong>1 µg (0.001 mg) to 10 µg</strong> depending on the weighing module.</p>'
    . '<p><strong>Standard micro modules</strong> (pan Ø 30 mm, filter pan Ø 50 mm)</p>'
    . '<ul>'
    . '<li><p><strong>10.6S:</strong> 10.1 g with 0.001 mg readability.</p></li>'
    . '<li><p><strong>6.6S:</strong> 6.1 g with 0.001 mg readability.</p></li>'
    . '<li><p><strong>3.6P (dual-range):</strong> 1.1 g / 2.1 g / 3.1 g with 0.001 mg / 0.002 mg / 0.005 mg readability.</p></li>'
    . '</ul>'
    . '<p><strong>High-capacity micro modules</strong> (pan Ø 50 mm)</p>'
    . '<ul>'
    . '<li><p><strong>36S:</strong> 32 g with 0.001 mg readability.</p></li>'
    . '<li><p><strong>36P:</strong> 10.1 g with 0.001 mg, or 32 g with 0.01 mg.</p></li>'
    . '<li><p><strong>66S:</strong> 61 g with 0.001 mg readability.</p></li>'
    . '<li><p><strong>66P:</strong> 12 g with 0.001 mg, or 61 g with 0.01 mg.</p></li>'
    . '<li><p><strong>116S:</strong> 111 g with 0.002 mg readability.</p></li>'
    . '</ul>'
    . '<p><strong>Choose your display and software</strong></p>'
    . '<p>The <strong>MCA</strong> has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp packages. The <strong>MCE</strong> has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.</p>'
    . '<p><strong>Draft shields and accessories</strong></p>'
    . '<p>Micro balances are offered with an automatic, motorized round glass draft shield (M) that learns your preferred way of working, or a manual stainless steel draft shield (F) made for weighing filters with diameters up to 50 mm. For the F draft shield, titanium filter weighing pans (52 mm and 75 mm) and a safe-lock tube holder for reaction tubes up to 2 mL are available as accessories. Which draft shield suits each module is confirmed when you order.</p>'
    . '<p><strong>Built for stable, traceable results</strong></p>'
    . '<ul>'
    . '<li><p>Monolithic weighing system with typical stabilization of 2.5 to 5 seconds, depending on the module.</p></li>'
    . '<li><p>Automatic internal adjustment (isoCAL) and automated motorized levelling.</p></li>'
    . '<li><p>Conductive-coated glass draft shields against electrostatic effects.</p></li>'
    . '<li><p>Technical controls for 21 CFR Part 11 compliance, an integrated audit trail and user management on the balance.</p></li>'
    . '</ul>'
    . '<p>Exact values differ from module to module, so the table on this page shows ranges for the whole line. Tell our team what you weigh and how much, and we will recommend the right module, display and draft shield and share the exact figures from the datasheet.</p>';

$features = [
    ['title' => '1 µg micro readability', 'text' => 'Eight weighing modules cover 1.1 g to 111 g, with readability from 1 µg (0.001 mg) up to 10 µg depending on the module.'],
    ['title' => 'Dual-range (P) modules', 'text' => 'The 3.6P, 36P and 66P modules give a finer readability at low load and a coarser one over the full capacity in a single balance.'],
    ['title' => 'Modular premium platform', 'text' => 'Choose the MCA 7-inch colour touchscreen with optional QApp software, or the MCE touchscreen for routine weighing, plus the draft shield that suits your samples.'],
    ['title' => 'Automatic internal adjustment (isoCAL)', 'text' => 'The balance adjusts itself after a temperature change of 1.5 K or after 12 hours, so results stay reliable without manual routines.'],
    ['title' => 'Data integrity built in', 'text' => 'Integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance, directly on the balance.'],
];

$advantages = [
    ['title' => 'One platform, many capacities', 'text' => 'Pick the module that fits your sample size today, from 1.1 g up to 111 g, on the same premium platform.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 2.5 to 5 seconds and typical measurement time is 6 to 10 seconds, depending on the module.'],
    ['title' => 'Protection from charged samples', 'text' => 'Conductive-coated glass draft shields reduce the effect of electrostatic charges on the result.'],
    ['title' => 'Easy to operate', 'text' => 'Guided workflows (QApps), automated motorized levelling and a teachable motorized draft shield reduce mistakes and training time.'],
    ['title' => 'Made for regulated labs', 'text' => 'Audit trail with 300,000 data points and alibi memory for 300,000 records support quality and compliance work.'],
];

$faqs = [
    ['question' => 'What readability and capacity range do the Cubis II Micro balances cover?', 'answer' => 'The micro modules cover capacities from 1.1 g to 111 g with readability from 0.001 mg (1 µg) to 0.01 mg (10 µg), depending on the module. Exact figures for each module are listed in the overview on this page.'],
    ['question' => 'How do I choose between the modules?', 'answer' => 'Start from the largest sample you will weigh and the readability you need. For example 10.6S (10.1 g) and 6.6S (6.1 g) read to 1 µg, while 116S carries up to 111 g at 2 µg. Our team will recommend the right module for your work.'],
    ['question' => 'What does the P in 3.6P, 36P and 66P mean?', 'answer' => 'These modules have two readability ranges in one balance. For example the 36P weighs 10.1 g at 0.001 mg or up to 32 g at 0.01 mg.'],
    ['question' => 'What is the difference between the MCA and MCE versions?', 'answer' => 'The MCA has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp software packages. The MCE has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.'],
    ['question' => 'Can the balance weigh filters?', 'answer' => 'Yes. The standard micro modules come with a Ø 30 mm pan and a Ø 50 mm filter pan, and the F draft shield is a manual stainless steel shield made for weighing filters with diameters up to 50 mm.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It uses automatic internal adjustment (isoCAL), which is triggered by a temperature change of 1.5 K or after 12 hours. Each module has its own recommended external test load, for example 10 g for the 10.6S.'],
    ['question' => 'Where should a micro balance be installed?', 'answer' => 'Indoors on a stable bench, away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations, heavy foot traffic, electromagnetic fields and very dry air.'],
    ['question' => 'Does it support regulated laboratory work?', 'answer' => 'Yes. The balance offers an integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance directly on the balance.'],
];

$documents = [
    ['title' => 'Cubis II Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC4MB7R3WZ9TQ5D8NH2VGXA.pdf'],
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
        ['model' => 'Cubis II Micro', 'heading' => 'Cubis II Micro Balance', 'batch' => 1, 'specs' => [
            'Weighing module'                => 'Standard micro: 10.6S, 6.6S, 3.6P / High-capacity micro: 36S, 36P, 66S, 66P, 116S',
            'Readability (mg)'               => '0.001 (1 µg) to 0.01 (10 µg)',
            'Weighing capacity (g)'          => '1.1 to 111',
            'Pan size (mm)'                  => 'Ø 30 (standard micro, filter pan Ø 50) / Ø 50 (high-capacity micro)',
            'Repeatability up to 5% load, typical (mg)' => '0.0005 to 0.0025',
            'Repeatability near Max, typical (mg)' => '0.0006 to 0.006',
            'Linearity, typical (mg)'        => '0.003 to 0.02',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '1',
            'Stabilization time, typical (sec.)' => '2.5 to 5',
            'Measurement time, typical (sec.)' => '6 to 10',
            'Minimum weight, USP, optimum (mg)' => '0.82 to 1.64',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Display'                        => 'MCA: 7" colour touch TFT / MCE: TFT touchscreen',
            'Weighing module size, L × W × H (mm)' => '340 × 139 × 129 (standard micro) / 486 or 510 × 240 × 302 (high-capacity micro)',
        ]],
    ],
];
