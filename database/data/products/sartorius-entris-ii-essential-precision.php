<?php

/**
 * Sartorius Entris II Essential (BCE) Precision - ONE product for the BCE 1 mg, 10 mg, 100 mg and 1 g models.
 *
 * Values: Sartorius Entris II brochure technical table (French, api.sartorius.com document 71467, pages 6-7) and the English Essential brochure
 * (document 65105) for the features. Models with i have internal adjustment (isoCAL), models without i use external adjustment.
 * The owner's pasted list matched (subset): 1 mg BCE223, 323, 423, 623, 653 (i and non-i); 10 mg BCE622, 822, 1202, 2202, 3202, 4202, 6202
 * (i and non-i); 100 mg BCE2201, 5201, 8201 (i and non-i); 1 g BCE6200 and BCE8200 (i and non-i). BCE653 has a lower weighing chamber (50 mm) and
 * measures 219 x 317 x 145 mm.
 */

$category = 'Precision Lab Balances';
$brand    = 'Sartorius';
$group    = 'Entris II Essential (BCE) Series';
$type     = 'Precision Balance';

$shortDescription = 'The Sartorius Entris II Essential (BCE) precision balances weigh from 220 g up to 8,200 g with 1 mg, 10 mg, 100 mg or 1 g readability and a typical stabilization time of 1.0 second or less. Offering best value for basic weighing, they combine isoCAL internal adjustment (on "i" models), a hybrid LED and touch display and 12 built-in applications in a budget-friendly balance.';

$overview = '<p>The <strong>Sartorius Entris II Essential (BCE) Precision Balances</strong> offer unrivalled value for basic weighing tasks: the only balance in this class featuring isoCAL, LED touch technology and 12 built-in applications, backed by almost 150 years of German engineering expertise. Models with internal adjustment are marked <strong>i</strong> (for example BCE623i-1S); the others are adjusted with an external test weight.</p>'
    . '<p><strong>1 mg models with draft shield</strong> (Ø 120 mm pan)</p>'
    . '<ul>'
    . '<li><p>BCE223 (220 g), BCE323 (320 g), BCE423 (420 g), BCE623 (620 g) and BCE653 (650 g), each with internal (i) and external adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>10 mg models with open pan</strong> (182 × 182 mm pan)</p>'
    . '<ul>'
    . '<li><p>BCE622 (620 g), BCE822 (820 g), BCE1202 (1,200 g), BCE2202 (2,200 g), BCE3202 (3,200 g), BCE4202 (4,200 g) and BCE6202 (6,200 g), each with internal (i) and external adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>100 mg and 1 g models with open pan</strong></p>'
    . '<ul>'
    . '<li><p>100 mg: BCE2201 (2,200 g), BCE5201 (5,200 g) and BCE8201 (8,200 g). 1 g: BCE6200 (6,200 g) and BCE8200 (8,200 g). Each with internal (i) and external adjustment.</p></li>'
    . '</ul>'
    . '<p><strong>Quality you can trust</strong></p>'
    . '<ul>'
    . '<li><p>A monolithic weigh cell invented by Sartorius delivers accurate results year in and year out.</p></li>'
    . '<li><p>The fastest stabilization time in its class, overload protection and the "@start" self-test make the balance reliable.</p></li>'
    . '<li><p>Parts made of hard-wearing PBT, stainless steel and glass give high chemical resistance, and a wipe-clean design with easy-to-remove parts prevents cross-contamination.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to use</strong></p>'
    . '<p>A hybrid screen combines intuitive, wear-resistant LED and touch technology in a clearly structured user interface. Twelve built-in applications with GLP/GMP compliant printout and data output cover weighing, dosing, counting, percentage weighing, mixing, components, animal weighing, calculation, density determination, statistics, peak hold, checkweighing and mass unit conversion, plus an underfloor weighing feature for bigger samples. Eight built-in languages and password protection that guards the balance against unintentional changes are included.</p>'
    . '<p><strong>Plug-and-play connectivity</strong></p>'
    . '<p>The balance detects Sartorius accessories such as a printer or a second display automatically. A future-proof USB Type-C interface and an RS232 9-pin interface (for backwards compatibility) are provided, and the PC-Direct feature sends weighing data directly into spreadsheets or documents. Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => 'isoCAL internal adjustment (i models)', 'text' => 'Fully automated, temperature- and time-controlled calibration and adjustment, unique in this weighing class.'],
    ['title' => 'Hybrid LED and touch screen', 'text' => 'Intuitive, wear-resistant LED and touch technology in a clearly structured user interface.'],
    ['title' => '12 built-in applications', 'text' => 'Applications such as dosing, counting, percentage weighing and density determination, with GLP/GMP compliant printout and data output.'],
    ['title' => 'Monolithic weigh cell', 'text' => 'Accurate results year in and year out, with the fastest stabilization time in its class.'],
    ['title' => 'Plug-and-play connectivity', 'text' => 'USB Type-C and RS232 interfaces, automatic detection of Sartorius accessories and PC-Direct for spreadsheets.'],
];

$advantages = [
    ['title' => 'Best value in its class', 'text' => 'Premium German engineering for basic weighing at a budget price.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.0 second or less on the 1 mg models and 0.9 seconds or less on the others.'],
    ['title' => 'Easy to clean', 'text' => 'Chemical-resistant PBT, stainless steel and glass parts and a wipe-clean design prevent cross-contamination.'],
    ['title' => 'Protected settings', 'text' => 'Password protection guards the balance against unintentional changes.'],
    ['title' => 'Wide model range', 'text' => 'More than 30 precision models from 220 g to 8,200 g, from 1 mg to 1 g readability.'],
];

$faqs = [
    ['question' => 'What is the difference between the "i" and the non-"i" models?', 'answer' => 'Models with "i" in the code (for example BCE6202i-1S) have internal isoCAL calibration and adjustment. Models without it (for example BCE6202-1S) are adjusted with an external test weight.'],
    ['question' => 'Which capacities and readability are available?', 'answer' => '1 mg models from 220 g to 650 g, 10 mg models from 620 g to 6,200 g, 100 mg models from 2,200 g to 8,200 g and 1 g models of 6,200 g and 8,200 g.'],
    ['question' => 'Which models have a draft shield?', 'answer' => 'The 1 mg models have a draft shield, a Ø 120 mm pan and a weighing chamber 240 mm high (the BCE653 has a lower chamber of 50 mm). The other models have an open 182 × 182 mm pan.'],
    ['question' => 'What is the difference between Entris II Essential (BCE) and Advanced (BCA)?', 'answer' => 'The Essential line offers best value for basic weighing with a hybrid LED and touch screen and 12 built-in applications. The Advanced line adds real-time level support, integrated protection systems, CalAuditTrail and a graphic touch display.'],
    ['question' => 'Which interfaces does it have?', 'answer' => 'A USB Type-C interface and a 9-pin RS232 interface. The PC-Direct feature sends results to a computer without extra software.'],
    ['question' => 'Where should a precision balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and electromagnetic fields.'],
];

$documents = [
    ['title' => 'Entris II Essential Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC8N6V7EZR8X2ZN1TF6FGED.pdf'],
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
        ['model' => 'Entris II Essential Precision', 'heading' => 'Entris II Essential (BCE) Precision Balance', 'batch' => 1, 'specs' => [
            'Model'                          => '1 mg: BCE223, BCE323, BCE423, BCE623, BCE653 / 10 mg: BCE622, BCE822, BCE1202, BCE2202, BCE3202, BCE4202, BCE6202 / 100 mg: BCE2201, BCE5201, BCE8201 / 1 g: BCE6200, BCE8200 (i and non-i, all -1S)',
            'Readability (mg)'               => '1 / 10 / 100 / 1,000',
            'Weighing capacity (g)'          => '220 to 8,200',
            'Pan size (mm)'                  => 'Ø 120 (1 mg models) / 182 × 182 (other models)',
            'Repeatability, typical (mg)'    => '1 (1 mg) / 10 (10 mg) / 100 (100 mg) / 1,000 (1 g)',
            'Stabilization time, typical (sec.)' => '≤ 1.0 (1 mg models) / ≤ 0.9 (other models)',
            'Weighing chamber height (mm)'   => '240 (1 mg models; 50 for BCE653) / open pan (other models)',
            'Dimensions, W × D × H (mm)'     => '219 × 317 × 345 (1 mg models; 219 × 317 × 145 for BCE653) / 219 × 317 × 94 (other models)',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL) on "i" models / External on other models',
            'Display'                        => 'Hybrid LED and touch',
            'Interfaces'                     => 'USB Type-C, RS232 (9-pin)',
        ]],
    ],
];
