<?php

/**
Aczet CG-SF Series Flameproof Table Top Scale. Sources (14 Nov 2026, aczet.com only): https://aczet.com/product/flame-proof-table-top-scale (specs from the website table), datasheet https://aczet.com/upload/product_documentation/Flame_Proof_Table_Top_Scale_CG-F_Series.pdf (attached, not read in detail).
Models: CG-3SF (3 kg, 0.1 g), CG-6SF (6 kg, 0.5 g), CG-10SF (10 kg, 0.5 g), CG-15SF (15 kg, 1 g), CG-30SF (30 kg, 2 g); pan 250 x 210 mm; same five as the paste.
Certification (Aczet text only): Aczet's page states these scales are intrinsically safe for Zone 1 & 2, Gas Group IIB, category Ex ia, temperature class T6 at 40 C ambient, IS/IEC 60079-11:2006 (the page also prints the number as 600079-11:20026), certified by CIMFR (spelled CMFIR on the page) Dhanbad, IP 66, cast alloy FLP enclosure. No certificate number is on the page.
Doubt: the page links the datasheet as CTG-F in one label but the file is CG-F.
Pictures: Aczet has one picture; extras are crops.
 */

$category = 'Flame Proof Balance';
$brand    = 'Aczet';
$group = 'CG-SF Series';
$type = 'Flameproof Table Top Scale';

$shortDescription = 'The Aczet CG-SF Series Flameproof Table Top Scale is a stainless steel scale with an intrinsically safe flameproof indicator, in five capacities from 3 kg to 30 kg. Aczet states Zone 1 and 2, Gas Group IIB use with IP 66 protection.';

$overview = '<p>The <strong>Aczet CG-SF Series</strong> is an intrinsically safe flameproof table top scale for weighing in hazardous areas. Five models are listed: CG-3SF (3 kg, 0.1 g), CG-6SF (6 kg, 0.5 g), CG-10SF (10 kg, 0.5 g), CG-15SF (15 kg, 1 g) and CG-30SF (30 kg, 2 g), all with a 250 × 210 mm pan.</p>'
    . '<p><strong>Hazardous-area design</strong></p><p>Aczet describes a cast alloy flameproof enclosure that is intrinsically safe for Zone 1 and 2 and Gas Group IIB, category ia with temperature class T6 at 40 °C ambient, and states IP 66 protection so the scale can be used in hazardous and industrial environments.</p>'
    . '<p><strong>Certification (Aczet statement)</strong></p><p>Aczet states that the scale is certified by CIMFR Dhanbad for use in Zone 1 and 2 for Gas Group IIB as per IS/IEC 60079-11:2006. Ask us for the certificate before installation.</p>'
    . '<p><strong>Display and use</strong></p><p>A bright red LED display stays readable in any light, and the enclosure has easy function keys. Aczet describes a stainless steel scale with a resolution of up to 15,000 counts. It runs on 230 V AC, 50 / 60 Hz.</p>'
    . '<p>The table shows ranges for the whole series; tell our team the capacity you need and we will confirm the model.</p>';

$features = [
    ['title' => 'Five capacities, 3 to 30 kg', 'text' => 'CG-3SF to CG-30SF with readability from 0.1 g to 2 g.'],
    ['title' => 'Intrinsically safe flameproof indicator', 'text' => 'Cast alloy enclosure for Zone 1 and 2, Gas Group IIB, as stated by Aczet.'],
    ['title' => 'IP 66 protection', 'text' => 'Aczet states IP 66 for use in hazardous and industrial environments.'],
    ['title' => 'Bright red LED display', 'text' => 'Readable under any lighting.'],
    ['title' => 'Stainless steel scale', 'text' => 'Compact stainless steel body with a 250 × 210 mm pan.'],
];

$advantages = [
    ['title' => 'Weigh safely near flammable gases', 'text' => 'Built for hazardous areas rather than a standard indoor scale.'],
    ['title' => 'Compact footprint', 'text' => 'Table top size for benches and filling points.'],
    ['title' => 'Precise at small loads', 'text' => '0.1 g readability on the 3 kg model.'],
    ['title' => 'Easy to read', 'text' => 'Large LED digits and simple function keys.'],
    ['title' => 'Choose the capacity', 'text' => 'Five models between 3 kg and 30 kg.'],
];

$faqs = [
    ['question' => 'Which models are in the CG-SF series?', 'answer' => 'CG-3SF, CG-6SF, CG-10SF, CG-15SF and CG-30SF.'],
    ['question' => 'What is the readability?', 'answer' => '0.1 g (3 kg), 0.5 g (6 kg and 10 kg), 1 g (15 kg) and 2 g (30 kg).'],
    ['question' => 'What hazardous-area rating does Aczet state?', 'answer' => 'Intrinsically safe, Zone 1 and 2, Gas Group IIB, category ia, temperature class T6 at 40 °C ambient, IS/IEC 60079-11:2006, certified by CIMFR Dhanbad according to Aczet.'],
    ['question' => 'What is the protection class?', 'answer' => 'Aczet states IP 66.'],
    ['question' => 'What is the pan size?', 'answer' => '250 × 210 mm on all five models.'],
    ['question' => 'What power does it need?', 'answer' => 'A/C 230 V, ±10 %, 50 / 60 Hz.'],
    ['question' => 'Is the datasheet available?', 'answer' => 'Yes, the Aczet datasheet is attached to this page.'],
];

$documents = [
    ['title' => 'CG-SF Series Flameproof Table Top Scale Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG3FP1A9BWQ4T8ZH3NC6DF3.pdf'],
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
        ['model' => 'CG-SF Flameproof Table Top Scale', 'heading' => 'CG-SF Series Flameproof Table Top Scale', 'batch' => 1, 'specs' => [
            'Model' => 'CG-3SF, CG-6SF, CG-10SF, CG-15SF, CG-30SF',
            'Maximum capacity (kg)' => '3 to 30 (CG-3SF: 3; CG-6SF: 6; CG-10SF: 10; CG-15SF: 15; CG-30SF: 30)',
            'Readability (g)' => '0.1 to 2 (CG-3SF: 0.1; CG-6SF: 0.5; CG-10SF: 0.5; CG-15SF: 1; CG-30SF: 2)',
            'Pan size (mm)' => '250 × 210',
            'Display' => 'Red LED',
            'Power supply' => 'A/C 230 V ± 10 %, 50 / 60 Hz',
            'Protection class' => 'IP 66 (as stated by Aczet)',
            'Hazardous-area certification (as stated by Aczet)' => 'Intrinsically safe, Zone 1 & 2, Gas Group IIB, Ex ia, temperature class T6 at 40 °C ambient, IS/IEC 60079-11:2006; CIMFR Dhanbad; IP 66; cast alloy FLP enclosure',
        ]],
    ],
];
