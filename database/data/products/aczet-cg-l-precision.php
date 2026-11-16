<?php

/**
 * Aczet CG-L Series Precision Balance (backlit LCD) - ONE product for the whole CG-L line, 11 models (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened, Chrome User-Agent):
 *  - Product page https://aczet.com/product/load-cell-precision ('CG-L Series' overview + Full Specs of CG 203L, 303L, 302L, 602L, 2002L, 3001L, 6001L,
 *    10001L, 15001L, 30001L). The CG family is NOT in the /productsolution/precision-balance menu; it sits on this page ('Load Cell Precision').
 *  - Product page https://aczet.com/product/gold-balance ('Basic Gold Balance', jewellery menu): CG 203L, 303L, 503L, 302L, 602L, 2202L.
 *  - NO datasheet exists for CG-L: the only CG PDF (CG_series_compress1.pdf) covers CG 302 / 602 / 1202 (red LED) only. So the document slot is
 *    left EMPTY in this file (the CG datasheet must not be attached to a product it does not describe).
 *
 * Models found vs the paste: paste had CG 203L, 303L, 503L, 302L, 602L, 2002L, 3001L, 6001L, 10001L, 15001L, 30001L (11) -> all 11 exist on aczet.com.
 * Differences / doubts (owner to confirm with Aczet):
 *  - CG 503L (500 g) appears ONLY on the Basic Gold page, not on the load-cell page. It is included (Aczet source, same specs as 203L / 303L).
 *  - The 2000 g model is 'CG 2002L' on the load-cell page and 'CG 2202L' on the Basic Gold page (same 2000 g, 0.01 g). 'CG 2002L' is used (matches the paste
 *    and the series naming 302L / 602L / 2002L); 2202L may be a typo on the jewellery page.
 *  - Interface: 203L and 303L say 'RS 232C-Optional' on the load-cell page, 'RS 232C' on the Basic Gold page; all other models say 'RS 232C'. Written as
 *    'RS 232C (optional on CG 203L and CG 303L)'.
 *  - Tare range of CG 203L: 'Full Capacity' (load-cell page) vs '-200 gm' (Basic Gold page): written as the capacity of each model.
 *  - CG 203L / 303L: Aczet's table on the load-cell page prints a stray swap of 'Display' and 'Response Time' values; the sensible reading is used.
 *  - Repeatability / linearity of 0.1 g models: the page shows '0.1 gm' for some and '±0.1 gm' for others; written as ± 0.1 / ± 0.2 (same number).
 *  - Pan sizes: CG 203L / 303L / 503L 90 (Ø); CG 302L / 602L 128 (Ø); CG 2002L 138 (Ø); CG 3001L / 6001L / 10001L 175 x 155 mm; CG 15001L / 30001L 275 x 220 mm
 *    (the pages print the diameter symbol as a broken character; 'Ø' is the sensible reading).
 *  - Picture to model mapping on the page is by order, not labelled.
 * Not claimed: 'IP65' is written on the page for the weight sensor only; no IP rating of the balance, no certificates. Draft shield is stated for the
 * series ('draft shield' in the overview) but which models have it is not stated per model, so it is not listed per model. Nothing claimed about Agarwal Brothers.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CG-L Series';
$type     = 'Precision Balance';

$shortDescription = 'The Aczet CG-L Series Precision Balances cover 200 g to 30 kg in eleven models, reading 0.001 g, 0.01 g or 0.1 g. A precision load cell, a clear backlit LCD, a built-in rechargeable battery and a choice of weighing units make them a reliable general-purpose balance for education, jewellery, industrial and laboratory weighing.';

$overview = '<p>The <strong>Aczet CG-L Series Precision Balance</strong> is a reliable and versatile weighing instrument for education, jewellery and industrial applications. Aczet highlights its high-quality housing, clear LCD display, draft shield, multiple units of weight and precision load cell, which together give accurate and repeatable results.</p>'
    . '<p><strong>Eleven models in three readability groups</strong></p>'
    . '<ul>'
    . '<li><p>0.001 g: CG 203L (200 g), CG 303L (300 g) and CG 503L (500 g), with a 90 mm diameter pan.</p></li>'
    . '<li><p>0.01 g: CG 302L (300 g), CG 602L (600 g) and CG 2002L (2,000 g).</p></li>'
    . '<li><p>0.1 g: CG 3001L (3 kg), CG 6001L (6 kg), CG 10001L (10 kg), CG 15001L (15 kg) and CG 30001L (30 kg).</p></li>'
    . '</ul>'
    . '<p><strong>Weighing technology</strong></p>'
    . '<p>The series uses a precision aluminium load cell. Repeatability equals the readability on the 0.001 g, 0.01 g and 0.1 g models, with linearity of 0.002 g, 0.02 g and 0.2 g respectively. The response time is 2 to 3 seconds.</p>'
    . '<p><strong>Functions</strong></p>'
    . '<p>Tare, counting, zero tracking, percentage weighing, accumulation and check weighing are built in, and you can switch between weighing units such as g, t, ct, oz, ld and kg. A backlit alphanumeric LCD shows the reading clearly, and five colour-coded tactile keys keep operation simple.</p>'
    . '<p><strong>Built for daily use</strong></p>'
    . '<p>An in-built battery charger and battery level indication support work away from the mains, a weight stable indication tells you when to read, and anti-slip adjustable feet help levelling. An RS 232C interface is provided and, on the CG 203L and CG 303L, is listed as optional.</p>'
    . '<p>The table on this page gives ranges for the whole CG-L series. Tell our team the capacity and readability you need, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Eleven models, 200 g to 30 kg', 'text' => 'Choose from 0.001 g, 0.01 g or 0.1 g readability, from the CG 203L to the CG 30001L.'],
    ['title' => 'Precision load cell', 'text' => 'Aczet describes a precision aluminium load cell for accurate and repeatable results.'],
    ['title' => 'Clear backlit LCD', 'text' => 'An alphanumeric backlit display with five colour-coded tactile keys keeps operation simple.'],
    ['title' => 'Battery and charger built in', 'text' => 'In-built battery charger, battery level indication and weight stable indication.'],
    ['title' => 'Many functions and units', 'text' => 'Tare, counting, zero tracking, percentage, accumulation, check weighing and units g, t, ct, oz, ld and kg.'],
];

$advantages = [
    ['title' => 'One series, many needs', 'text' => 'From a 200 g balance reading 0.001 g up to a 30 kg balance reading 0.1 g.'],
    ['title' => 'Easy to read', 'text' => 'A backlit LCD stays readable in different lighting.'],
    ['title' => 'Flexible units', 'text' => 'Switch between weighing units to suit jewellery, laboratory or industrial work.'],
    ['title' => 'Quick set-up', 'text' => 'Anti-slip adjustable feet and a weight stable indication help you start weighing sooner.'],
    ['title' => 'Backed by the maker', 'text' => 'Aczet states efficient after-sales service for the CG-L Series.'],
];

$faqs = [
    ['question' => 'Which models are in the CG-L series?', 'answer' => 'The Aczet pages list CG 203L (200 g), 303L (300 g), 503L (500 g), 302L (300 g), 602L (600 g), 2002L (2,000 g), 3001L (3 kg), 6001L (6 kg), 10001L (10 kg), 15001L (15 kg) and 30001L (30 kg).'],
    ['question' => 'What readability does each group have?', 'answer' => 'The 203L, 303L and 503L read 0.001 g, the 302L, 602L and 2002L read 0.01 g, and the 3001L, 6001L, 10001L, 15001L and 30001L read 0.1 g.'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability equals the readability: 0.001 g, 0.01 g or 0.1 g. Linearity is 0.002 g, 0.02 g and 0.2 g for the three groups respectively.'],
    ['question' => 'What pan size do the models have?', 'answer' => 'CG 203L, 303L and 503L: 90 mm diameter. CG 302L and 602L: 128 mm. CG 2002L: 138 mm. CG 3001L, 6001L and 10001L: 175 x 155 mm. CG 15001L and 30001L: 275 x 220 mm.'],
    ['question' => 'Does it have a battery?', 'answer' => 'Aczet lists an in-built battery charger and battery level indication for the series. The mains supply is AC 230 V, plus or minus 10 percent, 50 to 60 Hz.'],
    ['question' => 'Can I connect it to a computer?', 'answer' => 'An RS 232C interface is listed. For the CG 203L and CG 303L, Aczet lists it as optional, so confirm it with our team when ordering.'],
    ['question' => 'How does the CG-L differ from the CG series?', 'answer' => 'The CG Series (CG 302, 602 and 1202) has a red LED display and a stainless steel pan of 118 mm. The CG-L Series has a backlit LCD and more capacities, including 0.001 g and 0.1 g models.'],
    ['question' => 'Is there a datasheet?', 'answer' => 'Aczet has not published a separate datasheet for the CG-L models. Our team can share the details Aczet provides for the model you choose.'],
];

$documents = [];

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
        ['model' => 'CG-L Precision', 'heading' => 'CG-L Series Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                       => 'CG 203L, CG 303L, CG 503L, CG 302L, CG 602L, CG 2002L, CG 3001L, CG 6001L, CG 10001L, CG 15001L, CG 30001L',
            'Weighing capacity (g)'       => '200 to 30,000 (CG 203L: 200; CG 303L: 300; CG 503L: 500; CG 302L: 300; CG 602L: 600; CG 2002L: 2,000; CG 3001L: 3,000; CG 6001L: 6,000; CG 10001L: 10,000; CG 15001L: 15,000; CG 30001L: 30,000)',
            'Readability (g)'             => '0.001 to 0.1 (CG 203L, CG 303L, CG 503L: 0.001; CG 302L, CG 602L, CG 2002L: 0.01; CG 3001L to CG 30001L: 0.1)',
            'Repeatability (g)'           => '0.001 to ± 0.1 (equal to the readability of each model)',
            'Linearity (g)'               => '0.002 to ± 0.2 (CG 203L, CG 303L, CG 503L: 0.002; CG 302L, CG 602L, CG 2002L: 0.02; CG 3001L to CG 30001L: 0.2)',
            'Tare range, subtractive (g)' => 'Up to the capacity of each model',
            'Pan size (mm)'               => 'Ø 90 (CG 203L, 303L, 503L); Ø 128 (CG 302L, 602L); Ø 138 (CG 2002L); 175 × 155 (CG 3001L, 6001L, 10001L); 275 × 220 (CG 15001L, 30001L)',
            'Display'                     => 'Alphanumeric backlit LCD',
            'Response time (sec.)'        => '2 to 3',
            'Interface'                   => 'RS 232C (optional on CG 203L and CG 303L)',
            'Application'                 => 'Counting and unit conversion',
            'Weighing units'              => 'g, t, ct, oz, ld, kg',
            'Power supply'                => 'AC 230 V ± 10%, 50 to 60 Hz; in-built battery charger',
        ]],
    ],
];
