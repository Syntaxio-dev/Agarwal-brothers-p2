<?php

/**
Aczet CTG-T4LF Series Four Load Cell Flameproof Floor Scale (MS structure). Sources (14 Nov 2026, aczet.com only): https://aczet.com/product/flame-proof-four-load-cell-scale (specs from the website table), datasheet https://aczet.com/upload/product_documentation/Four_Load_Cell_Flame_Proof_Scale_CTG-F_Series_(MS_Structure).pdf (attached, not read in detail).
Models: as PRINTED by Aczet: CTG-1T4LF (1 t, 200 g, 1000 x 1000 mm), CTG-2T4LF (2 t, 500 g, 1200 x 1200), 'CTG-3T4FL' (3 t, 500 g, 1200 x 1200), 'CTG-3T4LF 5' (3 t, 1 kg, 1500 x 1500), 'CTG-5T4FL' (5 t, 1 kg, 1500 x 1500). The paste gave only the series name.
Certification (Aczet text only): Aczet's page states these scales are intrinsically safe for Zone 1 & 2, Gas Group IIB, category Ex ia, temperature class T6 at 40 C ambient, IS/IEC 60079-11:2006 (the page also prints the number as 600079-11:20026), certified by CIMFR (spelled CMFIR on the page) Dhanbad, IP 66, cast alloy FLP enclosure. No certificate number is on the page.
Doubt: Aczet's codes are inconsistent (T4FL vs T4LF, a stray '5'); kept exactly, owner/Aczet to confirm the real codes. Page also says 150 % overload construction, IP68 / IP67 shear-beam load cells, 5 m cable, optional 4-20 mA.
Pictures: Aczet has one picture; extras are crops.
 */

$category = 'Flame Proof Balance';
$brand    = 'Aczet';
$group = 'CTG-T4LF Series';
$type = 'Four Load Cell Flameproof Floor Scale';

$shortDescription = 'The Aczet CTG-T4LF Series is a four load cell flameproof floor scale with a mild steel platform and an intrinsically safe indicator, from 1 ton to 5 ton. Aczet states Zone 1 and 2, Gas Group IIB use with IP 66 protection.';

$overview = '<p>The <strong>Aczet CTG-T4LF Series</strong> is a four load cell flameproof floor scale for heavy loads in hazardous areas. Aczet lists capacities of 1 ton (200 g readability, 1000 × 1000 mm), 2 ton (500 g, 1200 × 1200 mm), 3 ton (500 g on 1200 × 1200 mm or 1 kg on 1500 × 1500 mm) and 5 ton (1 kg, 1500 × 1500 mm).</p>'
    . '<p><strong>Hazardous-area design</strong></p><p>Aczet states that the cast alloy flameproof enclosure, intrinsically safe load cells, weight indicator and circuit are safe for Zone 1 and 2 and Gas Group IIB, category Ex ia with temperature class T6 at 40 °C ambient, with IP 66 protection for use near explosive vapours, gases and fogs.</p>'
    . '<p><strong>Certification (Aczet statement)</strong></p><p>Aczet states the scale is certified by CIMFR Dhanbad as per IS/IEC 60079-11:2006. Please ask us for the certificate before installation.</p>'
    . '<p><strong>Platform and load cells</strong></p><p>The mild steel platter has a low profile and is built for an overload of 150 percent of the full capacity. The shear beam load cells are IP68 and IP67 protected, with integrated overload protection, and a 5 m single cable with a conduit pipe is provided. A 4 to 20 mA output is optional.</p>'
    . '<p><strong>Display</strong></p><p>A bright red 7-segment LED display and water-resistant push keys make the indicator easy to use. Supply is 230 V AC, 50 / 60 Hz.</p>'
    . '<p>The table shows the range for the whole series; tell our team the capacity and platform size and we will confirm the model.</p>';

$features = [
    ['title' => '1 ton to 5 ton capacities', 'text' => 'Four load cell floor scales with readability from 200 g to 1 kg.'],
    ['title' => 'Intrinsically safe system', 'text' => 'Load cells, indicator and circuit stated by Aczet as Ex ia for Zone 1 and 2.'],
    ['title' => 'IP68 / IP67 load cells', 'text' => 'Sealed shear beam load cells with integrated overload protection.'],
    ['title' => '150 percent overload construction', 'text' => 'Rigid mild steel low-profile platter.'],
    ['title' => 'Bright 7-segment LED display', 'text' => 'Water-resistant push keys, optional 4 to 20 mA output.'],
];

$advantages = [
    ['title' => 'Heavy loads in hazardous areas', 'text' => 'Floor scale for drums, pallets and containers near flammable vapours.'],
    ['title' => 'Choose a platform size', 'text' => '1000 × 1000, 1200 × 1200 or 1500 × 1500 mm.'],
    ['title' => 'Handles wet, dirty sites', 'text' => 'Sealed load cells and an IP 66 indicator, as stated by Aczet.'],
    ['title' => 'Overload margin', 'text' => 'Platform built for 150 percent of capacity.'],
    ['title' => 'PLC-ready option', 'text' => 'Optional 4 to 20 mA output.'],
];

$faqs = [
    ['question' => 'Which models are listed?', 'answer' => 'Aczet lists CTG-1T4LF, CTG-2T4LF, CTG-3T4FL, CTG-3T4LF 5 and CTG-5T4FL. The names are printed inconsistently on the Aczet page, so we confirm the exact code when you enquire.'],
    ['question' => 'What are the capacities and readabilities?', 'answer' => '1 ton at 200 g, 2 ton at 500 g, 3 ton at 500 g (1200 × 1200 mm) or 1 kg (1500 × 1500 mm), and 5 ton at 1 kg.'],
    ['question' => 'What hazardous-area rating does Aczet state?', 'answer' => 'Intrinsically safe, Zone 1 and 2, Gas Group IIB, Ex ia, T6 at 40 °C ambient, IS/IEC 60079-11:2006, certified by CIMFR Dhanbad according to Aczet.'],
    ['question' => 'What load cells are used?', 'answer' => 'Shear beam load cells with IP68 and IP67 protection and a 5 m cable with a conduit pipe.'],
    ['question' => 'Is there an analogue output?', 'answer' => 'A 4 to 20 mA output is available as an option, according to Aczet.'],
    ['question' => 'What overload is the platform built for?', 'answer' => 'Aczet states 150 percent of the full capacity.'],
    ['question' => 'Is a datasheet available?', 'answer' => 'Yes, the Aczet datasheet is attached to this page.'],
];

$documents = [
    ['title' => 'CTG-T4LF Series Four Load Cell Flameproof Scale Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG3FP1A9BWQ4T8ZH3NC6DF5.pdf'],
];

return [
    'category' => $category,
    'brand' => $brand,
    'model_group' => $group,
    'product_type' => $type,
    'specs_general' => true,
    'short_description' => $shortDescription,
    'overview' => $overview,
    'features' => $features,
    'advantages' => $advantages,
    'faqs' => $faqs,
    'documents' => $documents,

    'models' => [
        ['model' => 'CTG-T4LF Flameproof Floor Scale', 'heading' => 'CTG-T4LF Series Four Load Cell Flameproof Floor Scale', 'batch' => 1, 'specs' => [
            'Model' => 'CTG-1T4LF, CTG-2T4LF, CTG-3T4FL, CTG-3T4LF 5, CTG-5T4FL (as printed by Aczet)',
            'Maximum capacity' => '1 to 5 ton (CTG-1T4LF: 1 ton; CTG-2T4LF: 2 ton; CTG-3T4FL: 3 ton; CTG-3T4LF 5: 3 ton; CTG-5T4FL: 5 ton)',
            'Readability' => '200 g to 1 kg (CTG-1T4LF: 200 g; CTG-2T4LF: 500 g; CTG-3T4FL: 500 g; CTG-3T4LF 5: 1 kg; CTG-5T4FL: 1 kg)',
            'Platform size (mm)' => '1000 × 1000 (1 ton); 1200 × 1200 (2 ton, CTG-3T4FL); 1500 × 1500 (CTG-3T4LF 5, 5 ton)',
            'Display' => 'Red LED, 7 segment',
            'Power supply' => 'A/C 230 V ± 10 %, 50 / 60 Hz',
            'Load cells' => 'Four shear beam load cells, IP68 / IP67 (as stated by Aczet)',
            'Protection class' => 'IP 66 (as stated by Aczet)',
            'Hazardous-area certification (as stated by Aczet)' => 'Intrinsically safe, Zone 1 & 2, Gas Group IIB, Ex ia, temperature class T6 at 40 °C ambient, IS/IEC 60079-11:2006; CIMFR Dhanbad; IP 66; cast alloy FLP enclosure',
        ]],
    ],
];
