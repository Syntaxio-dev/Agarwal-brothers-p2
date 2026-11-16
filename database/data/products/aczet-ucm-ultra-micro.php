<?php

/**
 * Aczet UCM-A Series Touch Screen Ultra-Micro Balance (model UCM 2A) - ONE product, ONE model (14 Nov 2026).
 *
 * SOURCES (all read on 14 Nov 2026; aczet.com needs a full browser User-Agent, otherwise it answers 406):
 *  - https://aczet.com/product/ultra-micro-balance   (page "Ultra Micro Balance": overview texts, "Full Specs" tables of UCM 2 and UCM 2A)
 *  - https://aczet.com/upload/product_documentation/Ultra_Micro_Balance_2017_-_UCM_2A_Series.pdf   (1-page datasheet "Ultra Micro Balance UCM-A Series")
 *  - Aczet "Micro Balance" product pages (accessories: CI-01 ionizer, CPR printer, anti-vibration table, test weights; EMFC / 21 CFR texts are NOT used here).
 *  - Reseller listings (upscales.buyweighingmachine.com) were only used as a cross-check of the 2.1 g / 0.3 ug numbers; nothing is copied from them.
 *
 * DIFFERENCES FROM THE PASTE (and between Aczet's own sources) - OWNER TO CONFIRM:
 *  1. The paste said "UCM 2A ~2.1 g x 0.1 ug". The Aczet WEBSITE table for UCM 2A says Capacity 2 g, Repeatability +-0.4 ug, Response time 10-20 sec
 *     (its UCM 2A row looks copied from the non-touch UCM 2 row). The Aczet DATASHEET of the UCM 2A says Capacity 2.1 g, Repeatability (sd) 0.3 ug,
 *     Response time 8 - 10 sec. This file uses the DATASHEET values (it is specific to the 2A, it is the PDF attached below, and resellers repeat
 *     2.1 g / 0.3 ug). If the owner prefers the website values: capacity 2, repeatability +-0.4, response 10 - 20.
 *  2. Readability 0.1 ug, minimum load 10 ug, linearity +-1.5 ug, pan size 16 mm, weighing chamber 75 Ø x 86.5 (H), 5.7" touch screen,
 *     motorised internal calibration, units, 18 to 30 C, sensitivity drift +-2 ppm/C, 230 V / 115 V adaptor, ISO GLP/GMP/USP compliance,
 *     electronic box 240 x 210 x 65 mm, mechanical box 360 x 140 x 146 mm, weight 10.20 / 14.70 kg: same on the website and the datasheet.
 *  3. Minimum sample weight (U=1%, k=2) 0.1 mg and USP 1 mg, and "Tare range: full capacity" are on the website only (the datasheet says tare -2 g).
 *  4. The website lists a second model, UCM 2 (non-touch, 5.7" display, RS 232C, 10-20 sec). It is NOT loaded: this product is the touch screen UCM 2A.
 *  5. The datasheet lists the "Dimensions for Mechanical Box" twice (360 x 140 x 146 and 565 x 565 x 355 mm); the second is probably the packed size,
 *     so it is not used. The website text "Interface: RS 232C Optional USB" is taken from the overview text (the 2A table only prints RS 232C on the UCM 2 row).
 *  6. The datasheet and website say nothing about 21 CFR Part 11 for the UCM, so it is not claimed. "MTS"-style abbreviations are not used.
 *  7. The photo's screen shows "Max: 7g" (an Aczet stock UI picture used for the whole touch-screen family); it is only illustrative.
 */

$category = 'Ultra Micro Balance';
$brand    = 'Aczet';
$group    = 'UCM Series';
$type     = 'Ultra-Micro Balance';

$shortDescription = 'The Aczet UCM 2A is a touch screen ultra-micro balance with 2.1 g capacity and 0.1 µg (0.0001 mg) readability. Its brilliant Mono-Tech weighing sensor sits in a separate mechanical unit, away from the 5.7" touch screen terminal, so the electronics do not warm the weighing cell. Perfect Self Auto Calibration (PSAC) adjusts it automatically with its built-in weight when the temperature changes or a set time has passed, and an infrared sensor lets you start weighing with a wave of the hand. Ten built-in applications and ISO / GLP / GMP compliant records make it ready for pharma, food, chemical, research and environmental laboratories.';

$overview = '<p>The <strong>Aczet UCM-A Series Ultra-Micro Balance</strong> (model <strong>UCM 2A</strong>) is made for the weighings where a few micrograms decide the result: very small, expensive, rare or toxic samples, standards, filters and low-dose preparations. It weighs up to <strong>2.1 g</strong> with a readability of <strong>0.1 µg</strong>, so the display shows seven decimals in grams.</p>'
    . '<p><strong>Two-unit design</strong></p>'
    . '<p>The balance consists of two components: one holds the electronics and the other holds the precise mechanical measuring cell. According to Aczet, this separation removes the effect of heat from the electronics on the microbalance. The weighing chamber is a glass draft shield with a 16 mm diameter pan.</p>'
    . '<p><strong>Touch screen and hands-free weighing</strong></p>'
    . '<ul>'
    . '<li><p>5.7" touch screen display with built-in applications for pieces counting, percent weighing, formulation, filling, check weighing and statistics.</p></li>'
    . '<li><p>Infrared sensor: you can operate the balance with a wave of your hand, so you can concentrate on a valuable or toxic sample without touching the balance.</p></li>'
    . '<li><p>Weighing results in many units, including g, mg, ct, GN, oz and dwt.</p></li>'
    . '</ul>'
    . '<p><strong>Stable and traceable</strong></p>'
    . '<ul>'
    . '<li><p><strong>Brilliant Mono-Tech</strong> weighing sensor built to match high international standards.</p></li>'
    . '<li><p><strong>Perfect Self Auto Calibration (PSAC):</strong> motorised internal calibration with a built-in weight, started when the ambient temperature changes by a set value or a set time has passed.</p></li>'
    . '<li><p>Built-in clock and records for ISO / GLP / GMP compliance, with a USP compliance mention in the datasheet.</p></li>'
    . '<li><p>RS 232C interface with optional USB (Windows direct communication) for data transfer; optional password protection of the menu.</p></li>'
    . '</ul>'
    . '<p><strong>Where it is used</strong></p>'
    . '<p>Aczet names the pharma, food, chemical, research and environmental industries as the typical users. Micro balances are sensitive to their surroundings, so the installation place should be chosen carefully, and optional accessories such as an anti-vibration weighing table, the CI-01 antistatic ionizer, a dot matrix data printer and test weights are available from Aczet.</p>'
    . '<p>Tell our team what you weigh and how much, and we will help you choose the right balance and share the full datasheet.</p>';

$features = [
    ['title' => '0.1 µg readability up to 2.1 g', 'text' => 'Ultra-micro resolution with seven decimals in grams, for very small, rare or toxic samples.'],
    ['title' => '5.7" touch screen terminal', 'text' => 'Operate the balance and its built-in applications on a touch screen that sits apart from the weighing unit.'],
    ['title' => 'Two-unit design', 'text' => 'The electronics and the precise mechanical measuring cell are separate components, which removes the heat of the electronics from the weighing cell.'],
    ['title' => 'Perfect Self Auto Calibration (PSAC)', 'text' => 'Motorised internal calibration with a built-in weight starts by itself after a temperature change or a set time.'],
    ['title' => 'Infrared hands-free sensor', 'text' => 'Start weighing with a wave of the hand, so you never have to touch the balance while handling a valuable or toxic sample.'],
];

$advantages = [
    ['title' => 'Very small samples, readable', 'text' => 'Weigh down to a few micrograms and still read the result to 0.1 µg.'],
    ['title' => 'Always calibrated', 'text' => 'PSAC keeps the balance adjusted when the room temperature or the time changes, without a manual routine.'],
    ['title' => 'Safer sample handling', 'text' => 'The infrared sensor means less touching of the balance while you work with toxic or expensive material.'],
    ['title' => 'Ready-made workflows', 'text' => 'Ten pre-programmed applications such as filling, statistics, check weighing, percent weighing and formulation reduce routine work.'],
    ['title' => 'Compliant records', 'text' => 'The built-in clock supports ISO / GLP / GMP documentation, and data can leave through RS 232C or the optional USB interface.'],
];

$faqs = [
    ['question' => 'What are the capacity and readability of the UCM 2A?', 'answer' => 'Aczet lists the UCM 2A with a capacity of 2.1 g in its datasheet and a readability of 0.1 µg (0.0001 mg). Minimum load is 10 µg.'],
    ['question' => 'What pan and weighing chamber does it have?', 'answer' => 'The pan size is 16 mm in diameter and the weighing chamber is 75 mm in diameter and 86.5 mm high.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with a built-in weight. Perfect Self Auto Calibration (PSAC) starts it automatically when the ambient temperature changes by a set value or after a set time. External adjustment with a test weight is also possible.'],
    ['question' => 'What does the balance measure besides plain weight?', 'answer' => 'It has ten pre-programmed applications, including pieces counting, percent weighing, formulation, filling, check weighing and statistics, plus pipette calibration and dynamic weighing.'],
    ['question' => 'How do I connect it to a printer or computer?', 'answer' => 'The balance has an RS 232C interface, with optional USB (Windows direct communication). Aczet also offers a dot matrix data printer for its balances as an accessory.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'Aczet lists a minimum sample weight of 0.1 mg (U = 1 %, k = 2) and 1 mg as per USP. Your own minimum weight depends on the installation place and the environment.'],
    ['question' => 'In which conditions does it work?', 'answer' => 'The operating temperature is 18 °C to 30 °C and the sensitivity drift is ±2 ppm per °C. Micro balances are sensitive to their surroundings, so choose the installation place carefully; an anti-vibration weighing table is available as an accessory.'],
    ['question' => 'Which industries use it?', 'answer' => 'Aczet names the pharma, food, chemical, research and environmental industries.'],
];

$documents = [
    ['title' => 'UCM-A Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXE5R7A9BWQ4T8ZH3NC6DGU1.pdf'],
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
        ['model' => 'UCM Touch Screen Ultra-Micro', 'heading' => 'UCM Series Touch Screen Ultra-Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'UCM 2A',
            'Weighing capacity (g)'          => '2.1',
            'Readability (µg)'               => '0.1',
            'Minimum load (µg)'              => '10',
            'Repeatability (sd) (µg)'        => '0.3',
            'Linearity (µg)'                 => '±1.5',
            'Tare range (subtractive)'       => 'Full capacity',
            'Minimum sample weight, U = 1 %, k = 2 (mg)' => '0.1',
            'Minimum sample weight, as per USP (mg)' => '1',
            'Pan size (mm)'                  => 'Ø 16',
            'Weighing chamber (mm)'          => 'Ø 75 × 86.5 (H)',
            'Response time (sec.)'           => '8 - 10',
            'Display'                        => '5.7" touch screen display',
            'Calibration and adjustment'     => 'Motorised internal calibration (PSAC)',
            'Units of measure'               => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Application modes'              => 'PCS, % weighing, determination, formulation, automatic statistic calculator etc.',
            'Interface'                      => 'RS 232C, optional USB (Windows direct communication)',
            'Operating temperature (°C)'     => '18 to 30',
            'Sensitivity drift (ppm/°C)'     => '±2',
            'Power supply'                   => 'A/C adaptor 230 V or 115 V ±20 %, 50 - 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP, USP',
            'Electronic box, W × D × H (mm)' => '240 × 210 × 65',
            'Mechanical box, L × W × H (mm)' => '360 × 140 × 146',
            'Weight, net / gross (kg)'       => '10.20 / 14.70',
        ]],
    ],
];
