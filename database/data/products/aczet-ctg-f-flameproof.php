<?php

/**
Aczet CTG-F Series Flameproof Platform Scale (MS structure). Sources (14 Nov 2026, aczet.com only): https://aczet.com/product/flame-proof-platform-scale (specs from the website table), datasheet https://aczet.com/upload/product_documentation/Flame_Proof_Scale_CTG-F_Series_(MS_Structure).pdf (attached, not read in detail).
Models: CTG-60F (60 kg, 5 g, 500 x 500 mm), CTG-150F (150 kg, 10 g, 600 x 600), CTG-200F (200 kg, 20 g), CTG-300F (300 kg, 20 g), CTG-500F (500 kg, 50 g); same capacities as the paste.
Certification (Aczet text only): Aczet's page states these scales are intrinsically safe for Zone 1 & 2, Gas Group IIB, category Ex ia, temperature class T6 at 40 C ambient, IS/IEC 60079-11:2006 (the page also prints the number as 600079-11:20026), certified by CIMFR (spelled CMFIR on the page) Dhanbad, IP 66, cast alloy FLP enclosure. No certificate number is on the page.
Aczet describes a Mild Steel platform with a rustproof SS platter.
Pictures: Aczet has one picture; extras are crops.
 */

$category = 'Flame Proof Balance';
$brand    = 'Aczet';
$group = 'CTG-F Series';
$type = 'Flameproof Platform Scale';

$shortDescription = 'The Aczet CTG-F Series Flameproof Platform Scale has a mild steel platform with a rustproof stainless steel platter and an intrinsically safe indicator, in five capacities from 60 kg to 500 kg. Aczet states Zone 1 and 2, Gas Group IIB use with IP 66 protection.';

$overview = '<p>The <strong>Aczet CTG-F Series</strong> is an intrinsically safe flameproof industrial platform scale for hazardous areas. Five models are listed: CTG-60F (60 kg, 5 g), CTG-150F (150 kg, 10 g), CTG-200F (200 kg, 20 g), CTG-300F (300 kg, 20 g) and CTG-500F (500 kg, 50 g). The CTG-60F has a 500 × 500 mm platform; the others have 600 × 600 mm.</p>'
    . '<p><strong>Hazardous-area design</strong></p><p>Aczet states that the cast alloy flameproof enclosure, intrinsically safe load cell, weight indicator and electronic circuit are safe for Zone 1 and 2 and Gas Group IIB, category Ex ia with temperature class T6 at 40 °C ambient, with IP 66 protection for use near explosive vapours, gases and fogs.</p>'
    . '<p><strong>Certification (Aczet statement)</strong></p><p>Aczet states the scale is certified by CIMFR Dhanbad as per IS/IEC 60079-11:2006. Ask us for the certificate before installation.</p>'
    . '<p><strong>Build and display</strong></p><p>The platform is mild steel with a rustproof stainless steel platter. A bright red LED display stays readable in any light and the enclosure has easy function keys. Supply is 230 V AC, 50 / 60 Hz.</p>'
    . '<p>The table shows ranges for the whole series; tell our team the capacity and platform size you need and we will confirm the model.</p>';

$features = [
    ['title' => 'Five capacities, 60 to 500 kg', 'text' => 'CTG-60F to CTG-500F with readability from 5 g to 50 g.'],
    ['title' => 'Intrinsically safe system', 'text' => 'Load cell, indicator and circuit stated by Aczet as Ex ia for Zone 1 and 2.'],
    ['title' => 'IP 66 protection', 'text' => 'Stated by Aczet for hazardous and industrial environments.'],
    ['title' => 'Mild steel platform, SS platter', 'text' => 'Robust structure with a rustproof platter.'],
    ['title' => 'Bright red LED display', 'text' => 'Readable in any lighting.'],
];

$advantages = [
    ['title' => 'Safer weighing near flammable vapours', 'text' => 'Built for hazardous areas, not a standard indoor scale.'],
    ['title' => 'Choose a platform size', 'text' => '500 × 500 mm or 600 × 600 mm.'],
    ['title' => 'Wide capacity range', 'text' => 'From bench loads of 60 kg up to 500 kg.'],
    ['title' => 'Easy to read', 'text' => 'Large LED digits and simple keys.'],
    ['title' => 'Rugged build', 'text' => 'Cast alloy enclosure and steel platform.'],
];

$faqs = [
    ['question' => 'Which models are in the CTG-F series?', 'answer' => 'CTG-60F, CTG-150F, CTG-200F, CTG-300F and CTG-500F.'],
    ['question' => 'What are the readabilities?', 'answer' => '5 g (60 kg), 10 g (150 kg), 20 g (200 and 300 kg) and 50 g (500 kg).'],
    ['question' => 'What are the platform sizes?', 'answer' => '500 × 500 mm for CTG-60F and 600 × 600 mm for the others.'],
    ['question' => 'What hazardous-area rating does Aczet state?', 'answer' => 'Intrinsically safe, Zone 1 and 2, Gas Group IIB, Ex ia, T6 at 40 °C ambient, IS/IEC 60079-11:2006, certified by CIMFR Dhanbad according to Aczet.'],
    ['question' => 'What is the protection class?', 'answer' => 'Aczet states IP 66.'],
    ['question' => 'What power does it need?', 'answer' => 'A/C 230 V, ±10 %, 50 / 60 Hz.'],
    ['question' => 'Is a datasheet available?', 'answer' => 'Yes, the Aczet datasheet is attached to this page.'],
];

$documents = [
    ['title' => 'CTG-F Series Flameproof Platform Scale Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG3FP1A9BWQ4T8ZH3NC6DF4.pdf'],
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
        ['model' => 'CTG-F Flameproof Platform Scale', 'heading' => 'CTG-F Series Flameproof Platform Scale', 'batch' => 1, 'specs' => [
            'Model' => 'CTG-60F, CTG-150F, CTG-200F, CTG-300F, CTG-500F',
            'Maximum capacity (kg)' => '60 to 500 (CTG-60F: 60; CTG-150F: 150; CTG-200F: 200; CTG-300F: 300; CTG-500F: 500)',
            'Readability (g)' => '5 to 50 (CTG-60F: 5; CTG-150F: 10; CTG-200F: 20; CTG-300F: 20; CTG-500F: 50)',
            'Platform size (mm)' => '500 × 500 (CTG-60F); 600 × 600 (CTG-150F, 200F, 300F, 500F)',
            'Display' => 'Bright red LED',
            'Power supply' => 'A/C 230 V ± 10 %, 50 / 60 Hz',
            'Structure' => 'Mild steel platform with rustproof stainless steel platter',
            'Protection class' => 'IP 66 (as stated by Aczet)',
            'Hazardous-area certification (as stated by Aczet)' => 'Intrinsically safe, Zone 1 & 2, Gas Group IIB, Ex ia, temperature class T6 at 40 °C ambient, IS/IEC 60079-11:2006; CIMFR Dhanbad; IP 66; cast alloy FLP enclosure',
        ]],
    ],
];
