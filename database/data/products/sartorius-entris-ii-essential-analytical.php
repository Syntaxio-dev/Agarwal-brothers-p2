<?php

/**
 * Sartorius Entris II Essential (BCE) Analytical - ONE product for the six BCE analytical models.
 *
 * Values: Sartorius "Entris II Laboratory Balances" brochure (English, Essential line, api.sartorius.com document 65105, page 4 technical
 * specifications and feature pages) and the Entris II brochure technical table (BCE64i, BCE64, BCE124i, BCE124, BCE224i, BCE224).
 * The owner's pasted list matched: there is no BCE324 model.
 */

$category = 'Analytical Lab Balances';
$brand    = 'Sartorius';
$group    = 'Entris II Essential (BCE) Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Sartorius Entris II Essential (BCE) analytical balances weigh 60 g, 120 g or 220 g with 0.1 mg readability and a typical stabilization time of 1.5 seconds or less. Offering best value for basic weighing, they combine isoCAL internal adjustment (on "i" models), a hybrid LED and touch display and 12 built-in applications in a budget-friendly balance.';

$overview = '<p>The <strong>Sartorius Entris II Essential (BCE) Analytical Balances</strong> offer unrivalled value for basic weighing tasks at <strong>0.1 mg</strong>: the only balance in this class featuring isoCAL, LED touch technology and 12 built-in applications, backed by almost 150 years of German engineering expertise. Six models cover the range:</p>'
    . '<ul>'
    . '<li><p><strong>Internal adjustment (isoCAL):</strong> BCE224i-1S (220 g), BCE124i-1S (120 g) and BCE64i-1S (60 g).</p></li>'
    . '<li><p><strong>External adjustment:</strong> BCE224-1S (220 g), BCE124-1S (120 g) and BCE64-1S (60 g).</p></li>'
    . '</ul>'
    . '<p><strong>Quality you can trust</strong></p>'
    . '<ul>'
    . '<li><p>A monolithic weigh cell invented by Sartorius delivers accurate results year in and year out.</p></li>'
    . '<li><p>The fastest stabilization time in its class, overload protection and the "@start" self-test make the balance reliable.</p></li>'
    . '<li><p>Parts made of hard-wearing PBT, stainless steel and glass give high chemical resistance, and a wipe-clean design with easy-to-remove parts prevents cross-contamination.</p></li>'
    . '<li><p>Specially coated glass draft shield parts minimize errors caused by electrostatically charged samples, and the draft shield can be removed completely.</p></li>'
    . '</ul>'
    . '<p><strong>Easy to use</strong></p>'
    . '<p>A hybrid screen combines intuitive, wear-resistant LED and touch technology in a clearly structured user interface. Twelve built-in applications with GLP/GMP compliant printout and data output cover weighing, dosing, counting, percentage weighing, mixing, components, animal weighing, calculation, density determination, statistics, peak hold, checkweighing and mass unit conversion, plus an underfloor weighing feature for bigger samples. Eight built-in languages (English, German, French, Italian, Spanish, Portuguese, Russian and Polish) and password protection that guards the balance against unintentional changes are included.</p>'
    . '<p><strong>Plug-and-play connectivity</strong></p>'
    . '<p>The balance detects Sartorius accessories such as a printer or a second display automatically. A future-proof USB Type-C interface and an RS232 9-pin interface (for backwards compatibility) are provided, and the PC-Direct feature sends weighing data directly into spreadsheets or documents.</p>'
    . '<p>All models have a Ø 90 mm weighing pan and a weighing chamber 240 mm high, and measure 219 × 317 × 345 mm (W × D × H). Our team can help you choose the right model and accessories.</p>';

$features = [
    ['title' => 'isoCAL internal adjustment (i models)', 'text' => 'Fully automated, temperature- and time-controlled calibration and adjustment, unique in this weighing class.'],
    ['title' => 'Hybrid LED and touch screen', 'text' => 'Intuitive, wear-resistant LED and touch technology in a clearly structured user interface.'],
    ['title' => '12 built-in applications', 'text' => 'Applications such as dosing, counting, percentage weighing and density determination, with GLP/GMP compliant printout and data output.'],
    ['title' => 'Monolithic weigh cell', 'text' => 'Accurate results year in and year out, with the fastest stabilization time in its class.'],
    ['title' => 'Plug-and-play connectivity', 'text' => 'USB Type-C and RS232 interfaces, automatic detection of Sartorius accessories and PC-Direct for spreadsheets.'],
];

$advantages = [
    ['title' => 'Best value in its class', 'text' => 'Premium German engineering for basic weighing at a budget price.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time is 1.5 seconds or less for every model.'],
    ['title' => 'Easy to clean', 'text' => 'Chemical-resistant PBT, stainless steel and glass parts and a wipe-clean design prevent cross-contamination.'],
    ['title' => 'Protected settings', 'text' => 'Password protection guards the balance against unintentional changes.'],
    ['title' => 'Reliable operation', 'text' => 'Overload protection and the "@start" self-test guarantee dependable performance.'],
];

$faqs = [
    ['question' => 'What is the difference between the "i" and the non-"i" models?', 'answer' => 'Models with "i" in the code (for example BCE224i-1S) have internal isoCAL calibration and adjustment. Models without it (for example BCE224-1S) are adjusted with an external test weight.'],
    ['question' => 'Which capacities are available?', 'answer' => 'Analytical models of 60 g, 120 g and 220 g, each with internal or external adjustment and 0.1 mg readability.'],
    ['question' => 'What is the difference between Entris II Essential (BCE) and Advanced (BCA)?', 'answer' => 'The Essential line offers best value for basic weighing with a hybrid LED and touch screen and 12 built-in applications. The Advanced line adds real-time level support, integrated protection systems, CalAuditTrail and a graphic touch display.'],
    ['question' => 'Which interfaces does it have?', 'answer' => 'A USB Type-C interface and a 9-pin RS232 interface. The PC-Direct feature sends results to a computer without extra software.'],
    ['question' => 'How do I keep the weighing chamber clean?', 'answer' => 'The draft shield can be removed completely, and the wipe-clean design with easy-to-remove parts prevents cross-contamination.'],
    ['question' => 'Where should an analytical balance be installed?', 'answer' => 'On a stable bench away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations and electromagnetic fields.'],
];

$documents = [
    ['title' => 'Entris II Essential Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01KXC7J2R5CXQ6V0ZK9RD4EGED.pdf'],
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
        ['model' => 'Entris II Essential Analytical', 'heading' => 'Entris II Essential (BCE) Analytical Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'BCE224i-1S, BCE224-1S, BCE124i-1S, BCE124-1S, BCE64i-1S, BCE64-1S',
            'Readability (mg)'               => '0.1',
            'Weighing capacity (g)'          => '60 to 220 (BCE64: 60 / BCE124: 120 / BCE224: 220)',
            'Pan size (mm)'                  => 'Ø 90',
            'Repeatability, typical (mg)'    => '0.1',
            'Stabilization time, typical (sec.)' => '≤ 1.5',
            'Weighing chamber height (mm)'   => '240',
            'Dimensions, W × D × H (mm)'     => '219 × 317 × 345',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL) on "i" models / External on other models',
            'Display'                        => 'Hybrid LED and touch',
            'Interfaces'                     => 'USB Type-C, RS232 (9-pin)',
        ]],
    ],
];
