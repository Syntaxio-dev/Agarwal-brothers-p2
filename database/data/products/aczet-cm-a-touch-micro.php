<?php

/**
 * Aczet CM-A Series Touch Screen Micro Balance - ONE product for the whole CM-A line, specs as RANGES / per-model values.
 *
 * Sources (read 14 Nov 2026):
 *  - Aczet datasheet "Touch Screen Micro Balances CM-A SERIES" (https://aczet.com/upload/product_documentation/Touch_Screen_Micro_Balance_CM_-_A.pdf,
 *    one page, specification table read from the rendered page).
 *  - https://aczet.com/product/micro-balance (Overview tab: EMFC sensor, PSAC, USP chapters 41 / 1251, accessories list; the page itself lists only
 *    the standard CM models in its spec tables, not the CM-A ones).
 *
 * Differences from the unverified paste (owner to know):
 *  - The CM-A datasheet lists FOUR models: CM 5A (5 g), CM 6A (6.1 g), CM 11A (11 g), CM 21A (21 g). There is NO CM 31A on any Aczet page or
 *    datasheet, so it is not loaded. (CM 31 exists only in the standard CM series, see aczet-cm-standard-micro.php.)
 *  - Capacity is therefore 5 to 21 g, not 5 to 31 g.
 *  - Repeatability depends on the load range (CM 5A / 6A: 1 µg up to 2 g and 1.6 µg from 2 g to 5 g; CM 11A: 1.5 / 2.0 / 2.5 µg; CM 21A: 1.5 / 2.0 / 2.5 / 3.0 µg),
 *    so the cell says "1 to 3" and the load bands are in the overview / FAQ.
 *
 * Unconfirmed / to check with Aczet:
 *  - "Balance Size 565 x 565 x 355 mm" in the datasheet equals the PACKING dimension in the CM operating manual; it is labelled "Packing dimensions" here.
 *  - The datasheet says "Weighing Chamber 90 mm Ø" and "Pan size 15 Ø ~ 30 Ø" (the pan depends on the model); not split per model by Aczet.
 *  - 21 CFR Part 11 compliance is printed as "available on request"; it is not claimed as standard.
 *  - Interface: the datasheet table says RS 232C; its text says USB (Windows direct communication) is optional.
 *  - PSAC is printed as "Perfect-Self Auto Calibration (PSAC)" on the datasheet.
 *  - Photos: Aczet product picture (touch screen version, 0.000000 display) plus the datasheet picture; the display close-up is a crop of the
 *    same product picture (its sample screen shows "Max: 7g", a screen demo, not a CM-A model value).
 */

$category = 'Micro Balance';
$brand    = 'Aczet';
$group    = 'CM-A Series';
$type     = 'Touch Screen Micro Balance';

$shortDescription = 'The Aczet CM-A series are touch screen micro balances with 1 µg (0.001 mg) readability and capacities of 5 g, 6.1 g, 11 g and 21 g (models CM 5A, CM 6A, CM 11A and CM 21A). A 5.7-inch touch screen display, motorised internal calibration with Perfect Self Auto Calibration (PSAC), an infrared sensor for touch-free operation and a built-in real-time clock for GLP records make them practical for weighing filters, pipette checks and small samples in pharma, food, testing and research laboratories.';

$overview = '<p>The <strong>Aczet CM-A Series Touch Screen Micro Balances</strong> are made for weighing very small samples with a readability of <strong>1 µg (0.001 mg)</strong>. The line covers four models, <strong>CM 5A, CM 6A, CM 11A and CM 21A</strong>, with capacities of <strong>5 g, 6.1 g, 11 g and 21 g</strong>. All models share one 5.7-inch touch screen display, so you choose the capacity and not the operating style.</p>'
    . '<p><strong>Two-terminal design</strong></p>'
    . '<p>The balance has two parts: one housing the electronics and display, and one with the precise mechanical measuring cell and the glass draft shield. Separating them keeps the heat of the electronics away from the weighing cell and its effect on the result, and it lets you place the display where it is comfortable to read.</p>'
    . '<p><strong>Sensor and calibration</strong></p>'
    . '<ul>'
    . '<li><p>High-end Mono-Tech weigh sensor based on electromagnetic force compensation (EMFC).</p></li>'
    . '<li><p>Motorised internal calibration with <strong>Perfect Self Auto Calibration (PSAC)</strong>: when the temperature changes by a set value or a set time has passed, the balance calibrates itself.</p></li>'
    . '<li><p>Capacity tracker on the display to watch for overloads.</p></li>'
    . '</ul>'
    . '<p><strong>Operation and applications</strong></p>'
    . '<ul>'
    . '<li><p>5.7-inch touch screen display and an infrared sensor: wave your hand to operate the balance without touching it, useful for valuable or toxic samples.</p></li>'
    . '<li><p>Ten built-in applications such as percent weighing, filling, statistics, check weighing and formulation.</p></li>'
    . '<li><p>Built-in real-time clock for dated, traceable records in line with ISO, GLP and GMP practice.</p></li>'
    . '<li><p>Many weighing units, including g, mg, ct, oz, dwt, grain and momme.</p></li>'
    . '</ul>'
    . '<p><strong>Where it is used</strong></p>'
    . '<p>Typical work includes filter paper weighing, micro pipette checks, preparing small standards and quality control in the pharma and biotech industry, food and beverages, metals, plastics and electronics, pulp, paper and textile, engineering and testing laboratories.</p>'
    . '<p><strong>Accessories</strong></p>'
    . '<p>Aczet offers an anti-static ionizer (CI-01), a dot-matrix data printer (CPR 02), an anti-vibration weighing table, calibration test weights, a battery backup unit, 21 CFR Part 11 PC software and IQ, OQ and PQ qualification documents. 21 CFR Part 11 compliance is available on request.</p>'
    . '<p>Exact values differ from model to model, so the table on this page shows ranges for the whole CM-A line. Tell our team what you weigh and how much, and we will recommend the right model and share the exact figures from the Aczet datasheet.</p>';

$features = [
    ['title' => '1 µg readability', 'text' => 'Four models (CM 5A, CM 6A, CM 11A, CM 21A) read to 1 µg (0.001 mg) with capacities from 5 g up to 21 g.'],
    ['title' => '5.7-inch touch screen', 'text' => 'One touch screen display on every model, with ten built-in applications such as percent weighing, statistics and check weighing.'],
    ['title' => 'Infrared hands-free sensor', 'text' => 'Operate the balance with a wave of the hand, so valuable or toxic samples are weighed without touching the balance.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised internal calibration with Perfect Self Auto Calibration (PSAC), triggered by a temperature change or a set time interval.'],
    ['title' => 'Two-terminal design', 'text' => 'Electronics and display are separate from the mechanical weighing cell, which reduces the effect of heat on the result.'],
];

$advantages = [
    ['title' => 'One display, four capacities', 'text' => 'Pick the capacity you need, from 5 g to 21 g, and keep the same touch screen operation.'],
    ['title' => 'Fast weighing', 'text' => 'Response time is 8 to 10 seconds, so a weighing does not hold up your routine.'],
    ['title' => 'Safer for sensitive samples', 'text' => 'Touch-free sensors reduce the risk of cross-contamination and spills, and the draft shield is easy to clean.'],
    ['title' => 'Traceable records', 'text' => 'Built-in real-time clock and a printer option support dated GLP documentation.'],
    ['title' => 'Overload awareness', 'text' => 'A capacity tracker on the display shows how close you are to the maximum load of the weighing cell.'],
];

$faqs = [
    ['question' => 'Which models are in the CM-A series and what are their capacities?', 'answer' => 'The Aczet datasheet lists four models: CM 5A (5 g), CM 6A (6.1 g), CM 11A (11 g) and CM 21A (21 g). All read to 1 µg (0.001 mg).'],
    ['question' => 'What is the difference between the CM-A and the standard CM series?', 'answer' => 'The CM-A has a 5.7-inch touch screen display, while the standard CM has an alpha-numeric backlit LCD with push buttons. The standard CM series is also offered up to 31 g (CM 31).'],
    ['question' => 'What is the repeatability?', 'answer' => 'It depends on the load. For the CM 5A and CM 6A it is 1 µg up to 2 g and 1.6 µg from 2 g to 5 g. The CM 11A and CM 21A are 1.5 µg up to 0.2 g, 2.0 µg from 0.2 g to 5 g and 2.5 µg from 5 g to 11 g; the CM 21A is 3.0 µg from 11 g to 21 g.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with Perfect Self Auto Calibration (PSAC): the balance calibrates itself when the temperature changes by a set value or after a set time. Calibration with an external test weight is also possible.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The datasheet gives a minimum sample weight as per USP of 2 mg for the CM 5A and CM 6A and 2.4 mg for the CM 11A and CM 21A. The result depends on the installation location and environmental conditions.'],
    ['question' => 'Does the balance support 21 CFR Part 11?', 'answer' => 'Aczet states that 21 CFR Part 11 compliance is available on request, with PC software as an accessory. Ask our team if you need it.'],
    ['question' => 'How do I connect it to a printer or a computer?', 'answer' => 'The balance has an RS 232C interface, and a USB interface with Windows direct communication is available as an option. Aczet offers a dot-matrix data printer (CPR 02) for printing results.'],
    ['question' => 'Where should a micro balance be installed?', 'answer' => 'Indoors in a dry environment, on a stable bench, at an operating temperature of 18 °C to 30 °C and away from vibration and draughts. An anti-vibration weighing table and an anti-static ionizer are available as accessories.'],
];

$documents = [
    ['title' => 'CM-A Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC9ACZ3M5T8WQ2HNB7RVDXA.pdf'],
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
        ['model' => 'CM-A Touch Screen Micro', 'heading' => 'CM-A Series Touch Screen Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                              => 'CM 5A, CM 6A, CM 11A, CM 21A',
            'Readability (µg)'                   => '1 (0.001 mg)',
            'Weighing capacity (g)'              => '5 to 21 (CM 5A: 5, CM 6A: 6.1, CM 11A: 11, CM 21A: 21)',
            'Repeatability (±µg)'                => '1 to 3, depending on the load range',
            'Linearity (±µg)'                    => '5 to 7 (CM 5A and CM 6A: 5, CM 11A: 6, CM 21A: 7)',
            'Tare range (subtractive)'           => 'Full capacity',
            'Min. sample weight as per USP (mg)' => '2 to 2.4 (CM 5A and CM 6A: 2, CM 11A and CM 21A: 2.4)',
            'Pan size (mm)'                      => 'Ø 15 to Ø 30',
            'Response time (sec.)'               => '8 - 10',
            'Calibration and adjustment'         => 'Perfect Auto Internal Time & Temp. Controlled Smart Calibration (PSAC)',
            'Display'                            => '5.7" touch screen display',
            'Unit of measure'                    => 'g, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Sensitivity drift (ppm/°C)'         => '2 (2 × 10⁻⁶ / °C)',
            'Interface'                          => 'RS 232C (USB, Windows direct communication, optional)',
            'Operating temperature (°C)'         => '18 to 30',
            'Power supply'                       => 'A/C adaptor 230 V or 115 V ± 20 %, 50 - 60 Hz',
            'Compliance'                         => 'ISO GLP / GMP',
            'Weighing chamber (mm)'              => 'Ø 90',
            'Net weight / gross weight (kg)'     => '10.20 / 14.70',
            'Packing dimensions (mm)'            => '565 × 565 × 355',
        ]],
    ],
];
