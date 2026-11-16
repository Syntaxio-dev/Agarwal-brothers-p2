<?php

/**
 * Aczet CY 323C Precision Balance (milligram class, 320 g x 0.001 g) - ONE individual product (specs_general = false, exact values).
 *
 * Sources (read 14 Nov 2026, only aczet.com opened):
 *  - Aczet brochure 'Precision Balance CY-C Series' https://aczet.com/upload/product_documentation/Precision_-Professional_Series_(2).pdf (5 pages, page 4 is the technical datasheet; attached as the document). It lists CY 123C, 223C, 323C, 363C, 423C, 513C, 623C, 723C, 1003C, 2003C, 3003C.
 *  - The same brochure for the features (internal calibration, PSAC, IP54, 11 applications, GLP, Mono-Tech).
 * Every value below is from the datasheet table: capacity 320 g, minimum load 0.02 g, readability 0.001 g, repeatability 0.001 g, linearity 0.002 g, tare -320 g, USP minimum weight 1 g (0.1%) and 0.1 g (U = 1%), pan 128 x 128 mm, 342.5 x 212 x 271 mm with chamber height 169.5 mm, 7.5 kg.
 * Differences / doubts:
 *  - The ordinary Aczet 'Precision Balances - CY Series' sheet and the website page product/gold-and-carat-balance describe CY 323 (no C) with EXTERNAL calibration, 110 mm round pan, 15 to 35 C; not used for this C model. Another Aczet page (product/-2) lists a 'CZ 323' with a 110 mm pan: a different series (CZ), not used.
 *  - Aczet has no web page of its own for the single model CY 323C; the series brochure was used. Listings in India show the pan as 128 x 128 mm, which agrees with the datasheet.
 *  - The datasheet's USP minimum-weight cells are not labelled per column in the text layer (values '1 g' and '0.1 g' sit under 'USP 0.1%' and 'U=1%'); read from the rendered page order. Worth a second look by a person.
 *  - IP54 is stated in the Aczet series brochure; no IP test report is attached.
 * Market evidence: see research-precision.md (CY 323C appears in two GeM listings, Moglix, UP Scales, IndustryBuying and US dealer lists; it is the most listed 0.001 g model).
 * No price in any text (dealer prices vary and are not Aczet's).
 */

$category = 'Precision Balance';
$brand    = 'Aczet';
$group    = 'CY-C Series';
$type     = 'Precision Balance';

$shortDescription = 'The Aczet CY 323C is a precision balance with a 320 g capacity and 0.001 g (1 mg) readability, built for labs that need milligram weighing above the analytical range. It has motorised internal calibration, a 128 × 128 mm pan under a three-side-opening glass draft shield, a backlit LCD and 11 built-in applications. Aczet lists it in its CY-C Professional Level series.';

$overview = '<p>The <strong>Aczet CY 323C Precision Balance</strong> weighs up to 320 g with a readability of 0.001 g (1 mg). It is the balance many labs choose when they search for a "320 g 0.001 g precision balance" and need a milligram result on samples that are too heavy for an analytical balance. The letter C in the name stands for the motorised internal calibration of the Professional Level CY-C series.</p>
<p><strong>Calibration that looks after itself</strong></p>
<p>Built-in motorised weights calibrate the balance at a keypress. The Perfect Self Auto Calibration (PSAC) function repeats the internal calibration automatically when the temperature changes or after a set time, so readings stay reliable through the working day.</p>
<p><strong>Specifications that matter at the bench</strong></p>
<ul>
<li><p>Capacity 320 g, readability 0.001 g, repeatability 0.001 g, linearity ± 0.002 g (Aczet datasheet).</p></li>
<li><p>Minimum load 0.02 g, typical USP minimum weight 1 g (0.1%) and 0.1 g at U = 1%.</p></li>
<li><p>Pan 128 × 128 mm, response time 2 to 3 seconds, tare range up to the full capacity.</p></li>
</ul>
<p><strong>Built for daily laboratory work</strong></p>
<p>The balance has a large backlit LCD, a spacious glass draft shield that opens on three sides and an IP54 protection class against dust and water splashes, as stated by Aczet for the series. The shield parts can be taken apart for cleaning. A capacity tracker on the display warns about overloads, a monitoring function watches ambient conditions, and a hook allows below-balance weighing. Eleven built-in applications include piece counting, percentage weighing, formulation, pipette check, check weighing, totalization, density and statistics. A built-in GLP documentation function and the RS 232C interface (USB optional) support ISO / GLP / GMP records.</p>
<p><strong>Who it suits</strong></p>
<p>Aczet names food, pharma, chemical, cosmetic, polymer, cement and testing laboratories as typical users. If you need 220 g, 510 g or another capacity in the same series, tell our team about your application and we will confirm the right model for you.</p>';

$features = [
    ['title' => '320 g × 0.001 g', 'text' => 'Milligram readability with a 320 g capacity, one step above the analytical range.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Built-in weights calibrate at a keypress; PSAC repeats it when temperature or time changes.'],
    ['title' => 'Draft shield with IP54', 'text' => 'Three-side-opening glass shield; Aczet states IP54 protection against dust and water splashes for the series.'],
    ['title' => '11 built-in applications', 'text' => 'Counting, percentage, formulation, pipette check, check weighing, density, statistics and more.'],
    ['title' => 'GLP-ready records', 'text' => 'Built-in GLP documentation and RS 232C interface (USB optional) for traceable results.'],
];

$advantages = [
    ['title' => 'Less time with test weights', 'text' => 'PSAC runs the internal calibration for you when conditions change.'],
    ['title' => 'Light samples are fine', 'text' => 'Typical minimum weight is 1 g by the USP 0.1% criterion and 0.1 g at U = 1%.'],
    ['title' => 'Easy to keep clean', 'text' => 'The draft shield parts can be dismantled and assembled again without tools.'],
    ['title' => 'Overload protection', 'text' => 'A capacity tracker on the display helps protect the weighing cell.'],
    ['title' => 'Weigh what does not fit the pan', 'text' => 'A below-balance hook is provided for oversized, magnetic or hot and cold items.'],
];

$faqs = [
    ['question' => 'What are the capacity and readability of the Aczet CY 323C?', 'answer' => 'The CY 323C weighs up to 320 g with a readability of 0.001 g (1 mg). Repeatability is 0.001 g and linearity is ± 0.002 g according to the Aczet datasheet.'],
    ['question' => 'Does the CY 323C have internal calibration?', 'answer' => 'Yes. The C in CY 323C means motorised internal calibration with built-in weights. PSAC repeats the calibration automatically when temperature or time changes.'],
    ['question' => 'What is the difference between CY 323C and CY 323?', 'answer' => 'Both are 320 g × 0.001 g precision balances. The CY 323C has motorised internal calibration and is in the Professional Level series; the CY 323 uses external calibration with a test weight.'],
    ['question' => 'What is the CY 323C price?', 'answer' => 'The price depends on the configuration, accessories and GST. Send us an enquiry with the model name and we will share a quotation.'],
    ['question' => 'What pan size does the CY 323C have?', 'answer' => 'The pan is 128 × 128 mm under a glass draft shield. The balance measures 342.5 × 212 × 271 mm and weighs about 7.5 kg.'],
    ['question' => 'What is the smallest sample the CY 323C can weigh?', 'answer' => 'Aczet gives a typical minimum weight of 1 g by the USP 0.1% criterion and 0.1 g at U = 1%. The minimum load is 0.02 g.'],
    ['question' => 'Can I connect the CY 323C to a printer or computer?', 'answer' => 'Yes. It has an RS 232C interface, with USB (Windows direct communication) optional. Aczet lists a CPR 02 dot-matrix data printer and 21 CFR Part 11 PC software among the accessories.'],
    ['question' => 'Which labs use a 320 g × 1 mg precision balance?', 'answer' => 'Typical uses are preparing formulations, weighing raw materials and reagents, quality control and testing. Aczet names food, pharma, chemical, cosmetic, polymer, cement and testing labs as users of the series.'],
];

$documents = [
    ['title' => 'CY-C Precision Balance Brochure and Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG2PR1A9BWQ4T8ZH3NC6DI1.pdf'],
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
        ['model' => 'CY 323C', 'heading' => 'Aczet CY 323C Precision Balance 320 g × 0.001 g', 'batch' => 1, 'specs' => [
            'Model' => 'CY 323C',
            'Weighing capacity (g)' => '320',
            'Readability (g)' => '0.001',
            'Minimum load (g)' => '0.02',
            'Repeatability, sd (g)' => '0.001',
            'Linearity (g)' => '± 0.002',
            'Tare range, subtractive (g)' => '-320',
            'Minimum weight, USP 0.1%, typical (g)' => '1',
            'Minimum weight, U = 1%, typical (g)' => '0.1',
            'Pan size (mm)' => '128 × 128',
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
            'Balance size, W × D × H (mm)' => '342.5 × 212 × 271 (chamber height: 169.5)',
            'Weight, approx. (kg)' => '7.5',
        ]],
    ],
];
