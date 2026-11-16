<?php

/**
Aczet CY-FC Series Flameproof Balance (internal calibration), ONE product, specs_general.
Sources (read 14 Nov 2026, aczet.com only): https://aczet.com/product/flame-proof-balance and datasheet https://aczet.com/upload/product_documentation/Flame_Proof_Balance_CY-F_Series.pdf (1 page, attached).
Certification wording is Aczet's: the page says CIMFR Dhanbad, Zone 1 & 2, Gas Group IIB, IS/IEC 60079-0:2011. The pasted 'IS/IEC 60079-1:2007' and 'certificate CIMFR/TC/P/4013' are NOT on the page and are not used.
Pictures: Aczet has one picture for CY-FC and CY-F; the extras are crops.
Models: Aczet lists ONE internal-calibration model, CY 224FC (datasheet prints CY 224F-C), 220 g x 0.1 mg. The pasted CY 513FC does NOT exist on Aczet (CY 513F is external calibration, see cy-f-flameproof) and is left out.
Doubts: USP minimum weights are from the page.
 */

$category = 'Flame Proof Balance';
$brand    = 'Aczet';
$group = 'CY-FC Series';
$type = 'Flameproof Analytical Balance (Internal Calibration)';

$shortDescription = 'The Aczet CY-FC Series Flameproof Balance is an analytical balance in a flameproof enclosure for hazardous areas. The CY 224FC reads 0.1 mg up to 220 g and has motorised internal calibration. Aczet states CIMFR Dhanbad certification for Zone 1 and 2, Gas Group IIB.';

$overview = '<p>The <strong>Aczet CY-FC Series Flameproof Balance</strong> is made for laboratories and plants where flammable gases or vapours may be present, such as chemical, pharmaceutical, oil and gas, mining and defence sites. The CY 224FC is the internal-calibration model: 220 g capacity, 0.1 mg readability and 0.1 mg repeatability.</p>'
    . '<p><strong>Flameproof construction</strong></p><p>The electronics sit in a flameproof cum weatherproof enclosure with a glass window. Aczet lists a cast aluminium rotary switch, water-resistant push buttons and double compression glands that keep hazardous gases or powders out of the enclosure. A protective cover is supplied.</p>'
    . '<p><strong>Certification (Aczet statement)</strong></p><p>Aczet states that CIMFR Dhanbad certification covers use in Zone 1 and 2 for Gas Group IIB as per IS/IEC 60079-0:2011. Please ask us for the certificate for the exact model before you plan an installation.</p>'
    . '<p><strong>Weighing technology</strong></p><p>The balance uses an electromagnetic force compensation (EMFC) weigh sensor and a built-in capacity tracker that helps prevent overloading. The motorised internal weight lets you calibrate without handling an external test weight.</p>'
    . '<p><strong>Everyday use</strong></p><p>A large backlit LCD, a built-in clock for GLP documents, a round bubble level and adjustable feet make set-up simple. Built-in applications include piece counting, percentage weighing, check weighing, density determination, formulation and statistics, and there is an RS 232C interface (USB optional).</p>'
    . '<p>Aczet also offers customised flameproof ranges on request. Tell our team your capacity and hazardous-area class and we will confirm the right model.</p>';

$features = [
    ['title' => '0.1 mg readability to 220 g', 'text' => 'CY 224FC reads 0.1 mg with 0.1 mg repeatability and 0.2 mg linearity.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Built-in weight calibration, no external test weight needed for routine adjustment.'],
    ['title' => 'Flameproof cum weatherproof enclosure', 'text' => 'Cast aluminium rotary switch, water-resistant push buttons and a glass window.'],
    ['title' => 'Double compression glands', 'text' => 'Aczet lists them to keep hazardous gases or powders out of the enclosure.'],
    ['title' => 'Backlit LCD, GLP clock', 'text' => 'Large readable display with a built-in clock for GLP documentation.'],
];

$advantages = [
    ['title' => 'For hazardous industries', 'text' => 'Designed for chemical, pharma, oil and gas, mining and defence use.'],
    ['title' => 'Calibrate in place', 'text' => 'Internal weight lets users keep the balance where it is installed.'],
    ['title' => 'Overload awareness', 'text' => 'Built-in capacity tracker helps protect the weighing cell.'],
    ['title' => 'Standard compliance claims', 'text' => 'Aczet lists ISO, GLP / GMP and USP chapters 41 and 1251.'],
    ['title' => 'Custom ranges on request', 'text' => 'Aczet states customised flameproof balances are available.'],
];

$faqs = [
    ['question' => 'Which model is in the CY-FC series?', 'answer' => 'Aczet lists the CY 224FC (printed CY 224F-C on the datasheet): 220 g capacity, 0.1 mg readability, with motorised internal calibration.'],
    ['question' => 'What is the difference between CY-FC and CY-F?', 'answer' => 'The CY-FC has motorised internal calibration. The CY-F models use external calibration with a test weight.'],
    ['question' => 'What hazardous-area certification does Aczet state?', 'answer' => 'Aczet states CIMFR Dhanbad certification for use in Zone 1 and 2, Gas Group IIB, as per IS/IEC 60079-0:2011. Ask us for the certificate for the model you buy.'],
    ['question' => 'What is the repeatability and linearity?', 'answer' => 'Repeatability is 0.1 mg and linearity is 0.2 mg.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'Minimum load is 10 mg. Aczet gives a typical USP minimum weight of 160 mg and 16 mg for U = 1 percent.'],
    ['question' => 'How does it connect to a PC?', 'answer' => 'It has an RS 232C interface, with USB (Windows direct communication) optional.'],
    ['question' => 'What accessories are listed?', 'answer' => 'Aczet lists an anti-vibration pad, dot-matrix data printer, test weights, density kit, 21 CFR Part 11 PC software and a qualification document.'],
];

$documents = [
    ['title' => 'CY-F / CY-FC Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG3FP1A9BWQ4T8ZH3NC6DF1.pdf'],
];

return [
    'category' => $category,
    'brand' => $brand,
    'model_group' => $group,
    'product_type' => $type,
    'specs_general' => true,
    'short_description' => $shortDescription,
    'overview' => $overview,
    'features' => $features,
    'advantages' => $advantages,
    'faqs' => $faqs,
    'documents' => $documents,

    'models' => [
        ['model' => 'CY 224FC', 'heading' => 'CY-FC Series Flameproof Balance (Internal Calibration)', 'batch' => 1, 'specs' => [
            'Model' => 'CY 224FC',
            'Readability (mg)' => '0.1',
            'Weighing capacity (g)' => '220',
            'Minimum load (mg)' => '10',
            'Repeatability, sd (mg)' => '0.1',
            'Linearity (mg)' => '± 0.2',
            'Tare range, subtractive (g)' => '-220',
            'USP minimum weight (mg)' => '160 (typical)',
            'Minimum weight, U = 1%, k = 2 (mg)' => '16 (typical)',
            'Pan size (mm)' => 'Ø 90',
            'Response time (sec.)' => '2 to 3',
            'Calibration and adjustment' => 'Motorised internal calibration',
            'Hazardous-area certification (as stated by Aczet)' => 'CIMFR Dhanbad, Zone 1 & 2, Gas Group IIB, IS/IEC 60079-0:2011; flameproof cum weatherproof enclosure with glass window',
            'Display' => 'Alphanumeric backlit LCD',
            'Units' => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)' => '15 to 35',
            'Sensitivity drift (ppm/°C)' => '± 2',
            'Interface' => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes' => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply' => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V, 50 / 60 Hz (current printed as 15 A, probably 1.5 A)',
            'Compliance' => 'ISO, GLP / GMP & USP',
            'Balance size, W × D × H (mm)' => '517 × 302 × 130',
            'Weight, approx. (kg)' => '16.3',
        ]],
    ],
];
