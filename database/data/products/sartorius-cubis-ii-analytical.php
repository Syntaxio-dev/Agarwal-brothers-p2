<?php

/**
 * Sartorius Cubis II Analytical - ONE product for the whole analytical line (owner's rule), specs as RANGES.
 *
 * Covers the modules 524S, 524P, 324S, 324P, 224S and 124S of the Sartorius "Cubis II Datasheet" (Version 12, 2023), page 9
 * (Analytical Balances 0.1 mg). The owner's pasted list of modules and ranges matched the datasheet.
 * Typical values are used for repeatability, linearity, stabilization and measurement time.
 *
 * Left out on purpose (owner to confirm): the MTS order codes of the paste (MCA524S-I-MTS, MCA224S-I-MTS, MCE224S-U-MTS) could not be
 * confirmed and "MTS" is never expanded. Order codes such as MCA224S-2S00-U / -I and MCE124S -A / -I / -U exist in dealer listings.
 * A dealer lists a Cubis III generation as well, so the owner should check with Sartorius which Cubis generation is current.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'Cubis II Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius Cubis II analytical balance range reads to 0.1 mg across capacities from 80 g up to 520 g, with polyrange P modules that give 0.2 mg and 0.5 mg at higher loads. One modular premium platform lets you choose the weighing module (524S, 524P, 324S, 324P, 224S, 124S), the display (MCA 7-inch colour touchscreen or MCE touchscreen) and the draft shield (manual, automatic or automatic with ionizer). Fast stabilization, isoCAL internal adjustment and built-in data integrity controls make it ready for demanding research, pharma and quality control weighing.';

$overview = '<p>The <strong>Sartorius Cubis II Analytical Balances</strong> are modular premium balances for everyday analytical weighing at <strong>0.1 mg</strong>, from preparing standards and samples to formulation, quality control and research. Six weighing modules cover capacities from <strong>80 g to 520 g</strong>, so one platform fits many applications.</p>'
    . '<p><strong>Weighing modules</strong></p>'
    . '<ul>'
    . '<li><p><strong>524S:</strong> 520 g with 0.1 mg readability.</p></li>'
    . '<li><p><strong>524P (polyrange):</strong> 120 g at 0.1 mg, 240 g at 0.2 mg and 520 g at 0.5 mg.</p></li>'
    . '<li><p><strong>324S:</strong> 320 g with 0.1 mg readability.</p></li>'
    . '<li><p><strong>324P (polyrange):</strong> 80 g at 0.1 mg, 160 g at 0.2 mg and 320 g at 0.5 mg.</p></li>'
    . '<li><p><strong>224S:</strong> 220 g with 0.1 mg readability.</p></li>'
    . '<li><p><strong>124S:</strong> 120 g with 0.1 mg readability.</p></li>'
    . '</ul>'
    . '<p><strong>Display and software</strong></p>'
    . '<p>The <strong>MCA</strong> has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp packages. The <strong>MCE</strong> has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.</p>'
    . '<p><strong>Draft shields</strong></p>'
    . '<p>Choose a manual glass analytical draft shield with smooth-action doors (U), an automatic, motorized glass draft shield with a learning function (A), or the automatic shield with a built-in ionizer that removes electrostatic charge from samples (I).</p>'
    . '<p><strong>Built for fast, traceable results</strong></p>'
    . '<ul>'
    . '<li><p>Typical stabilization time of 1 second and typical measurement time of 3 seconds.</p></li>'
    . '<li><p>Automatic internal adjustment (isoCAL) and automated motorized levelling.</p></li>'
    . '<li><p>Technical controls for 21 CFR Part 11 compliance, an integrated audit trail and user management on the balance.</p></li>'
    . '</ul>'
    . '<p><strong>Minimum weight, connectivity and fleet management</strong></p>'
    . '<p>According to USP chapter 41 the optimum minimum weight is 82 mg, and the typical minimum weight is 82 to 100 mg depending on the module. The balances have an RS232 serial port, a USB-A port for Sartorius printers and USB sticks, and a USB-B port for a virtual COM port and direct PC communication. For MCA models the Ingenix Suite can manage a whole fleet of Cubis II balances across the laboratories of a network.</p>'
    . '<p>Exact values depend on the module, so the table on this page shows ranges for the whole line. Tell our team what you weigh and how much, and we will recommend the right module, display and draft shield and share the exact figures from the datasheet.</p>';

$features = [
    ['title' => '0.1 mg readability up to 520 g', 'text' => 'Six weighing modules from 80 g to 520 g, with 0.1 mg readability in every module.'],
    ['title' => 'Polyrange (P) modules', 'text' => 'The 524P and 324P read to 0.1 mg at the lower load, 0.2 mg at the middle range and 0.5 mg up to full capacity in one balance.'],
    ['title' => 'Modular premium platform', 'text' => 'Choose the MCA 7-inch colour touchscreen with optional QApp software, or the MCE touchscreen, and a manual, automatic or ionizer glass draft shield.'],
    ['title' => 'Fast and stable', 'text' => 'Typical stabilization time of 1 second and typical measurement time of 3 seconds keep analytical weighing efficient.'],
    ['title' => 'isoCAL and data integrity', 'text' => 'Automatic internal adjustment after a 1.5 K temperature change, plus audit trail, user management and 21 CFR Part 11 controls.'],
];

$advantages = [
    ['title' => 'One platform, many options', 'text' => 'Pick the module, display and draft shield that fit your samples, from 80 g to 520 g.'],
    ['title' => 'Protection from charged samples', 'text' => 'Conductive-coated glass draft shields and the optional built-in ionizer reduce electrostatic effects on the result.'],
    ['title' => 'Easy to operate', 'text' => 'Guided workflows (QApps), automated motorized levelling and a teachable motorized draft shield reduce mistakes and training time.'],
    ['title' => 'Made for regulated labs', 'text' => 'Audit trail with 300,000 data points and alibi memory for 300,000 records support quality and compliance work.'],
    ['title' => 'Built to last', 'text' => 'Die-cast aluminium, stainless steel and Optiwhite float glass construction suited to a precision laboratory.'],
];

$faqs = [
    ['question' => 'What capacity and readability do the Cubis II Analytical balances cover?', 'answer' => 'The modules cover 80 g to 520 g. Readability is 0.1 mg on every module, and the polyrange 524P and 324P modules also offer 0.2 mg and 0.5 mg at higher loads.'],
    ['question' => 'What does the P in 524P and 324P mean?', 'answer' => 'These modules have three weighing ranges in one balance. For example the 324P weighs up to 80 g at 0.1 mg, up to 160 g at 0.2 mg and up to 320 g at 0.5 mg.'],
    ['question' => 'What is the difference between the MCA and MCE versions?', 'answer' => 'The MCA has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp software packages. The MCE has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.'],
    ['question' => 'Which draft shield should I choose?', 'answer' => 'The U shield is a manual glass analytical shield. The A shield is an automatic, motorized glass shield with a learning function. The I shield is the automatic shield with a built-in ionizer for electrostatic samples.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It uses automatic internal adjustment (isoCAL), triggered by a temperature change of 1.5 K or after 12 hours (6 hours on the 524 modules). The recommended external test load ranges from 100 g to 500 g depending on the module.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'According to USP chapter 41 the optimum minimum weight is 82 mg and the typical minimum weight is 82 to 100 mg, depending on the module.'],
    ['question' => 'Where should an analytical balance be installed?', 'answer' => 'Indoors on a stable bench, away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations, heavy foot traffic, electromagnetic fields and very dry air.'],
    ['question' => 'Does it support regulated laboratory work?', 'answer' => 'Yes. The balance offers an integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance directly on the balance.'],
];

$documents = [
    ['title' => 'Cubis II Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGAD.pdf'],
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
        ['model' => 'Cubis II Analytical', 'heading' => 'Cubis II Analytical Balance', 'batch' => 1, 'specs' => [
            'Weighing module'                => '524S, 524P, 324S, 324P, 224S, 124S',
            'Readability (mg)'               => '0.1 to 0.5 (0.1 on all modules; polyrange 524P and 324P also 0.2 and 0.5)',
            'Weighing capacity (g)'          => '80 to 520',
            'Pan size (mm)'                  => '85 × 85',
            'Repeatability up to 5% load, typical (mg)' => '0.04 to 0.05',
            'Repeatability near Max, typical (mg)' => '0.05',
            'Linearity, typical (mg)'        => '0.13 to 0.2',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '1',
            'Stabilization time, typical (sec.)' => '1',
            'Measurement time, typical (sec.)' => '3',
            'Minimum weight, USP, optimum (mg)' => '82',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Recommended external test load (g)' => '100 to 500 (class E2)',
            'Display'                        => 'MCA: 7" colour touch TFT / MCE: TFT touchscreen',
            'Draft shield'                   => 'U: manual glass / A: automatic motorized glass / I: automatic with ionizer',
            'Weighing module size, L × W × H (mm)' => '425 × 240 × 373',
        ]],
    ],
];
