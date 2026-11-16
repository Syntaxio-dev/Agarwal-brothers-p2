<?php

/**
 * Aczet CZ Series Milligram Precision Balance (0.001 g, external calibration) - ONE product for the four 0.001 g models, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet product page https://aczet.com/product/-2 ("Single Block (PCGM) Precision Balance", 'Full Specs' tables of every CZ model)
 *  - Aczet datasheet "Technical Data, Precision Balances, CZ Series" https://aczet.com/upload/product_documentation/PCGM_external_Datasheet.pdf (1 page, rendered and read)
 *    <- attached as the document. Spec values: datasheet; every value matches the product page table.
 *
 * Models in this product: CZ 223 (220 g), CZ 323 (320 g), CZ 513 (510 g), CZ 723 (720 g); all 0.001 g readability, 0.002 g repeatability and linearity.
 *
 * Differences / doubts (owner to confirm):
 *  - The page and datasheet list eight external-calibration CZ models. Left out of this product: CZ 64 and CZ 124 (0.1 mg, not milligram class) and CZ 1202 and
 *    CZ 2202 (0.01 g, other body, 135 x 165 pan). The page also lists internal-calibration "C" versions (CZ 64C ... CZ 723C); they are not in the datasheet
 *    attached here and are NOT included (their rows were not compared value for value).
 *  - The datasheet is the whole CZ line (it also shows the 0.1 mg and 0.01 g models); that is why the product text names the other CZ models only in the FAQ.
 *  - The pasted CY-C / CY milligram names do not exist for 0.001 g on aczet.com; 0.001 g models are CZ only.
 *  - 'Weighing chamber 150' has no unit symbol beyond 'mm' in the datasheet heading; loaded as mm. 'Net weight approx 2.7 Kg' (page also says Net / Gross).
 *  - Operating temperature prints '15? to 35?C' (degree sign garbled); read as 15 to 35 C. Pan 110 followed by a diameter sign (110 Ø).
 *  - Compliance 'ISO, GLP / GMP' is the datasheet's wording; no certificate, IP rating or USP claim is made for the CZ.
 *  - The datasheet text says 'Battery Operated rechargeable' and 'Interface RS 232C Optional USB'; the table says 'RS 232C / USB (Optional)'. The table is used.
 *  - Datasheet: 'Internal Calibration' and 'External Calibration' are both described in the page's feature list; the 0.001 g CZ models are External Calibration
 *    (external adjusting weight required, not supplied: not stated whether it is included).
 * Not confirmed: order codes, weight of the pan material, whether USB is a fitted or a paid option beyond 'Optional'. Nothing is claimed about Agarwal Brothers.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CZ Series';
$type     = 'Milligram Precision Balance';

$shortDescription = 'The Aczet CZ Series milligram precision balances read to 0.001 g (1 mg) and cover four capacities: CZ 223 (220 g), CZ 323 (320 g), CZ 513 (510 g) and CZ 723 (720 g). A single-block weigh sensor, a bright backlit LCD with a capacity tracker, a 110 mm pan under a glass draft shield and external calibration make them practical balances for everyday laboratory, quality-control and educational weighing.';

$overview = '<p>The <strong>Aczet CZ Series Milligram Precision Balance</strong> range is made for routine weighing where a reading to 0.001 g (1 mg) is needed. Four models share the same compact body: the CZ 223 (220 g), CZ 323 (320 g), CZ 513 (510 g) and CZ 723 (720 g). Aczet builds them around its single-block (PCGM) weigh sensor, which it describes as high-end technology that matches international standards.</p>'
    . '<p><strong>Four models, one body</strong></p>'
    . '<ul>'
    . '<li><p>Capacities of 220 g, 320 g, 510 g and 720 g, all reading 0.001 g.</p></li>'
    . '<li><p>Repeatability (sd) and linearity of 0.002 g on every model.</p></li>'
    . '<li><p>A 110 mm diameter pan, a response time of 2 to 3 seconds and a subtractive tare range equal to each capacity.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to read, easy to protect</strong></p>'
    . '<p>A high-contrast backlit LCD with 15 mm digits stays readable under any room lighting, and a capacity tracker on the display helps you watch for overload. A glass draft shield around the pan keeps air movement from disturbing the reading, and adjustable feet let you level the balance on the bench.</p>'
    . '<p><strong>Built-in applications</strong></p>'
    . '<p>Piece counting, percent weighing and animal (dynamic) weighing are available, with units that include mg, g, kg, lb, ct, GN, mo, oz, dwt and more. A statistics function reports the number of readings, minimum, maximum, standard deviation, difference and coefficient of variation of stored values.</p>'
    . '<p><strong>Calibration, data and records</strong></p>'
    . '<p>These models use external calibration with an external adjusting weight. A built-in clock supports GLP / GMP documentation, an RS 232C interface connects the balance to a printer or PC and a USB interface is optional. The balance is also equipped with a lug for an optional anti-theft cable and lock and can be run on a rechargeable battery.</p>'
    . '<p>The table on this page shows ranges for the whole CZ 0.001 g line. Tell our team the capacity you need and we will confirm the right model and the exact figures for it.</p>';

$features = [
    ['title' => 'Four capacities, 0.001 g readability', 'text' => 'CZ 223, 323, 513 and 723 weigh 220 g to 720 g and all read to 0.001 g with 0.002 g repeatability.'],
    ['title' => 'Single-block weigh sensor', 'text' => 'Aczet builds the CZ Series on its single-block (PCGM) weigh sensor technology.'],
    ['title' => 'Bright backlit LCD', 'text' => '15 mm digits are easy to read in any lighting, with a capacity tracker to watch for overload.'],
    ['title' => 'Draft shield and 110 mm pan', 'text' => 'A glass draft shield protects the reading from air movement above a roomy 110 mm pan.'],
    ['title' => 'Counting, percent, statistics', 'text' => 'Piece counting, percent and animal weighing, many units and a statistics function are built in.'],
];

$advantages = [
    ['title' => 'Milligram readability at a practical price class', 'text' => 'Reading to 0.001 g suits most routine laboratory, quality-control and teaching weighing.'],
    ['title' => 'Choose the capacity you need', 'text' => 'Four capacities up to 720 g on one body keep the readability the same.'],
    ['title' => 'Overload awareness', 'text' => 'The capacity tracker helps you avoid overloading the weigh cell.'],
    ['title' => 'Records for GLP / GMP work', 'text' => 'A built-in clock and an RS 232C interface (USB optional) support documented weighing.'],
    ['title' => 'Portable and secure', 'text' => 'A rechargeable battery option and a lug for an optional anti-theft cable help in shared laboratories.'],
];

$faqs = [
    ['question' => 'Which models are in this CZ milligram range?', 'answer' => 'The Aczet datasheet lists four models with 0.001 g readability: CZ 223 (220 g), CZ 323 (320 g), CZ 513 (510 g) and CZ 723 (720 g).'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability (sd) is 0.002 g and linearity is plus or minus 0.002 g on all four models.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'These models use external calibration: the balance is adjusted with an external adjusting weight. Ask our team which test weight suits your capacity.'],
    ['question' => 'What is the pan size and how fast does it settle?', 'answer' => 'The pan is 110 mm in diameter and the response time is 2 to 3 seconds.'],
    ['question' => 'Can I connect it to a printer or PC?', 'answer' => 'Yes. An RS 232C interface is provided and a USB interface is optional. Aczet describes direct communication with Windows.'],
    ['question' => 'What other CZ models does Aczet make?', 'answer' => 'The same datasheet also lists the CZ 64 (60 g) and CZ 124 (120 g) with 0.1 mg readability and the CZ 1202 (1,200 g) and CZ 2202 (2,200 g) with 0.01 g readability. Ask our team if you need one of these.'],
    ['question' => 'What weighing applications are built in?', 'answer' => 'Piece counting, percent weighing and animal (dynamic) weighing, plus statistics, a choice of weighing units and a below-balance weighing hook function described on the Aczet page.'],
    ['question' => 'What are the power supply and operating temperature?', 'answer' => 'The balance runs on an AC adapter for 100 to 240 V, 50 to 60 Hz (plus or minus 20 percent) and operates from 15 to 35 degrees C. Aczet also describes a rechargeable battery option.'],
];

$documents = [
    ['title' => 'CZ Series Precision Balance Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DZ1.pdf'],
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
        ['model' => 'CZ Series Milligram Precision Balance', 'heading' => 'CZ Series Milligram Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CZ 223, CZ 323, CZ 513, CZ 723',
            'Weighing capacity (g)'          => '220 to 720 (CZ 223: 220; CZ 323: 320; CZ 513: 510; CZ 723: 720)',
            'Readability (g)'                => '0.001',
            'Repeatability, sd (g)'          => '0.002',
            'Linearity (g)'                  => '± 0.002',
            'Tare range, subtractive (g)'    => 'Up to -720 (equal to the capacity of each model)',
            'Pan size (mm)'                  => 'Ø 110',
            'Response time (sec.)'           => '2 to 3',
            'Display'                        => 'Bright LCD display with LED backlight',
            'Calibration and adjustment'     => 'External calibration',
            'Units'                          => 'mg, gm, kg, lb, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)'     => '15 to 35',
            'Interface'                      => 'RS 232C, USB optional',
            'Application modes'              => 'Piece counting, percent, animal weighing',
            'Power supply'                   => 'AC adapter 100 to 240 V ± 20%, 50 / 60 Hz',
            'Compliance'                     => 'ISO, GLP / GMP',
            'Balance size, W × D × H (mm)'   => '185 × 245 × 250',
            'Weighing chamber (mm)'          => '150',
            'Net weight, approx. (kg)'       => '2.7',
        ]],
    ],
];
