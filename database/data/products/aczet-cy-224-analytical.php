<?php

/**
 * Aczet CY 224 Analytical Balance - ONE individual product (specs_general = false, exact values).
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
$group    = 'CY Standard Series';
$type     = 'Analytical Balance';

$shortDescription = 'The Aczet CY 224 is an analytical balance with a 220 g capacity and 0.1 mg readability, using external calibration with a test weight. It has a 90 mm pan, a backlit LCD, several built-in weighing applications, RS 232C output (USB optional) and a glass draft shield. It is the standard-series counterpart of the internally calibrated CY 224C.';

$overview = '<p>The <strong>Aczet CY 224 Analytical Balance</strong> is a 220 g × 0.1 mg (0.0001 g) balance for routine analytical weighing. In Aczet\'s range the plain CY 224 is the standard-series model, which means it is calibrated externally with a certified test weight, while the CY 224C adds motorised internal calibration.</p>
<p><strong>Why labs choose the CY 224</strong></p>
<p>A 220 g capacity with 0.1 mg readability covers most day-to-day work in quality control, teaching and research labs: preparing standards, weighing reagents and checking batches. External calibration keeps the balance simple and usually keeps the purchase cost lower than an internal-calibration model, as long as a calibration weight is at hand (Aczet lists E1, E2 and F1 test weights as accessories) and calibration is done on your lab\'s schedule.</p>
<p><strong>Specifications (Aczet standard datasheet)</strong></p>
<ul>
<li><p>Capacity 220 g, readability 0.1 mg, repeatability 0.1 mg, linearity ± 0.2 mg.</p></li>
<li><p>Typical minimum weight 160 mg (USP 0.1%) and 16 mg (U = 1%).</p></li>
<li><p>Pan diameter 90 mm, response time 2 to 3 seconds, operating temperature 18 to 30 °C.</p></li>
</ul>
<p><strong>Operation and records</strong></p>
<p>An alphanumeric backlit LCD shows the result clearly. Application modes include piece counting, percentage weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation and an automatic statistic calculator, with many units from grams and milligrams to carats and ounces. The RS 232C interface (USB optional, with Windows direct communication) connects a printer or computer, and Aczet lists ISO, GLP / GMP and USP compliance.</p>
<p><strong>CY 224 or CY 224C?</strong></p>
<p>Choose the CY 224 when external calibration is acceptable. If the balance is used by several people, in a room with temperature swings or under a strict calibration routine, the internal-calibration CY 224C may suit better. Our team can help you compare the two and confirm the right model.</p>
<p><strong>Placement and accessories</strong></p>
<p>For stable readings put the balance on a firm, level bench away from draughts, direct sunlight and vibration. Aczet lists an anti-vibration pad, an anti-thief lock, a density kit, test weights, a remote display and a 21 CFR Part 11 PC software among its accessories for this series; ask us which ones suit your lab.</p>';

$features = [
    ['title' => '220 g × 0.1 mg', 'text' => 'Standard analytical range with 0.1 mg readability and 220 g capacity.'],
    ['title' => 'External calibration', 'text' => 'Calibrate with a certified test weight; a simple, cost-effective setup.'],
    ['title' => 'Backlit LCD and 90 mm pan', 'text' => 'Easy-to-read display and a pan sized for routine analytical weighing.'],
    ['title' => 'Multiple weighing modes', 'text' => 'Counting, percentage, check weighing, density, formulation and statistics.'],
    ['title' => 'RS 232C and optional USB', 'text' => 'Send results to a printer or PC for GLP / GMP records.'],
];

$advantages = [
    ['title' => 'Cost-effective choice', 'text' => 'The standard series has no motorised internal weights, so it is the simpler route to 0.1 mg weighing.'],
    ['title' => 'Same performance figures', 'text' => 'Datasheet repeatability 0.1 mg and linearity ± 0.2 mg, the same as the CY 224C.'],
    ['title' => 'Small samples', 'text' => 'Typical minimum weight 160 mg (USP 0.1%) and 16 mg at U = 1%.'],
    ['title' => 'Many units', 'text' => 'Grams, milligrams, carats, ounces and other units are built in.'],
    ['title' => 'Accessories available', 'text' => 'Density kit, test weights, printer, anti-vibration pad and more are listed by Aczet.'],
];

$faqs = [
    ['question' => 'What is the Aczet CY 224 analytical balance?', 'answer' => 'It is a 220 g × 0.1 mg analytical balance from Aczet with external calibration, a 90 mm pan and a backlit LCD, from the standard (non-C) series.'],
    ['question' => 'What is the difference between CY 224 and CY 224C?', 'answer' => 'Both read 0.1 mg up to 220 g. The CY 224 uses external calibration with a test weight, and the CY 224C has motorised internal calibration with built-in weights.'],
    ['question' => 'How do I calibrate the CY 224?', 'answer' => 'The CY 224 is calibrated externally, which means you place a certified test weight on the pan when prompted. Aczet lists E1, E2 and F1 test weights among its accessories.'],
    ['question' => 'What is the CY 224 price?', 'answer' => 'The price depends on the configuration, accessories and GST. Please send us an enquiry with the model name and we will share a quotation.'],
    ['question' => 'What are the repeatability and linearity of the CY 224?', 'answer' => 'The Aczet datasheet gives repeatability 0.1 mg and linearity ± 0.2 mg. Some web listings show a different linearity value, so please check the datasheet or ask us.'],
    ['question' => 'What is the minimum weight I can measure?', 'answer' => 'Aczet gives a typical minimum weight of 160 mg using the USP 0.1% criterion and 16 mg at U = 1%.'],
    ['question' => 'What interfaces does the CY 224 have?', 'answer' => 'It has an RS 232C interface, with USB (Windows direct communication) optional.'],
    ['question' => 'Is a 220 g × 0.1 mg balance the right choice for my lab?', 'answer' => 'It suits most routine analytical work, such as preparing standard solutions and weighing reagents or samples. If you need a smaller readability (0.01 mg), look at semi-micro balances, and tell us your application so we can suggest the right model.'],
];

$documents = [
    ['title' => 'CY Standard Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DI2.pdf'],
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
        ['model' => 'CY 224', 'heading' => 'Aczet CY 224 Analytical Balance 220 g × 0.1 mg', 'batch' => 1, 'specs' => [
            'Model' => 'CY 224',
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
            'Calibration and adjustment' => 'External calibration',
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
