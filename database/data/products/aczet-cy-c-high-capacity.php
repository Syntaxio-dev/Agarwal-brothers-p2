<?php

/**
 * Aczet CY-C Series High Capacity Balance - ONE product for the whole range, specs as RANGES (specs_general = true). Prepared 14 Nov 2026, only aczet.com opened.
 * Sources: https://aczet.com/product/industrial-precision-balance-1 (Overview, Full Specs, accessories) and the Aczet datasheet Industrial_Precision_Balance-_Internal.pdf (headed CY-C Series, lists CY ...HC) = attached document. Spec values from the DATASHEET.
 * Models: datasheet lists CY 15001HC, CY 20001HC, CY 25001HC, CY 35001HC, CY 50000HC. The site table lists only 15001, 25001, 35001 and 50000 (no 20001); the pasted list matches the datasheet.
 * Differences datasheet vs site (datasheet used): minimum load 10 g (site 5 g for 15001HC); pan 370 x 260 mm (site 370 x 270 mm); site has no 20001 table row.
 * CALIBRATION DOUBT: the site overview says CY-HC = motorised internal calibration with PSAC and CY-H = external calibration, but BOTH datasheets and the site Full Specs rows print "External Calibration", and the "Internal" PDF is headed "CY-C Series Top Pan Precision Balances Standard Level". Here: internal calibration per the site overview text; owner to confirm with Aczet.
 * IP 54 and "10 built-in applications" are stated on the same product page for the high-capacity range (not per series); no OIML / 21 CFR / certificate claim is made for the balance (OIML appears only for the test-weight accessory).
 * Not confirmed: order codes beyond the model names. Nothing is claimed about Agarwal Brothers.
 */

return [
    'category' => 'High Capacity Balance',
    'brand' => 'Aczet',
    'model_group' => 'CY-C Series',
    'product_type' => 'High Capacity Precision Balance',
    'specs_general' => true,
    'short_description' => 'The Aczet CY-C Series High Capacity Precision Balances weigh from 15 kg to 50 kg with 0.1 g readability (1 g on the 50 kg model): CY 15001HC, CY 20001HC, CY 25001HC, CY 35001HC, CY 50000HC. A MonoTech weighing sensor, calibration options, a large stainless-steel pan of 370 x 260 mm, a backlit LCD and an RS 232C interface suit production, stores and quality-control weighing.',
    'overview' => '<p>The <strong>Aczet CY-C Series High Capacity Precision Balance</strong> range is made for weighing heavier samples and batches without giving up precision. Aczet describes its high-capacity precision balances as covering 15 kg to 50 kg with a readability of 0.1 g and 1 g.</p><p><strong>Five models, one body</strong></p><ul><li><p>CY 15001HC, CY 20001HC, CY 25001HC and CY 35001HC: 15, 20, 25 and 35 kg at 0.1 g readability, 0.1 g repeatability and 0.2 g linearity.</p></li><li><p>CY 50000HC: 50 kg at 1 g readability, 1 g repeatability and 2 g linearity.</p></li></ul><p><strong>Weighing technology</strong></p><p>The new MonoTech single-block weighing sensor has a uniform structure that gives stable temperature behaviour, a quick response and dependable corner-load performance. Motorised built-in weights provide internal calibration, and the Perfect Self Automatic Calibration (PSAC) function calibrates the balance automatically when time or temperature changes. An in-built 4-point protection system helps prevent overloading, and Aczet states that the balances follow the USP requirements of chapters 41 and 1251.</p><p><strong>Built for the work area</strong></p><p>A large grade 304 stainless-steel, non-magnetic pan of 370 x 260 mm carries the load, a protective cover is supplied, and Aczet states an IP 54 protection class against dust and splashes of water. The bright backlit LCD has large digits, and a hook allows weighing below the balance.</p><p><strong>Applications and records</strong></p><p>Built-in applications include piece counting, percentage weighing, animal or dynamic weighing, check weighing, totalising, formulation and automatic density determination. A built-in real-time clock supports GLP documentation, and an RS 232C interface is provided, with USB (Windows direct communication) as an option.</p><p>The table on this page shows ranges for the whole series. Tell our team the capacity you need, and we will confirm the right model and share its exact figures.</p>',
    'features' => [
        ['title' => '15 kg to 50 kg capacity', 'text' => 'Five models from CY 15001HC to CY 50000HC, reading 0.1 g (1 g on the 50 kg model).'],
        ['title' => 'MonoTech single-block sensor', 'text' => 'Stable temperature behaviour, fast response and reliable corner-load performance.'],
        ['title' => 'Calibration with built-in options', 'text' => 'Aczet describes motorised internal calibration with PSAC for the HC models; the datasheet prints external calibration, so confirm the version at order.'],
        ['title' => 'Large stainless-steel pan', 'text' => 'A 370 x 260 mm grade 304 non-magnetic pan, with 4-point overload protection.'],
        ['title' => 'Backlit LCD and RS 232C', 'text' => 'Large backlit digits, a real-time clock for GLP records and an RS 232C interface (USB optional).'],
    ],
    'advantages' => [
        ['title' => 'Heavy loads, fine readability', 'text' => 'Weigh up to 35 kg at 0.1 g, or 50 kg at 1 g.'],
        ['title' => 'Overload protection', 'text' => 'A 4-point system protects the sensor when a load that is too heavy is put on the pan.'],
        ['title' => 'Easy to level and clean', 'text' => 'A round bubble level, adjustable feet and a smooth housing make set-up and cleaning quick.'],
        ['title' => 'Weigh below the balance', 'text' => 'A below-balance hook is available for samples that cannot sit on the pan.'],
        ['title' => 'GLP-ready records', 'text' => 'The built-in clock and RS 232C output support time-stamped documentation.'],
    ],
    'faqs' => [
        ['question' => 'Which models are in the CY-C Series high-capacity range?', 'answer' => 'The Aczet datasheet lists CY 15001HC, CY 20001HC, CY 25001HC, CY 35001HC, CY 50000HC, with capacities of 15, 20, 25, 35 and 50 kg.'],
        ['question' => 'What are the readability, repeatability and linearity?', 'answer' => 'CY 15001, 20001, 25001 and 35001 read 0.1 g with 0.1 g repeatability and 0.2 g linearity. The 50 kg model reads 1 g with 1 g repeatability and 2 g linearity.'],
        ['question' => 'How is the balance calibrated?', 'answer' => 'Aczet describes motorised built-in weight internal calibration, plus PSAC automatic calibration when time or temperature changes. Note that the Aczet datasheet table prints the calibration line as external, so confirm with our team when ordering.'],
        ['question' => 'What is the pan size?', 'answer' => 'The pan is 370 x 260 mm (the Aczet product page prints 370 x 270 mm) in grade 304 stainless steel.'],
        ['question' => 'What is the typical minimum sample weight?', 'answer' => 'The datasheet gives a typical USP minimum sample weight of 82 g (820 g for the 50 kg model). The minimum load is 10 g on the 15 to 35 kg models.'],
        ['question' => 'How do I connect it to a PC or printer?', 'answer' => 'It has an RS 232C interface, with USB (Windows direct communication) as an option. Aczet also lists a dot-matrix data printer among its accessories.'],
        ['question' => 'Is it protected against dust and water?', 'answer' => 'Aczet states an IP 54 protection class on its high-capacity precision balance page, which protects against dust and splashes of water. It is not made for washing down.'],
        ['question' => 'How big and heavy is it?', 'answer' => 'The body is 517 x 302 x 130 mm and weighs about 16.3 kg.'],
    ],
    'documents' => [
        ['title' => 'CY-C Series High Capacity Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG4HC1A9BWQ4T8ZH3NC6DH1.pdf'],
    ],
    'models' => [
        ['model' => 'CY-C High Capacity Internal', 'heading' => 'CY-C High Capacity Balance', 'batch' => 1, 'specs' => [
            'Model' => 'CY 15001HC, CY 20001HC, CY 25001HC, CY 35001HC, CY 50000HC',
            'Maximum capacity (kg)' => '15 to 50 (CY 15001HC: 15; CY 20001HC: 20; CY 25001HC: 25; CY 35001HC: 35; CY 50000HC: 50)',
            'Minimum load (g)' => '10 (not stated for CY 50000HC)',
            'Readability (g)' => '0.1 (CY 50000HC: 1)',
            'Repeatability, sd (g)' => '0.1 (CY 50000HC: 1)',
            'Linearity (g)' => '± 0.2 (CY 50000HC: ± 2)',
            'Tare range, subtractive (kg)' => 'Up to -15, -20, -25, -35 and -50 (equal to the capacity of each model)',
            'USP minimum weight (g)' => '82 (typical; CY 50000HC: 820)',
            'Pan size (mm)' => '370 × 260',
            'Response time (sec.)' => '2 to 3',
            'Display' => 'Alphanumeric backlit LCD',
            'Calibration and adjustment' => 'Internal with PSAC per the Aczet overview; the datasheet prints external (confirm at order)',
            'Units' => 'gm, mg, ct, GN, mo, oz, dwt, t1T, t1H, t1S, mom, Bat, MS',
            'Operating temperature (°C)' => '15 to 35',
            'Sensitivity drift (ppm/°C)' => '± 2',
            'Interface' => 'RS 232C, USB optional (Windows direct communication)',
            'Application modes' => 'PCS, % weighing, animal / dynamic weighing, check weighing, automatic density determination',
            'Protection class' => 'IP 54',
            'Power supply' => 'DC adapter, input 100 to 240 V / 0.8 A, output 13 V / 1.5 A, 50 / 60 Hz',
            'Compliance' => 'ISO, GLP / GMP & USP',
            'Balance size, W × D × H (mm)' => '517 × 302 × 130',
            'Weight, approx. (kg)' => '16.3',
        ]],
    ],
];
