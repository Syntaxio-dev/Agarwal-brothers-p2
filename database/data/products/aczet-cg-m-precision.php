<?php

/**
 * Aczet CG-M Series Compact Precision Balance (CG 602M, CG 3001M) - ONE product for the line (specs_general = true).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened, Chrome User-Agent):
 *  - Product page https://aczet.com/product/load-cell-precision ('Compact Balance CG-M Series' overview + Full Specs of CG 602M and CG 3001M).
 *    The CG family is NOT in the /productsolution/precision-balance menu; it sits on this page ('Load Cell Precision').
 *  - NO datasheet exists for CG-M (the only CG PDF covers CG 302 / 602 / 1202 only), so the document slot is EMPTY.
 *
 * Models: CG 602M (600 g x 0.01 g), CG 3001M (3000 g x 0.1 g) = same as the paste, both confirmed on aczet.com.
 * Doubts: the Aczet table for these two models is short (no tare range, display, interface or power rows), so only the
 *  printed values are used. The overview says 'LCD Backlight Display', '4 different weighing units', 'Tare all the range',
 *  'Square or round stainless steel weighing pan', 'Calibration mass' (supplied or optional is not stated), and 'Power: AA battery & AC-DC dual-use'
 *  (battery count and life not stated). Pan: CG 602M 128 mm (diameter symbol broken on the page, read as Ø), CG 3001M 155 x 155 mm.
 *  Which weighing units are the 4 is not stated. 'IP65' is written for the weight sensor only, not claimed for the balance. Only 2 pictures exist on
 *  aczet.com and the mapping of picture to model is by page order, not labelled. Nothing claimed about Agarwal Brothers.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CG-M Series';
$type     = 'Compact Precision Balance';

$shortDescription = 'The Aczet CG-M Series is a compact precision balance for schools, colleges and industrial use, in two models: CG 602M (600 g x 0.01 g) and CG 3001M (3,000 g x 0.1 g). It combines a precision load cell, a backlit LCD, piece counting and a stainless steel pan with battery or mains power.';

$overview = '<p>The <strong>Aczet CG-M Series Compact Balance</strong> is designed for school and college applications as well as industrial use. Aczet describes it as a balance that gives high accuracy and reliable performance in a compact, user-friendly design, with a precision aluminium load cell as the weight sensor.</p>'
    . '<p><strong>Two models</strong></p>'
    . '<ul>'
    . '<li><p>CG 602M: 600 g capacity, 0.01 g readability, 128 mm diameter pan.</p></li>'
    . '<li><p>CG 3001M: 3,000 g capacity, 0.1 g readability, 155 x 155 mm pan.</p></li>'
    . '</ul>'
    . '<p>Repeatability equals the readability on both models, and linearity is 0.02 g on the CG 602M and 0.2 g on the CG 3001M.</p>'
    . '<p><strong>Everyday features</strong></p>'
    . '<p>A backlit LCD keeps readings clear, and the tare function works over the whole range, so you can subtract the weight of a container up to the capacity. Piece counting helps with counting small parts, four different weighing units are available, and the stainless steel weighing pan is available in a square or round form.</p>'
    . '<p><strong>Power and set-up</strong></p>'
    . '<p>The balance can be powered by AA batteries or by an AC-DC supply, so it can be used on a bench or away from a socket. A calibration mass is listed with the series for adjusting the balance.</p>'
    . '<p><strong>Where it fits</strong></p>'
    . '<p>The compact body and simple controls suit teaching laboratories, school and college practicals and light industrial weighing where a full precision balance is not needed. For work that needs a draft shield, higher capacities or an RS 232C interface, the CG and CG-L series offer more options.</p>'
    . '<p>The table on this page gives ranges for the CG-M series. Tell our team your weighing range, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => 'Two models, 600 g and 3,000 g', 'text' => 'CG 602M reads 0.01 g and CG 3001M reads 0.1 g.'],
    ['title' => 'Precision load cell', 'text' => 'Aczet lists a precision aluminium load cell as the weight sensor.'],
    ['title' => 'Backlit LCD with piece counting', 'text' => 'A clear backlit display and a counting function for small parts.'],
    ['title' => 'Tare over the whole range', 'text' => 'Subtract a container up to the full capacity of the balance.'],
    ['title' => 'Battery or mains power', 'text' => 'AA battery and AC-DC dual use, plus a stainless steel pan in square or round form.'],
];

$advantages = [
    ['title' => 'Compact and easy to use', 'text' => 'A small body and simple operation suit schools, colleges and workshops.'],
    ['title' => 'Use it anywhere', 'text' => 'Dual battery and mains power means it is not tied to a socket.'],
    ['title' => 'Four weighing units', 'text' => 'Switch units to match the material you weigh.'],
    ['title' => 'Stainless steel pan', 'text' => 'A durable pan that is easy to wipe clean.'],
    ['title' => 'Same maker, wider choice', 'text' => 'Step up to the CG or CG-L series from Aczet when you need higher capacity or more features.'],
];

$faqs = [
    ['question' => 'Which models are in the CG-M series?', 'answer' => 'Aczet lists two: CG 602M (600 g, 0.01 g) and CG 3001M (3,000 g, 0.1 g).'],
    ['question' => 'What are the repeatability and linearity?', 'answer' => 'Repeatability is 0.01 g on the CG 602M and 0.1 g on the CG 3001M. Linearity is 0.02 g and 0.2 g respectively.'],
    ['question' => 'What pan size do the models have?', 'answer' => 'The CG 602M has a 128 mm diameter pan and the CG 3001M a 155 x 155 mm pan.'],
    ['question' => 'How is it powered?', 'answer' => 'Aczet lists dual power: AA batteries or an AC-DC supply. Ask our team about the battery count and supply details when you order.'],
    ['question' => 'Which weighing units are available?', 'answer' => 'Aczet lists four different weighing units, but does not name them on the page. Our team can confirm them for your model.'],
    ['question' => 'Is a calibration weight included?', 'answer' => 'Aczet lists a calibration mass with the series. Whether it is supplied with every balance should be confirmed with our team when ordering.'],
    ['question' => 'Does it have a draft shield or a data interface?', 'answer' => 'The Aczet page for the CG-M does not list a draft shield or an interface. The CG and CG-L series list an RS 232C interface.'],
    ['question' => 'Is it a good choice for a school or college lab?', 'answer' => 'Aczet describes the CG-M for school and college applications as well as industrial use, thanks to its compact size and simple operation.'],
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
        ['model' => 'CG-M Compact Precision', 'heading' => 'CG-M Series Compact Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                  => 'CG 602M, CG 3001M',
            'Weighing capacity (g)'  => '600 to 3,000 (CG 602M: 600; CG 3001M: 3,000)',
            'Readability (g)'        => '0.01 to 0.1 (CG 602M: 0.01; CG 3001M: 0.1)',
            'Repeatability (g)'      => '0.01 to 0.1 (equal to the readability of each model)',
            'Linearity (g)'          => '0.02 to 0.2 (CG 602M: 0.02; CG 3001M: 0.2)',
            'Pan size (mm)'          => 'Ø 128 (CG 602M); 155 × 155 (CG 3001M)',
            'Display'                => 'Backlit LCD',
            'Tare'                   => 'Over the whole range',
            'Weighing units'         => '4 different units',
            'Power supply'           => 'AA battery and AC-DC dual use',
        ]],
    ],
];
