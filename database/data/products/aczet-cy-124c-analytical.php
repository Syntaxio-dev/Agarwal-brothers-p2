<?php

/**
 * Aczet CY 124C Analytical Balance - ONE individual product (specs_general = false, exact values).
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

$shortDescription = 'The Aczet CY 124C is an analytical balance with a 120 g capacity and 0.1 mg readability and motorised internal calibration. A 90 mm pan under a glass draft shield, a backlit LCD, RS 232C output (USB optional) and a built-in clock make it a practical 120 g balance for pharma, chemical, food and research labs. It is part of Aczet\'s CY-C Professional Level series.';

$overview = '<p>The <strong>Aczet CY 124C Analytical Balance</strong> offers a 120 g capacity with 0.1 mg (0.0001 g) readability. A 120 g range suits work where samples and weighing vessels are light, such as titration, assay preparation and standard weighing, and the CY 124C adds motorised internal calibration.</p>
<p><strong>Internal calibration that follows the room</strong></p>
<p>Motorised built-in weights calibrate the balance on demand, and the Perfect Self Auto Calibration (PSAC) function repeats the internal calibration when the ambient temperature changes by a set value or after a defined time interval.</p>
<p><strong>Specifications (Aczet datasheet)</strong></p>
<ul>
<li><p>Capacity 120 g, readability 0.1 mg, repeatability 0.1 mg, linearity ± 0.2 mg.</p></li>
<li><p>Typical minimum weight 160 mg (USP 0.1%) and 16 mg (U = 1%).</p></li>
<li><p>Pan diameter 90 mm, response time 2 to 3 seconds, tare range up to -120 g.</p></li>
</ul>
<p><strong>Easy to use, easy to document</strong></p>
<p>The balance has a large backlit LCD, a three-side-opening glass draft shield, a levelling bubble and a capacity tracker for overload monitoring. Application modes include piece counting, percentage weighing, check weighing, density determination, formulation and statistics. A built-in clock supports ISO / GLP / GMP records, and the RS 232C interface (USB optional) connects a printer or PC.</p>
<p><strong>CY 124C, CY 224C or CY 324C?</strong></p>
<p>Pick by the heaviest load you weigh, including the container. The CY 124C reads 0.1 mg up to 120 g; the CY 224C goes to 220 g and the CY 324C to 320 g with the same readability. Tell our team what you weigh and we will suggest the right capacity.</p>
<p><strong>Placement and accessories</strong></p>
<p>For stable readings put the balance on a firm, level bench away from draughts, direct sunlight and vibration. Aczet lists an anti-vibration pad, an anti-thief lock, a density kit, test weights, a remote display, a battery backup and 21 CFR Part 11 PC software among its accessories for this series; ask us which ones suit your lab, and we will share the exact figures and a quotation for the CY 124C.</p>';

$features = [
    ['title' => '120 g × 0.1 mg', 'text' => 'A 120 g capacity with 0.1 mg readability for light samples and vessels.'],
    ['title' => 'Motorised internal calibration', 'text' => 'Built-in weights calibrate on demand; PSAC repeats it on temperature or time change.'],
    ['title' => 'Glass draft shield, 90 mm pan', 'text' => 'A three-side-opening shield protects the sample from air movement.'],
    ['title' => 'Backlit LCD with capacity tracker', 'text' => 'Clear display in any lighting and a guard against possible overloads.'],
    ['title' => 'GLP-ready records', 'text' => 'Built-in clock plus RS 232C output (USB optional).'],
];

$advantages = [
    ['title' => 'Right-sized capacity', 'text' => '120 g covers typical titration and standard-preparation work without paying for a larger range.'],
    ['title' => 'Stable results', 'text' => 'PSAC recalibrates after a temperature change or a set time.'],
    ['title' => 'Small samples', 'text' => 'Typical minimum weight 160 mg (USP 0.1%) and 16 mg at U = 1%.'],
    ['title' => 'Many applications built in', 'text' => 'Counting, percentage, check weighing, density, formulation and statistics.'],
    ['title' => 'Same platform as CY 224C', 'text' => 'Same 90 mm pan, display and interfaces as the 220 g model.'],
];

$faqs = [
    ['question' => 'What are the specifications of the Aczet CY 124C?', 'answer' => 'Capacity 120 g, readability 0.1 mg, repeatability 0.1 mg, linearity ± 0.2 mg, 90 mm pan, response time 2 to 3 seconds and motorised internal calibration, according to the Aczet datasheet.'],
    ['question' => 'Does the CY 124C have internal calibration?', 'answer' => 'Yes. The C in CY 124C stands for motorised internal calibration with built-in weights. The standard CY 124 uses external calibration.'],
    ['question' => 'What is the CY 124C price?', 'answer' => 'The price depends on the configuration, accessories and GST. Please send us an enquiry with the model name and we will share a quotation.'],
    ['question' => 'Should I buy the CY 124C or the CY 224C?', 'answer' => 'Both read 0.1 mg. Choose the 120 g CY 124C if your heaviest load, including the container, stays below 120 g; choose the 220 g CY 224C if you need more headroom.'],
    ['question' => 'What is the minimum weight of the CY 124C?', 'answer' => 'Aczet gives a typical minimum weight of 160 mg using the USP 0.1% criterion and 16 mg at U = 1%.'],
    ['question' => 'What operating conditions does the CY 124C need?', 'answer' => 'Aczet gives the operating temperature as 18 to 30 °C. Place the balance on a stable bench away from draughts and vibration; an anti-vibration pad is listed as an accessory.'],
    ['question' => 'Can the CY 124C connect to a printer or a computer?', 'answer' => 'Yes, through the RS 232C interface, with USB (Windows direct communication) optional. A CPR 02 dot-matrix printer is listed by Aczet as an accessory.'],
    ['question' => 'What is the weight and size of the balance?', 'answer' => 'About 9.3 kg and 342.5 × 212 × 341 mm (width × depth × height).'],
];

$documents = [
    ['title' => 'CY-C Professional Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DI3.pdf'],
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
        ['model' => 'CY 124C', 'heading' => 'Aczet CY 124C Analytical Balance 120 g × 0.1 mg', 'batch' => 1, 'specs' => [
            'Model' => 'CY 124C',
            'Weighing capacity (g)' => '120',
            'Readability (mg)' => '0.1',
            'Minimum load (mg)' => '10',
            'Repeatability, sd (mg)' => '0.1',
            'Linearity (mg)' => '± 0.2',
            'Tare range, subtractive (g)' => '-120',
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
