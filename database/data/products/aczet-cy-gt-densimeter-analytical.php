<?php

/**
 * Aczet CY 224GT Electronic Densimeter (CY-GT Series, 0.1 mg class) - ONE model, exact values (specs_general = false).
 *
 * Sources (read 14 Nov 2026, only aczet.com was opened):
 *  - Aczet product page https://aczet.com/product/electronic-densimeter (five CY-GT models with 'Full Specs' tables)
 *  - Aczet datasheet "CY - GT SERIES Electronic Densimeter, Professional Level"
 *    https://aczet.com/upload/product_documentation/Electronic_Densimeter_PDF_(1).pdf (1 page, same five models). Rendered and read.
 *
 * Models on aczet.com (CY-GT series): CY 224GT (220 g, 0.1 mg), CY 323GT (320 g, 1 mg), CY 613GT (600 g, 1 mg), CY 612GT (600 g, 10 mg),
 * CY 3102GT (3100 g, 10 mg). ONLY the CY 224GT is an analytical-class (0.1 mg) balance, so only it is in this file. The other four are
 * precision-class and belong under 'Precision Balance' if the owner wants them.
 *
 * Differences from the pasted entry (owner to confirm):
 *  - The paste says 'CY 204GT'. Aczet has NO CY 204GT: the 220 g / 0.1 mg model is the CY 224GT (page and datasheet agree).
 *  - specs_general is FALSE on purpose: this is a single model, so ranges would be wrong; the values are exact.
 * Differences between the page and the datasheet (datasheet used where they differ):
 *  - Interface: page says 'RS 232C, optional USB (Windows direct communication)'; datasheet says 'RS 232'. Datasheet used (USB not claimed).
 *  - Display: page says 'Alpha numeric backlit LCD'; datasheet says 'Large LCD backlit display'. Datasheet wording used.
 *  - The page prints 'Accuracy 0.001 g' for the CY 224GT; the datasheet does not print an accuracy row, so it is left out of the specs.
 *  - The datasheet gives density resolution, volume display, repeatability (g/cm3) and measured density all as 0.0001 for the CY 224GT
 *    (the decimal point is missing in the PDF text; the rendered page and the page table show 0.0001).
 *  - The page's overview says 'Standard USB and RS232 interface' for the series; not used (datasheet says RS 232).
 * Not confirmed: the product photo on the page is the series photo and its label reads CY 323GT, not 224GT; USP compliance is not stated for this
 * product; what the optional 'Powder Density' and 'Liquid Density' accessories include and cost (the datasheet only names them).
 * Nothing is claimed about Agarwal Brothers.
 */

$category = 'Analytical Balance';
$brand    = 'Aczet';
$group    = 'CY-GT Series';
$type     = 'Electronic Densimeter';

$shortDescription = 'The Aczet CY 224GT is an electronic densimeter built on a 0.1 mg analytical balance with a 220 g capacity. Using the Archimedes principle it weighs a solid or floating sample in air and then in water and shows the density directly, to 0.0001 g/cm³. Water temperature compensation, a choice of liquid media (water, ethanol or others), a complete windproof and dustproof cover, a built-in date and time stamp for GMP / GLP / ISO records and an RS 232 interface make it practical for rubber, plastic, alloy and other material testing labs.';

$overview = '<p>The <strong>Aczet CY 224GT Electronic Densimeter</strong> turns a 0.1 mg analytical balance into a density meter. It belongs to Aczet\'s CY-GT series of professional-level densimeters and is the model with the finest resolution: a 220 g capacity, 0.0001 g readability and a density resolution of 0.0001 g/cm³.</p>'
    . '<p><strong>How it measures</strong></p>'
    . '<p>The densimeter works on the Archimedes principle and measures solid or floating samples, with an optional set-up for liquids. Operation takes three steps: put the sample on the pan and measure its weight in air, put the sample into water and measure its weight in water, and read the density on the display. The pan for weighing in air is 30 mm in diameter and the pan for weighing in water is 60 mm in diameter. The minimum sample weight is 1 g.</p>'
    . '<p><strong>Accurate in real conditions</strong></p>'
    . '<ul>'
    . '<li><p><strong>Water temperature compensation:</strong> the actual water temperature is compensated, because temperature plays a large role in density results.</p></li>'
    . '<li><p><strong>Changing liquid medium:</strong> use a different liquid, such as water or ethanol, for samples with air bubbles or complex shapes.</p></li>'
    . '<li><p><strong>Mono-Tech sensor:</strong> the weighing cell is based on electromagnetic force compensation (EMFC).</p></li>'
    . '<li><p><strong>Cover:</strong> a complete windproof and dustproof cover protects the measurement.</p></li>'
    . '</ul>'
    . '<p><strong>Records and applications</strong></p>'
    . '<p>A built-in print function adds date and time to your results for GMP / GLP / ISO documentation, and an RS 232 interface is provided. Aczet recommends the CY-GT series for rubber, plastic, wire and cable, composite and polymer materials, footwear, tires and hard alloys, for quality control, research or development. Powder density and liquid density are available as optional accessories.</p>'
    . '<p>If you also need a larger capacity, the CY-GT series has other models with 320 g, 600 g and 3100 g capacities at lower resolution. Tell our team your sample size and the density resolution you need, and we will confirm the right model.</p>';

$features = [
    ['title' => 'Density to 0.0001 g/cm³', 'text' => 'The 220 g, 0.1 mg balance shows density with a resolution of 0.0001 g/cm³.'],
    ['title' => 'Three-step operation', 'text' => 'Weigh the sample in air, weigh it in water and read the density on the display.'],
    ['title' => 'Water temperature compensation', 'text' => 'The actual water temperature is compensated for a more reliable density result.'],
    ['title' => 'Solid and floating samples', 'text' => 'Handles solid and floating samples, with other liquid media such as ethanol for difficult samples.'],
    ['title' => 'Records for GLP', 'text' => 'Built-in date and time on the print format and an RS 232 interface support GMP / GLP / ISO documentation.'],
];

$advantages = [
    ['title' => 'Fine density resolution', 'text' => 'It is the highest-resolution model of the CY-GT series, with a 0.0001 g readability.'],
    ['title' => 'Handles tricky samples', 'text' => 'A different liquid medium can be used for samples with air bubbles or complex shapes.'],
    ['title' => 'Protected measurement', 'text' => 'The complete windproof and dustproof cover helps keep the reading stable.'],
    ['title' => 'Reliable weighing cell', 'text' => 'The Mono-Tech sensor uses electromagnetic force compensation (EMFC).'],
    ['title' => 'Room to grow', 'text' => 'Optional powder-density and liquid-density accessories extend what you can measure.'],
];

$faqs = [
    ['question' => 'How does the electronic densimeter measure density?', 'answer' => 'It uses the Archimedes principle. You weigh the sample in air, then weigh it in water, and the instrument calculates and shows the density.'],
    ['question' => 'What resolution and capacity does the CY 224GT have?', 'answer' => 'The capacity is 220 g with a readability of 0.0001 g. Density is measured and displayed to 0.0001 g/cm³, and the repeatability is 0.0001 g/cm³.'],
    ['question' => 'What samples can I measure?', 'answer' => 'Solid and floating samples. Aczet lists powder density and liquid density as optional accessories. The series is recommended for rubber, plastic, wire and cable, composite and polymer materials, footwear, tires and hard alloys.'],
    ['question' => 'What is the minimum sample weight?', 'answer' => 'The minimum sample weight is 1 g for the CY 224GT.'],
    ['question' => 'What if my sample has air bubbles or an odd shape?', 'answer' => 'Use a different liquid medium, such as water or ethanol. Aczet says this helps with samples that have air bubbles or complex shapes.'],
    ['question' => 'How is it calibrated and how do I connect it?', 'answer' => 'The CY 224GT uses external calibration with a test weight, and it has an RS 232 interface. The Aczet product page also mentions optional USB with Windows direct communication.'],
    ['question' => 'Are there other models in the CY-GT series?', 'answer' => 'Yes. Aczet also lists the CY 323GT (320 g, 0.001 g), CY 613GT (600 g, 0.001 g), CY 612GT (600 g, 0.01 g) and CY 3102GT (3100 g, 0.01 g) for larger samples at lower resolution.'],
    ['question' => 'Does the temperature of the water matter?', 'answer' => 'Yes, temperature plays a significant role in density measurement. The CY-GT series offers water temperature compensation so the reading takes the actual water temperature into account.'],
];

$documents = [
    ['title' => 'CY-GT Series Electronic Densimeter Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG1AN1A9BWQ4T8ZH3NC6DG1.pdf'],
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
        ['model' => 'CY 224GT', 'heading' => 'CY 224GT Electronic Densimeter', 'batch' => 1, 'specs' => [
            'Model'                          => 'CY 224GT',
            'Weighing capacity (g)'          => '220',
            'Readability (g)'                => '0.0001',
            'Tare range, subtractive (g)'    => '-220',
            'Density resolution (g/cm³)'     => '0.0001',
            'Repeatability, density (g/cm³)' => '0.0001',
            'Measured density (g/cm³)'       => '0.0001',
            'Minimum sample weight (g)'      => '1',
            'Application modes'              => 'Solid and floating samples (powder density and liquid density optional)',
            'Weighing in air, pan size (mm)' => 'Ø 30',
            'Weighing in water, pan size (mm)' => 'Ø 60',
            'Response time (sec.)'           => '2 to 3',
            'Display'                        => 'Large backlit LCD',
            'Calibration and adjustment'     => 'External calibration',
            'Interface'                      => 'RS 232',
            'Operating temperature (°C)'     => '18 to 30',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance'                     => 'ISO / GLP compliance',
            'Balance size, W × D × H (mm)'   => '342.5 × 212 × 341 (chamber height: 228.5)',
            'Weight, approx. (kg)'           => '10',
        ]],
    ],
];
