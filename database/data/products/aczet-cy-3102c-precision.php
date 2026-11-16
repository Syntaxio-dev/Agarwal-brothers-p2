<?php

/**
 * Aczet CY 3102C Precision Balance (3100 g, 0.01 g, open top pan) - ONE individual product (specs_general = false, exact values).
 *
 * Sources (read 14 Nov 2026, only aczet.com opened):
 *  - Aczet datasheet 'Top Pan - Professional' https://aczet.com/upload/product_documentation/Datasheet-_Top_Pan_-Professional1.pdf (1 page, attached as the document) and the brochure 'Top Pan Balance CY-C Series' https://aczet.com/upload/product_documentation/Top_Pan_-Professional_Series_(3).pdf (features).
 * Datasheet values for CY 3102C: capacity 3100 g, minimum load 0.5 g, readability 0.01 g, repeatability 0.01 g, linearity 0.02 g, tare -3100 g, USP minimum weight 10 g (0.1%) and 1 g (U = 1%), open pan 198 x 205 mm, 342.5 x 212 x 89.5 mm, 7.5 kg.
 * Differences / doubts:
 *  - 'Precision Balances - CY Series' (https://www.aczet.com/upload/product_documentation/Precision_Balances.pdf) prints CY 6102C linearity 0.02 g; the top-pan datasheet prints 0.03 g. The datasheet is used (the attached document). CY 3102C has 0.02 g in both documents.
 *  - The sheet gives pan 128 x 128 mm for the models up to 610 g (with a draft shield) and 198 x 205 mm for 1202 g and above (open pan, no draft shield); CY 3102C is in the second group. IP54 and the three-side draft shield are NOT claimed (they are stated for the precision milligram series only).
 *  - The datasheet's USP minimum-weight cells are not labelled per column in the text layer ('10 g' and '1 g'); read from the rendered page order. Worth a second look by a person.
 *  - Aczet has no page for the single model; the top-pan series page lists the non-C CY 1202 ... CY 6102 (external calibration). Not used for this C model.
 * Market evidence: see research-precision.md. No price in any text.
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CY-C Series';
$type     = 'Precision Balance';

$shortDescription = 'The Aczet CY 3102C is a top pan precision balance with a 3100 g capacity and 0.01 g readability, made for routine weighing in quality control, production, education and field work. It has motorised internal calibration, a large 198 × 205 mm open stainless-steel pan, a backlit LCD, 11 built-in applications and an RS 232C interface. Aczet lists it in its CY-C Professional Level series.';

$overview = '<p>The <strong>Aczet CY 3102C Precision Balance</strong> weighs up to 3100 g with a readability of 0.01 g (10 mg). That makes it a practical choice when you search for a "3 kg 0.01 g top pan balance" for incoming-goods checks, formulation, quality control or teaching labs. The letter C marks the motorised internal calibration of the Professional Level CY-C series.</p>
<p><strong>Calibration at a keypress</strong></p>
<p>Built-in motorised weights calibrate the balance without a loose test weight. The Perfect Self Auto Calibration (PSAC) function repeats the internal calibration when the temperature changes or after a set time, so the balance keeps its accuracy in rooms where conditions change.</p>
<p><strong>Specifications that matter at the bench</strong></p>
<ul>
<li><p>Capacity 3100 g, readability 0.01 g, repeatability 0.01 g, linearity ± 0.02 g (Aczet datasheet).</p></li>
<li><p>Minimum load 0.5 g and a typical USP minimum weight of 10 g (0.1%) and 1 g at U = 1%.</p></li>
<li><p>Open pan of 198 × 205 mm with no draft shield, response time 2 to 3 seconds.</p></li>
</ul>
<p><strong>Built for routine weighing</strong></p>
<p>The balance has a large backlit LCD with 15 mm digits, a capacity tracker that helps avoid overloading the weighing cell, a monitoring function for ambient conditions and a hook for below-balance weighing. Eleven built-in applications include piece counting, percentage weighing, formulation, pipette check, check weighing, totalization, density and statistics. A built-in GLP documentation function and the RS 232C interface (USB optional) support ISO / GLP / GMP records, and the Mono-Tech weighing sensor is based on electromagnetic force compensation.</p>
<p><strong>Who it suits</strong></p>
<p>Aczet names food, pharma, chemical, cosmetic, polymer, cement and testing laboratories as typical users of the series. If you need a different capacity, for example 2200 g, 4100 g or 6100 g, the same CY-C top pan range covers it. Tell our team about your application and we will confirm the right model for you.</p>';

$features = [
    ['title' => '3100 g × 0.01 g', 'text' => 'A top pan range for routine weighing, with 3100 g capacity and 0.01 g readability.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Built-in weights calibrate at a keypress; PSAC repeats it when temperature or time changes.'],
    ['title' => 'Large open pan', 'text' => 'A 198 × 205 mm pan with no draft shield makes loading bulky samples and containers easy.'],
    ['title' => '11 built-in applications', 'text' => 'Counting, percentage, formulation, pipette check, check weighing, density, statistics and more.'],
    ['title' => 'GLP-ready records', 'text' => 'Built-in GLP documentation and RS 232C interface (USB optional) for traceable results.'],
];

$advantages = [
    ['title' => 'Fewer manual calibrations', 'text' => 'PSAC recalibrates after a temperature change or a set time.'],
    ['title' => 'Easy loading', 'text' => 'The open pan takes beakers, drums and trays without opening a shield.'],
    ['title' => 'Easy to read', 'text' => 'A large backlit LCD with 15 mm digits is readable in any room lighting.'],
    ['title' => 'Overload protection', 'text' => 'A capacity tracker on the display helps protect the weighing cell.'],
    ['title' => 'Below-balance weighing', 'text' => 'A hook is provided for objects that do not sit on the pan.'],
];

$faqs = [
    ['question' => 'What are the capacity and readability of the Aczet CY 3102C?', 'answer' => 'The CY 3102C weighs up to 3100 g with a readability of 0.01 g. Repeatability is 0.01 g and linearity is ± 0.02 g according to the Aczet datasheet.'],
    ['question' => 'Does the CY 3102C have internal calibration?', 'answer' => 'Yes. The C in CY 3102C means motorised internal calibration with built-in weights. PSAC repeats the calibration automatically when temperature or time changes.'],
    ['question' => 'What is the difference between CY 3102C and CY 3102?', 'answer' => 'Both are 3100 g × 0.01 g top pan balances. The CY 3102C has motorised internal calibration, while the CY 3102 uses external calibration with a test weight.'],
    ['question' => 'What is the CY 3102C price?', 'answer' => 'The price depends on the configuration, accessories and GST. Send us an enquiry with the model name and we will share a quotation.'],
    ['question' => 'What pan size does the CY 3102C have?', 'answer' => 'The open stainless-steel pan is 198 × 205 mm and there is no draft shield. The balance measures 342.5 × 212 × 89.5 mm and weighs about 7.5 kg.'],
    ['question' => 'What is the smallest sample the CY 3102C can weigh?', 'answer' => 'Aczet gives a typical minimum weight of 10 g by the USP 0.1% criterion and 1 g at U = 1%. The minimum load is 0.5 g.'],
    ['question' => 'Can I connect the CY 3102C to a printer or computer?', 'answer' => 'Yes. It has an RS 232C interface, with USB (Windows direct communication) optional. Aczet lists a CPR 02 dot-matrix data printer and 21 CFR Part 11 PC software among the accessories.'],
    ['question' => 'Which jobs suit a 0.01 g top pan balance?', 'answer' => 'Typical uses are weighing raw materials and finished goods, formulation, batch checks, parts counting and teaching labs. Aczet names food, pharma, chemical, cosmetic, polymer, cement and testing labs as users of the series.'],
];

$documents = [
    ['title' => 'CY-C Top Pan Professional Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DI2.pdf'],
];

return [
    'category'          => $category,
    'brand'             => $brand,
    'model_group'       => $group,
    'product_type'      => $type,
    'specs_general'     => false,
    'short_description' => $shortDescription,
    'overview'          => $overview,
    'features'          => $features,
    'advantages'        => $advantages,
    'faqs'              => $faqs,
    'documents'         => $documents,

    'models' => [
        ['model' => 'CY 3102C', 'heading' => 'Aczet CY 3102C Precision Balance 3100 g × 0.01 g', 'batch' => 1, 'specs' => [
            'Model' => 'CY 3102C',
            'Weighing capacity (g)' => '3100',
            'Readability (g)' => '0.01',
            'Minimum load (g)' => '0.5',
            'Repeatability, sd (g)' => '0.01',
            'Linearity (g)' => '± 0.02',
            'Tare range, subtractive (g)' => '-3100',
            'Minimum weight, USP 0.1%, typical (g)' => '10',
            'Minimum weight, U = 1%, typical (g)' => '1',
            'Pan size (mm)' => '198 × 205 (open pan, no draft shield)',
            'Response time (sec.)' => '2 to 3',
            'Display' => 'Alphanumeric backlit LCD',
            'Calibration and adjustment' => 'Motorised internal calibration',
            'Units' => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)' => '18 to 30',
            'Sensitivity drift (ppm/°C)' => '± 2 (2 × 10⁻⁶ / °C)',
            'Interface' => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes' => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Power supply' => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance' => 'ISO, GLP / GMP and USP',
            'Balance size, W × D × H (mm)' => '342.5 × 212 × 89.5',
            'Weight, approx. (kg)' => '7.5',
        ]],
    ],
];
