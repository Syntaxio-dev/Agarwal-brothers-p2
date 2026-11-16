<?php

/**
 * Aczet CY 224C Analytical Balance - ONE individual product (specs_general = false, exact values).
 *
 * Sources (all read 14 Nov 2026; only aczet.com opened):
 *  - Aczet product page https://aczet.com/product/analytical-balance-1 ('Full Specs' tables per model, saved copy)
 *  - Aczet datasheets: DataSheet-_Analytical-Professional_(1)1.pdf (CY 64C ... CY 404C) and DataSheet-_Analytical-Standard_(1).pdf (CY 64 ... CY 404), both 1 page, rendered and read.
 *  Spec values come from the DATASHEETS. Differences from the website 'Full Specs' tables (datasheet used):
 *   - Minimum load: datasheet 10 mg, website 1 mg.
 *   - Linearity: datasheet +/-0.2 mg for CY 124C, CY 224C and CY 224; website shows +/-0.3 mg for CY 224C and CY 224 (CY 124C +/-0.2 matches).
 *   - Chamber height: datasheet 239.5 mm, website 228.5 mm.
 *
 * Market evidence: see research-analytical.md (220 g x 0.1 mg is the classic Indian range; CY 224C and CY 224 appear most in GeM, IndiaMART, Moglix and IndustryBuying listings; CY 124C is the third pick).
 * Doubts: no price is given (dealer prices vary widely and are not Aczet's); there is no separate Aczet page for this single model, the series page and datasheet were used; photos are small crops from Aczet brochures (low resolution).
 */

$category = 'Analytical Balance';
$brand    = 'Aczet';
$group    = 'CY-C Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Aczet CY 224C is an analytical balance with a 220 g capacity and 0.1 mg readability, the classic range for pharma, chemical, food and research labs. It has motorised internal calibration, a 90 mm pan under a three-side-opening glass draft shield, a backlit LCD, RS 232C output (USB optional) and a built-in clock for GLP records. Aczet lists it in its CY-C Professional Level series.';

$overview = '<p>The <strong>Aczet CY 224C Analytical Balance</strong> weighs up to 220 g with a readability of 0.1 mg (0.0001 g), which is the capacity and resolution most laboratories look for when they search for a "220 g 0.1 mg analytical balance". It belongs to the Professional Level CY-C series, where the letter C stands for the motorised internal calibration.</p>
<p><strong>Calibration you do not have to think about</strong></p>
<p>Built-in motorised weights calibrate the balance at the press of a key, and the Perfect Self Auto Calibration (PSAC) function repeats the internal calibration when the ambient temperature changes by a set value or after a defined time. That keeps results steady in labs where the temperature moves during the day.</p>
<p><strong>Specifications that matter at the bench</strong></p>
<ul>
<li><p>Capacity 220 g, readability 0.1 mg, repeatability 0.1 mg, linearity ± 0.2 mg (Aczet datasheet).</p></li>
<li><p>Typical minimum weight 160 mg by the USP 0.1% criterion and 16 mg at U = 1%, so small samples can be weighed with confidence.</p></li>
<li><p>Response time of 2 to 3 seconds and a 90 mm diameter pan.</p></li>
</ul>
<p><strong>Built for daily laboratory work</strong></p>
<p>The balance has a large backlit LCD, a spacious glass draft shield that opens on three sides, easy levelling with a bubble indicator and a capacity tracker on the display that helps you avoid overloading the weighing cell. Eleven built-in applications include piece counting, percentage weighing, formulation, check weighing, density determination and statistics, and a below-balance weighing hook is provided. A built-in clock supports ISO / GLP / GMP documentation, and the RS 232C interface (USB optional) connects a printer or PC.</p>
<p><strong>Who it suits</strong></p>
<p>Aczet names food, pharma, chemical, cosmetic, polymer, cement and testing laboratories as typical users. If you need 120 g or 320 g instead of 220 g, the same CY-C series covers them. Tell our team about your application and we will confirm the right model for you.</p>';

$features = [
    ['title' => '220 g × 0.1 mg', 'text' => 'The standard analytical range: 220 g capacity with 0.1 mg readability.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Built-in weights calibrate the balance at a keypress; PSAC repeats it when temperature or time changes.'],
    ['title' => 'Three-side-opening draft shield', 'text' => 'A spacious glass shield with a 90 mm pan keeps air currents off the sample.'],
    ['title' => 'Clear backlit LCD', 'text' => 'Easy to read in any room lighting, with a capacity tracker to avoid overloads.'],
    ['title' => 'GLP-ready output', 'text' => 'Built-in clock and RS 232C interface (USB optional) for traceable records.'],
];

$advantages = [
    ['title' => 'Fewer manual calibrations', 'text' => 'PSAC recalibrates after a temperature change or a set time, so you spend less time with test weights.'],
    ['title' => 'Small samples welcome', 'text' => 'Typical minimum weight is 160 mg (USP 0.1%) and 16 mg at U = 1%.'],
    ['title' => 'Many weighing jobs in one balance', 'text' => 'Counting, percentage, check weighing, density, formulation and statistics are built in.'],
    ['title' => 'Easy to keep clean', 'text' => 'The draft shield parts can be taken apart and put back, so cleaning is simple.'],
    ['title' => 'Below-balance weighing', 'text' => 'A hook lets you weigh objects that do not sit on the pan.'],
];

$faqs = [
    ['question' => 'What is the capacity and readability of the Aczet CY 224C?', 'answer' => 'The CY 224C weighs up to 220 g with a readability of 0.1 mg (0.0001 g). Repeatability is 0.1 mg and linearity is ± 0.2 mg according to the Aczet datasheet.'],
    ['question' => 'Does the CY 224C have internal calibration?', 'answer' => 'Yes. It has motorised internal calibration with built-in weights. The PSAC function repeats the internal calibration when the temperature changes by a set value or after a defined time.'],
    ['question' => 'What is the difference between CY 224C and CY 224?', 'answer' => 'Both are 220 g × 0.1 mg analytical balances with the same pan and display. The CY 224C has motorised internal calibration, while the standard CY 224 uses external calibration with a test weight.'],
    ['question' => 'What is the CY 224C price?', 'answer' => 'The price depends on the configuration, accessories and GST. Please send us an enquiry with the model name and we will share a quotation.'],
    ['question' => 'What is the smallest sample I can weigh on the CY 224C?', 'answer' => 'Aczet gives a typical minimum weight of 160 mg using the USP 0.1% criterion and 16 mg at U = 1%. The minimum load is 10 mg on the datasheet.'],
    ['question' => 'What pan size and response time does it have?', 'answer' => 'The pan is 90 mm in diameter and the response time is 2 to 3 seconds.'],
    ['question' => 'Can I connect the CY 224C to a printer or computer?', 'answer' => 'Yes. It has an RS 232C interface, with USB (Windows direct communication) optional. Aczet lists a CPR 02 dot-matrix data printer and 21 CFR Part 11 PC software among the accessories.'],
    ['question' => 'Which applications is a 220 g × 0.1 mg analytical balance used for?', 'answer' => 'Typical uses are preparing standard solutions, weighing active ingredients and reagents, formulation, quality control and research. Aczet names food, pharma, chemical, cosmetic, polymer, cement and testing labs as users of the series.'],
];

$documents = [
    ['title' => 'CY-C Professional Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DI1.pdf'],
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
        ['model' => 'CY 224C', 'heading' => 'Aczet CY 224C Analytical Balance 220 g × 0.1 mg', 'batch' => 1, 'specs' => [
            'Model' => 'CY 224C',
            'Weighing capacity (g)' => '220',
            'Readability (mg)' => '0.1',
            'Minimum load (mg)' => '10',
            'Repeatability, sd (mg)' => '0.1',
            'Linearity (mg)' => '± 0.2',
            'Tare range, subtractive (g)' => '-220',
            'Minimum weight, USP 0.1%, typical (mg)' => '160',
            'Minimum weight, U = 1%, typical (mg)' => '16',
            'Pan size (mm)' => 'Ø 90',
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
            'Balance size, W × D × H (mm)' => '342.5 × 212 × 341 (chamber height: 239.5)',
            'Weight, approx. (kg)' => '9.3',
        ]],
    ],
];
