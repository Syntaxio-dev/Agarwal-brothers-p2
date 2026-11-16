<?php

/**
 * Aczet CM-F Filter Micro Balance - ONE product for the whole CM-F line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, nothing else was opened):
 *  - Aczet product page https://aczet.com/product/filter-micro-balance (Overview text, the four 'Full Specs' tables, accessory list)
 *  - Aczet technical data sheet https://aczet.com/upload/product_documentation/Filter_Micro_Balance6.pdf (1 page, rendered and read)
 * Spec values are taken from the product page 'Full Specs' tables.
 *
 * Differences from the pasted model list / between the two Aczet sources (owner to confirm):
 *  - The paste (CM 2F, CM 5F, CM 6F, CM 11F, about 2 to 11 g x 1 ug, 50 mm filter pan) matches the PRODUCT PAGE exactly.
 *  - The attached datasheet is older: it lists CM 2F, CM 5F and "CM 6.1F" (no CM 11F at all), and gives the pan as "15 to 30 mm" for the series
 *    (the page says 50 mm for all four models). The page is used.
 *  - Datasheet vs page values: repeatability of CM 2F is 0.7 ug (datasheet) vs 1 ug (page); linearity of CM 5F / CM 6F is 5 ug (datasheet) vs 6 ug (page);
 *    USP minimum weight is 2 mg for all three models (datasheet) vs 1 / 2 / 2.4 / 2.4 mg (page); weight 10.20 / 14.70 kg (datasheet) vs 10 / 14 kg (page).
 *  - The page lists the model as "CM 6F" (6.1 g); the datasheet calls it "CM 6.1F".
 *  - Page typos kept out: CM 2F repeatability / linearity are printed with a +/- sign, the others without; written here without the sign (sd and +/- values).
 * Not confirmed: the order codes / variants beyond these four model names; whether the optional touch-free draft shield and the CI-01 ionizer
 * are available in India (Aczet lists them as options). Nothing about Agarwal Brothers is claimed.
 */

$category = 'Micro Balance';
$brand    = 'Aczet';
$group    = 'CM-F Series';
$type     = 'Filter Micro Balance';

$shortDescription = 'The Aczet CM-F Series filter micro balances are made for weighing filters in particulate-matter and emission testing. Four models (CM 2F, CM 5F, CM 6F and CM 11F) cover 2 g to 11 g with 1 µg readability and a 50 mm filter pan under a cylindrical aluminium draft shield that limits electrostatic influence. Motorised internal calibration, a backlit LCD with ten built-in applications, RS 232C output (USB optional) and GLP-ready time stamping make it practical for environmental testing and research labs.';

$overview = '<p>The <strong>Aczet CM-F Filter Micro Balance</strong> is a microbalance range built for one job: weighing filters accurately. Aczet describes these balances as ideal for measuring particulate emissions, and they can monitor the particle classes <strong>PM10</strong> and <strong>PM2.5</strong>, particles smaller than 10 µm and 2.5 µm. They suit research and testing institutes in environmental technology, where air pollution from vehicles, power plants and other motorised equipment is monitored and controlled.</p>'
    . '<p><strong>Four models, 1 µg readability</strong></p>'
    . '<ul>'
    . '<li><p><strong>CM 2F:</strong> 2 g capacity.</p></li>'
    . '<li><p><strong>CM 5F:</strong> 5 g capacity.</p></li>'
    . '<li><p><strong>CM 6F:</strong> 6.1 g capacity.</p></li>'
    . '<li><p><strong>CM 11F:</strong> 11 g capacity.</p></li>'
    . '</ul>'
    . '<p>All four read to 1 µg (0.001 mg) and share a 50 mm diameter pan, a minimum load of 100 µg and a response time of 8 to 10 seconds.</p>'
    . '<p><strong>Designed for filter weighing</strong></p>'
    . '<ul>'
    . '<li><p>A specially designed weighing chamber with a cylindrical aluminium draft shield reduces the influence of electrostatic charges on the filter weight.</p></li>'
    . '<li><p>The stainless steel housing gives short stabilization times and keeps heat transfer from the electronics away from the weighing cell.</p></li>'
    . '<li><p>The draft shields can be dismantled and assembled easily, so cleaning is quick.</p></li>'
    . '<li><p>The balance has two parts, a mechanical measuring cell and a separate electronics box, so the electronics do not warm the measuring cell.</p></li>'
    . '</ul>'
    . '<p><strong>Proven weighing technology</strong></p>'
    . '<p>The sensor works on electromagnetic force compensation (EMFC). Motorised built-in weights calibrate the balance internally, and the Perfect Self Automatic Calibration (PSAC) function repeats the calibration when the temperature changes or a set time has passed. A capacity tracker on the display helps you avoid overloading the weighing cell.</p>'
    . '<p><strong>Operation and documentation</strong></p>'
    . '<p>A bright backlit LCD with large digits and ten built-in applications (including density determination, check weighing, totalling and formulation) simplify routine work. RS 232C output (USB optional) and a built-in real-time clock support error-free, traceable records for GLP and GMP. Options listed by Aczet include a touch-free infrared draft shield, a CI-01 anti-static ionizer, a dot-matrix data printer, 21 CFR Part 11 PC software, an anti-vibration table, test weights and a battery backup.</p>'
    . '<p>The table on this page shows ranges for the whole CM-F series. Tell our team the filter size and the weight range you work with, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => '1 µg readability, four capacities', 'text' => 'The CM 2F, CM 5F, CM 6F and CM 11F cover 2 g to 11 g, all with 1 µg (0.001 mg) readability.'],
    ['title' => 'Filter weighing chamber', 'text' => 'A 50 mm pan under a cylindrical aluminium draft shield limits electrostatic influence on filters, for PM10 and PM2.5 testing.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised built-in weights plus PSAC recalibrate the balance on a temperature change or after a set time.'],
    ['title' => 'Separate electronics and measuring cell', 'text' => 'The two-part design keeps heat from the electronics away from the mechanical cell, with a stainless steel housing for fast stabilization.'],
    ['title' => 'GLP-ready records', 'text' => 'Built-in real-time clock, RS 232C output (USB optional) and ten built-in applications support traceable, error-free documentation.'],
];

$advantages = [
    ['title' => 'Right size for the filter job', 'text' => 'Choose 2, 5, 6.1 or 11 g capacity in one series and keep the same 1 µg readability.'],
    ['title' => 'Low sensitivity drift', 'text' => 'Sensitivity drift is stated as ±1 ppm per °C, so results stay steady across the 18 to 30 °C operating range.'],
    ['title' => 'Easy to clean and level', 'text' => 'Draft shields come apart without tools and a level indicator with adjustable feet helps you set the balance up correctly.'],
    ['title' => 'Overload protection', 'text' => 'The built-in capacity tracker on the display shows how close you are to the limit and helps protect the weighing cell.'],
    ['title' => 'Room to add accessories', 'text' => 'Optional touch-free operation, an ionizer, a printer, an anti-vibration table and 21 CFR Part 11 software can be added when you need them.'],
];

$faqs = [
    ['question' => 'Which models and capacities are in the CM-F series?', 'answer' => 'Aczet lists four models: CM 2F (2 g), CM 5F (5 g), CM 6F (6.1 g) and CM 11F (11 g). All read to 1 µg (0.001 mg).'],
    ['question' => 'What is a filter micro balance used for?', 'answer' => 'It weighs filters before and after sampling to measure particulate emissions. Aczet states the balances can monitor PM10 and PM2.5 particles, which suits environmental testing of vehicle, power plant and other exhaust and evaporative emissions.'],
    ['question' => 'What pan size does it have?', 'answer' => 'Aczet\'s product page lists a 50 mm diameter pan for all four models. Please tell us the diameter of the filters you use so our team can confirm it fits.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with built-in weights. The Perfect Self Automatic Calibration (PSAC) function repeats the calibration automatically when the temperature changes or after a defined time.'],
    ['question' => 'What are the minimum weights?', 'answer' => 'The minimum load is 100 µg on every model. The minimum sample weight is 0.1 mg for the CM 2F, 0.2 mg for the CM 5F and 0.24 mg for the CM 6F and CM 11F. The minimum sample weight as per USP is 1 mg, 2 mg, 2.4 mg and 2.4 mg respectively.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. A dot-matrix data printer and 21 CFR Part 11 PC software are listed as accessories.'],
    ['question' => 'How are static charges on filters handled?', 'answer' => 'The weighing chamber has a cylindrical aluminium draft shield made to reduce the influence of electrostatic charges. Aczet also offers an optional CI-01 anti-static ionizer for extra protection.'],
    ['question' => 'What operating conditions does it need?', 'answer' => 'Aczet gives the operating temperature as 18 to 30 °C. Install it on a stable bench, and an anti-vibration weighing table is available as an accessory if the floor or bench vibrates.'],
];

$documents = [
    ['title' => 'CM-F Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXD2AC9F4MWQ7T3ZH8NB5DGA.pdf'],
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
        ['model' => 'CM-F Filter Micro Balance', 'heading' => 'CM-F Filter Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CM 2F, CM 5F, CM 6F, CM 11F',
            'Readability (mg)'               => '0.001 (1 µg)',
            'Weighing capacity (g)'          => '2 to 11 (CM 2F: 2, CM 5F: 5, CM 6F: 6.1, CM 11F: 11)',
            'Minimum load (µg)'              => '100',
            'Pan size (mm)'                  => 'Ø 50',
            'Repeatability (µg)'             => '1 to 1.2 (CM 11F: 1.2)',
            'Linearity (µg)'                 => '3 to 6 (CM 2F: 3, CM 5F / CM 6F / CM 11F: 6)',
            'Tare range (g)'                 => '-2 to -11 (equal to capacity)',
            'Minimum sample weight (mg)'     => '0.1 to 0.24 (CM 2F: 0.1, CM 5F: 0.2, CM 6F / CM 11F: 0.24)',
            'Minimum sample weight, as per USP (mg)' => '1 to 2.4 (CM 2F: 1, CM 5F: 2, CM 6F / CM 11F: 2.4)',
            'Response time (sec.)'           => '8 to 10',
            'Sensitivity drift (ppm/°C)'     => '± 1',
            'Calibration and adjustment'     => 'Motorised internal calibration',
            'Display'                        => 'Alphanumeric backlit LCD',
            'Operating temperature (°C)'     => '18 to 30',
            'Units'                          => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Application modes'              => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination, formulation, automatic statistic calculator',
            'Interface'                      => 'RS 232C, USB optional (Windows direct communication)',
            'Power supply'                   => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A',
            'Compliance'                     => 'ISO, GLP / GMP compliance',
            'Balance size, W × D × H (mm)'   => 'Mechanical box: 360 (L) × 140 (W) × 146 (H) / Electronic box: 240 (W) × 210 (D) × 65 (H)',
            'Weight, approx. (kg)'           => '10 / 14',
        ]],
    ],
];
