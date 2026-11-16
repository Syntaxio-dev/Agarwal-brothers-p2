<?php

/**
 * Aczet CG Series Precision Balance (red LED, CG 302 / CG 602 / CG 1202) - ONE product for the line, specs as ranges / per-model values (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened, Chrome User-Agent):
 *  - Product page https://aczet.com/product/load-cell-precision ('CG Series' overview + Full Specs tables). NOTE: the CG family is NOT in the
 *    /productsolution/precision-balance menu; it sits on the page titled 'Load Cell Precision' (jewellery / laboratory weighing menus) and,
 *    for part of the range, on https://aczet.com/product/gold-balance ('Basic Gold Balance').
 *  - Datasheet https://aczet.com/upload/product_documentation/CG_series_compress1.pdf (1 page, image only, read from the rendered page).
 *    It covers ONLY CG 302, CG 602 and CG 1202 (so this product has the datasheet; CG-L and CG-M have none).
 *
 * Models: CG 302, CG 602, CG 1202 = same three as the paste. All confirmed by the datasheet.
 *
 * Differences between sources (the datasheet is used):
 *  - Pan size: datasheet and load-cell page say 118 Ø for all three; the Basic Gold page says 145 x 125 mm for CG 1202. Datasheet used (118 Ø).
 *  - Datasheet lists 'Repeatability (sd)' and 'Linearity (+/-)' 0.01 / 0.02 gm for all three; the pages print the same without the sign.
 * Not claimed: the load-cell page text says the weight sensor is a 'Precision Aluminum Load Cell - IP65' (sensor wording only), so no IP rating of the
 * balance is claimed; no certificates / approvals are stated by Aczet for this series. Nothing is claimed about Agarwal Brothers.
 * Not verified: battery life, rechargeable battery details (the datasheet only says 'built in rechargeable battery').
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CG Series';
$type     = 'Precision Balance';

$shortDescription = 'The Aczet CG Series Precision Balances read to 0.01 g and cover 300 g, 600 g and 1200 g: CG 302, CG 602 and CG 1202. A high-resolution load cell, a bright red LED display, a stainless steel pan, a built-in rechargeable battery and a foldable draft shield make them a practical, compact choice for jewellery, laboratory, industrial and education weighing.';

$overview = '<p>The <strong>Aczet CG Series Precision Balance</strong> is a portable, reliable balance for everyday weighing to 0.01 g. Aczet describes it as suited to jewellery, laboratory, industrial and education use, with simple five-button operation and a bright red LED display that is easy to read.</p>'
    . '<p><strong>Three models, one body</strong></p>'
    . '<ul>'
    . '<li><p>CG 302: 300 g capacity.</p></li>'
    . '<li><p>CG 602: 600 g capacity.</p></li>'
    . '<li><p>CG 1202: 1200 g capacity.</p></li>'
    . '</ul>'
    . '<p>All three read 0.01 g, with 0.01 g repeatability, 0.02 g linearity, a stainless steel pan of 118 mm diameter and a response time of 2 to 3 seconds.</p>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>The balance uses a high precision, single-point, environmentally protected load cell with a resolution of up to 2,00,000 counts. The datasheet also lists a safety overload of 150 percent of the scale capacity, external calibration and auto power off.</p>'
    . '<p><strong>Built for daily use</strong></p>'
    . '<p>The body is a rugged, compact ABS housing with adjustable anti-slip feet and a bubble level for quick levelling. A built-in rechargeable battery with a battery level indication lets you work away from a socket, and an in-built charger recharges it from the mains. A foldable draft shield is part of the interior design.</p>'
    . '<p><strong>Functions and connections</strong></p>'
    . '<p>Tare, counting, zero tracking, percentage weighing, accumulation and check weighing are built in, and you can switch between weighing units such as g, t, ct, oz, ld and kg. A colour-coded keyboard with five tactile keys keeps operation simple, and an RS 232C interface is provided.</p>'
    . '<p>The table on this page shows the values for the CG series. Tell our team the capacity you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Three capacities, 0.01 g readability', 'text' => 'CG 302, CG 602 and CG 1202 cover 300 g to 1200 g, all reading to 0.01 g.'],
    ['title' => 'High resolution load cell', 'text' => 'A single-point, environmentally protected load cell gives resolution of up to 2,00,000 counts.'],
    ['title' => 'Bright red LED display', 'text' => 'A high-bright red LED display and five colour-coded tactile keys keep readings and operation simple.'],
    ['title' => 'Built-in rechargeable battery', 'text' => 'Work away from the mains, with a battery level indication and an in-built charger.'],
    ['title' => 'Everyday weighing functions', 'text' => 'Tare, counting, zero tracking, percentage, accumulation, check weighing and several weighing units.'],
];

$advantages = [
    ['title' => 'Right capacity for the job', 'text' => 'Pick 300 g, 600 g or 1200 g and keep the same 0.01 g readability.'],
    ['title' => 'Compact and rugged', 'text' => 'A compact ABS housing with a stainless steel pan saves bench space and withstands daily use.'],
    ['title' => 'Overload protection', 'text' => 'The datasheet states a safety overload of 150 percent of the scale capacity.'],
    ['title' => 'Quick set-up', 'text' => 'Anti-slip adjustable feet and a bubble level make levelling quick.'],
    ['title' => 'Practical for many uses', 'text' => 'Aczet positions the series for jewellery, laboratory, industrial and education weighing.'],
];

$faqs = [
    ['question' => 'Which models are in the CG series with a red LED display?', 'answer' => 'The Aczet datasheet lists three models: CG 302 (300 g), CG 602 (600 g) and CG 1202 (1200 g). All read 0.01 g.'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability is 0.01 g and linearity is 0.02 g on all three models, according to the Aczet datasheet.'],
    ['question' => 'What is the pan size?', 'answer' => 'The datasheet gives a 118 mm diameter stainless steel pan for all three models.'],
    ['question' => 'Does it run on a battery?', 'answer' => 'Yes. The balance has a built-in rechargeable battery with a battery level indication and an in-built charger. The mains supply is AC 230 V, plus or minus 10 percent, 50 to 60 Hz.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'The datasheet lists external calibration, so you use a calibration weight of the right value. For regular checks, many customers also keep E2 or F1 class test weights.'],
    ['question' => 'Can I connect it to a computer or printer?', 'answer' => 'An RS 232C interface is provided. Aczet also lists a CPR 02 data printer among its accessories for its balances.'],
    ['question' => 'How is it different from the CG-L and CG-M series?', 'answer' => 'The CG-L Series has a backlit LCD with a draft shield on some models and a wider range of capacities, and the CG-M is a compact balance for schools and colleges. The CG Series described here has a red LED display.'],
    ['question' => 'How do I choose the right capacity?', 'answer' => 'Choose a capacity above the heaviest item you weigh, and a readability that suits the smallest difference you need to see. Tell our team your application and we will suggest the model.'],
];

$documents = [
    ['title' => 'CG Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DG2.pdf'],
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
        ['model' => 'CG Precision', 'heading' => 'CG Series Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                       => 'CG 302, CG 602, CG 1202',
            'Weighing capacity (g)'       => '300 to 1,200 (CG 302: 300; CG 602: 600; CG 1202: 1,200)',
            'Readability (g)'             => '0.01',
            'Repeatability, sd (g)'       => '0.01',
            'Linearity (g)'               => '± 0.02',
            'Tare range, subtractive (g)' => 'Up to -1,200 (equal to the capacity of each model)',
            'Pan size (mm)'               => 'Ø 118',
            'Display'                     => 'Red LED',
            'Response time (sec.)'        => '2 to 3',
            'Interface'                   => 'RS 232C',
            'Application'                 => 'Counting and unit conversion',
            'Calibration and adjustment'  => 'External calibration',
            'Power supply'                => 'AC 230 V ± 10%, 50 to 60 Hz; built-in rechargeable battery',
        ]],
    ],
];
