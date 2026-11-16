<?php

/**
 * Aczet CM-P Pipette Micro Balance - ONE product for the whole CM-P line, specs as RANGES (specs_general = true).
 *
 * Sources (read 14 Nov 2026, nothing else was opened):
 *  - Aczet product page https://aczet.com/product/pipette-micro-balance (Overview text, the 'Full Specs' table, accessory list)
 *  - Aczet technical data sheet https://aczet.com/upload/product_documentation/Pipette_Micro_Balance10.pdf (1 page, rendered and read)
 *
 * Differences from the pasted model list / between the two Aczet sources (owner to confirm):
 *  - The paste says CM 11P (11 g) and CM 21P (21 g), both 1 ug. The PRODUCT PAGE has a spec table for CM 11P ONLY; its overview says
 *    "a wide range of capacities from 11 g up to 21 g" but no CM 21P table or listing exists on the page. CM 21P comes from the DATASHEET
 *    (21 g, readability 1 ug, repeatability 1.2 ug, linearity 7 ug, tare -21 g, USP minimum weight 2.4 mg). So CM 21P is included on the datasheet's
 *    authority: ask the owner / Aczet whether CM 21P is currently sold.
 *  - Pan size: the page gives 20 mm diameter for CM 11P, the datasheet gives "15 to 30 mm" for the series. Written as 20 mm (page, CM 11P) with the
 *    datasheet range noted; the CM 21P pan is not listed on its own.
 *  - Values the page gives only for CM 11P (minimum load 100 ug, minimum sample weight 0.24 mg) are labelled as CM 11P. Weight: 12 / 14 kg (page) vs
 *    10.20 / 14.70 kg (datasheet): the page value is used.
 *  - The page's table header says "CM-P Series", the datasheet title says "CM Series Pipette Micro Balances".
 * Not confirmed: the ISO 8655 wording is Aczet's ("can comply with ISO 8655 or any other specification based on the gravimetric method"); no
 * certificate is claimed here. The datasheet photo shows a third-party pipette brand, so no picture from it was used. Nothing about Agarwal Brothers is claimed.
 */

$category = 'Micro Balance';
$brand    = 'Aczet';
$group    = 'CM-P Series';
$type     = 'Pipette Micro Balance';

$shortDescription = 'The Aczet CM-P Series pipette micro balances are made for gravimetric pipette calibration and liquid weighing. The CM 11P (11 g) and CM 21P (21 g) read to 1 µg and are suited to testing the volume of pipettes of any size by the gravimetric method, including tests based on ISO 8655. A cylindrical glass weighing chamber reduces electrostatic influence, an optional evaporation trap limits evaporation of the test liquid, and motorised internal calibration, RS 232C output and a built-in clock support GLP records.';

$overview = '<p>The <strong>Aczet CM-P Pipette Micro Balance</strong> is a user-friendly microbalance for <strong>pipette calibration</strong>. Aczet states that it gives accurate gravimetric testing for any pipette size, and that it can be used for ISO 8655 or any other test based on the gravimetric method: you dispense a liquid, weigh it to 1 µg and convert the mass into volume. It suits calibration laboratories, quality departments and research labs that check their own pipettes.</p>'
    . '<p><strong>Two models, 1 µg readability</strong></p>'
    . '<ul>'
    . '<li><p><strong>CM 11P:</strong> 11 g capacity, 1 µg readability, repeatability 1.2 µg, linearity 6 µg.</p></li>'
    . '<li><p><strong>CM 21P:</strong> 21 g capacity, 1 µg readability, repeatability 1.2 µg, linearity 7 µg.</p></li>'
    . '</ul>'
    . '<p><strong>Made for liquid weighing</strong></p>'
    . '<ul>'
    . '<li><p>An aesthetic, cylindrical weighing chamber made of glass minimises the influence of electrostatic charges on the result.</p></li>'
    . '<li><p>The optional CET 01 evaporation trap stabilises the humidity inside the weighing chamber and so limits evaporation of the test liquid, which would otherwise cause measurement errors. You no longer need to raise the humidity of the whole room.</p></li>'
    . '<li><p>The draft shields can be dismantled and assembled easily, which keeps the balance clean.</p></li>'
    . '<li><p>The balance has two parts, a mechanical measuring cell and a separate electronics box, so the electronics do not influence the measuring cell.</p></li>'
    . '</ul>'
    . '<p><strong>Proven weighing technology</strong></p>'
    . '<p>The sensor works on electromagnetic force compensation (EMFC). Motorised built-in weights calibrate the balance internally, and the Perfect Self Automatic Calibration (PSAC) function repeats the calibration when the temperature changes or a set time has passed. A capacity tracker on the display helps you avoid overloading the weighing cell.</p>'
    . '<p><strong>Operation and documentation</strong></p>'
    . '<p>A bright backlit LCD with large digits and ten built-in applications (including density determination, check weighing, totalling and formulation) simplify routine work. RS 232C output (USB optional) and a built-in real-time clock give traceable records for GLP and GMP. Optional accessories listed by Aczet include an anti-static ionizer, a dot-matrix data printer, 21 CFR Part 11 PC software, an anti-vibration table or pad, test weights, a battery backup and qualification documents (IQ, OQ, PQ).</p>'
    . '<p>The table on this page shows ranges for the whole CM-P series. Tell our team the pipette volumes you test, and we will confirm the right model and share the exact figures for it.</p>';

$features = [
    ['title' => '1 µg readability for pipette testing', 'text' => 'The CM 11P (11 g) and CM 21P (21 g) read to 1 µg, so even the smallest pipette volumes can be weighed gravimetrically.'],
    ['title' => 'Glass weighing chamber', 'text' => 'A cylindrical glass chamber reduces electrostatic influence, and the draft shields come apart easily for cleaning.'],
    ['title' => 'Optional evaporation trap', 'text' => 'The CET 01 trap steadies the humidity in the chamber and limits evaporation of the test liquid, so room humidity need not be raised.'],
    ['title' => 'Automatic internal calibration', 'text' => 'Motorised built-in weights plus PSAC recalibrate the balance when the temperature changes or after a set time.'],
    ['title' => 'GLP-ready records', 'text' => 'Built-in real-time clock, RS 232C output (USB optional) and ten built-in applications support traceable, error-free documentation.'],
];

$advantages = [
    ['title' => 'Covers every pipette size', 'text' => 'Aczet states the balance suits gravimetric testing for any pipette size, up to the 21 g capacity of the larger model.'],
    ['title' => 'Stable weighing conditions', 'text' => 'The glass chamber and optional evaporation trap help keep static and evaporation from shifting your readings.'],
    ['title' => 'Low sensitivity drift', 'text' => 'Sensitivity drift is stated as ±1 ppm per °C within the 18 to 30 °C operating range.'],
    ['title' => 'Overload protection', 'text' => 'The built-in capacity tracker shows how close you are to the limit, which helps protect the weighing cell.'],
    ['title' => 'Qualification support', 'text' => 'IQ, OQ and PQ qualification documents and 21 CFR Part 11 PC software are listed as accessories for regulated labs.'],
];

$faqs = [
    ['question' => 'What is a pipette micro balance used for?', 'answer' => 'It is used for gravimetric pipette calibration: the pipette dispenses a liquid onto the balance, the mass is measured to 1 µg and converted into volume to check the pipette\'s accuracy.'],
    ['question' => 'Which models are in the CM-P series?', 'answer' => 'The CM 11P weighs up to 11 g and the CM 21P up to 21 g. Both read to 1 µg (0.001 mg). Aczet\'s datasheet lists both models; please confirm the current availability of each with our team.'],
    ['question' => 'Can it be used for ISO 8655 testing?', 'answer' => 'Aczet states the balance can comply with ISO 8655 or any other specification based on the gravimetric method. The standard\'s requirements depend on your pipette volumes, so ask us about the right model for your tests.'],
    ['question' => 'Why is an evaporation trap offered?', 'answer' => 'When small volumes of liquid evaporate during weighing, the result is too low. The optional CET 01 evaporation trap stabilises the humidity inside the weighing chamber, which limits the evaporation of the test liquid.'],
    ['question' => 'What pan size does it have?', 'answer' => 'Aczet\'s product page lists a 20 mm diameter pan for the CM 11P, and its datasheet gives a 15 to 30 mm range for the series. Our team can confirm the pan of the model you choose.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It has motorised internal calibration with built-in weights. The Perfect Self Automatic Calibration (PSAC) function repeats the calibration automatically when the temperature changes or after a defined time.'],
    ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'The balance has an RS 232C interface, with USB (Windows direct communication) as an option. A dot-matrix data printer and 21 CFR Part 11 PC software are listed as accessories.'],
    ['question' => 'What operating conditions does it need?', 'answer' => 'Aczet gives the operating temperature as 18 to 30 °C. Install it on a stable bench; an anti-vibration weighing table or pad is available as an accessory if the bench vibrates.'],
];

$documents = [
    ['title' => 'CM-P Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXD2AC9F4MWQ7T3ZH8NB5DGB.pdf'],
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
        ['model' => 'CM-P Pipette Micro Balance', 'heading' => 'CM-P Pipette Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'CM 11P, CM 21P',
            'Readability (mg)'               => '0.001 (1 µg)',
            'Weighing capacity (g)'          => '11 to 21 (CM 11P: 11, CM 21P: 21)',
            'Minimum load (µg)'              => '100 (CM 11P)',
            'Pan size (mm)'                  => 'Ø 20 (CM 11P; datasheet gives Ø 15 to 30 for the series)',
            'Repeatability (µg)'             => '1.2',
            'Linearity (µg)'                 => '6 to 7 (CM 11P: 6, CM 21P: 7)',
            'Tare range (g)'                 => '-11 to -21 (equal to capacity)',
            'Minimum sample weight (mg)'     => '0.24 (CM 11P)',
            'Minimum sample weight, as per USP (mg)' => '2.4',
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
            'Weight, approx. (kg)'           => '12 / 14',
        ]],
    ],
];
