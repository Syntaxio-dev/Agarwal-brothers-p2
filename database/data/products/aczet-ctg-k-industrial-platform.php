<?php

/**
 * Aczet CTG-K / CTG-KH Series Industrial Precision Platform Scale - ONE product, specs as RANGES (specs_general = true). Prepared 14 Nov 2026, only aczet.com opened.
 * Sources: https://aczet.com/product/industrial-precision-balance (overview, Full Specs for all 8 models) and the datasheets Industrial_Precision_Platform_Scale_CTG_-_K_Series.pdf + Industrial_Precision_CTG_-_KH_Series1.pdf (merged into the attached document). All 8 pasted models exist on the site.
 * Differences: the K datasheet prints "CTG 500K5" with 10 g readability, the site says CTG 500K10; the site name (500K10) is used. Resolution: site overview says up to 1,50,000 counts, K datasheet 2,00,000, KH datasheet 1,50,000: no counts figure is published here.
 * The KH datasheet gives no interface line, so RS 232C is shown for CTG-K only. KH power "110,230V/ 10.5V, AC" is printed that way and kept as printed. No IP rating, OIML approval or certificate is claimed (none stated for this series).
 * Not confirmed: order codes beyond model names. Nothing is claimed about Agarwal Brothers.
 */

return [
    'category' => 'High Capacity Balance',
    'brand' => 'Aczet',
    'model_group' => 'CTG-K / KH Series',
    'product_type' => 'Industrial Precision Platform Scale',
    'specs_general' => true,
    'short_description' => 'The Aczet CTG-K and CTG-KH Series Industrial Precision Platform Scales weigh from 60 kg to 500 kg with 1 g to 10 g readability: CTG 60KH1, 100KH1, 100K1, 150K1, 100K2, 200K2, 200K5 and 500K10. A high-resolution load cell, a stainless-steel platter, a backlit LCD and counting and check-weighing functions suit stores, dispatch and production areas.',
    'overview' => '<p>The <strong>Aczet CTG-K / KH Series Industrial Precision Platform Scale</strong> brings precision weighing to heavier loads in industrial production areas. Aczet describes a weighing sensor with a high resolution and an environmentally protected load cell, in a compact, microprocessor-based design.</p><p><strong>Eight models in two groups</strong></p><ul><li><p>CTG-KH: CTG 60KH1 (60 kg) and CTG 100KH1 (100 kg), both reading 1 g, on a 320 x 360 mm platform with an aluminium die-casting base.</p></li><li><p>CTG-K: CTG 100K1 (100 kg) and CTG 150K1 (150 kg) at 1 g, CTG 100K2 (100 kg) and CTG 200K2 (200 kg) at 2 g, CTG 200K5 (200 kg) at 5 g and CTG 500K10 (500 kg) at 10 g, on a 400 x 500 mm stainless-steel platter.</p></li></ul><p><strong>Weighing and functions</strong></p><p>The scales respond in 2 to 3 seconds and have auto power-off to save energy. The CTG-K datasheet lists piece counting, unit conversion, zero tracking, an accumulation function and Hi / Low check weighing, with a safety overload of 150 percent of the capacity. Calibration is external, and a bubble level helps set the platform level.</p><p><strong>Build and connection</strong></p><p>The indicator housing is rugged ABS plastic. The CTG-K has a mild-steel structure with a stainless-steel weighing pan, a built-in rechargeable battery and an RS 232C interface; the CTG-KH has an aluminium die-casting platform and a stainless-steel platter.</p><p>The table on this page shows ranges for the whole series. Tell our team the capacity and readability you need, and we will confirm the right model and share its exact figures.</p>',
    'features' => [
        ['title' => '60 kg to 500 kg, 1 g to 10 g', 'text' => 'Eight models across the CTG-K and CTG-KH groups.'],
        ['title' => 'High-resolution load cell', 'text' => 'A single-point, environmentally protected load cell with high resolution.'],
        ['title' => 'Counting and check weighing', 'text' => 'Piece counting, unit conversion, accumulation and Hi / Low check weighing.'],
        ['title' => 'Stainless-steel platter', 'text' => 'A stainless-steel weighing platter on a rugged base, with a backlit LCD indicator.'],
        ['title' => 'Overload safety, auto power-off', 'text' => 'A safety overload of 150 percent of capacity and automatic power-off.'],
    ],
    'advantages' => [
        ['title' => 'Right size for the load', 'text' => 'Choose 60 kg to 500 kg and the readability that fits the job.'],
        ['title' => 'Compact and space-saving', 'text' => 'A small footprint for crowded stores and production areas.'],
        ['title' => 'Counting made simple', 'text' => 'Piece counting and unit conversion speed up stock and dispatch checks.'],
        ['title' => 'Quick weighing', 'text' => 'A 2 to 3 second response time keeps routine weighing moving.'],
        ['title' => 'Easy levelling', 'text' => 'A bubble level helps set the platform level before use.'],
    ],
    'faqs' => [
        ['question' => 'Which models are in the CTG-K / KH series?', 'answer' => 'The Aczet product page lists CTG 60KH1, 100KH1, 100K1, 150K1, 100K2, 200K2, 200K5 and 500K10.'],
        ['question' => 'What are the capacity and readability of each model?', 'answer' => 'CTG 60KH1: 60 kg, 1 g. CTG 100KH1 and 100K1: 100 kg, 1 g. CTG 150K1: 150 kg, 1 g. CTG 100K2: 100 kg, 2 g. CTG 200K2: 200 kg, 2 g. CTG 200K5: 200 kg, 5 g. CTG 500K10: 500 kg, 10 g.'],
        ['question' => 'What platform size do they have?', 'answer' => 'The CTG-K models have a 400 x 500 mm pan and the CTG-KH models a 320 x 360 mm pan.'],
        ['question' => 'How are the scales calibrated?', 'answer' => 'Aczet lists external calibration, using a test weight.'],
        ['question' => 'What functions are built in?', 'answer' => 'The CTG-K datasheet lists piece counting, unit conversion, zero tracking, an accumulation function and Hi / Low check weighing.'],
        ['question' => 'How is it powered and connected?', 'answer' => 'The CTG-K runs on AC 230 V, +/- 10 percent, 50 to 60 Hz, has a built-in rechargeable battery and an RS 232C interface. The CTG-KH is listed for 110 / 230 V AC (10.5 V as printed).'],
        ['question' => 'What happens if I overload it?', 'answer' => 'Aczet lists a safety overload of 150 percent of the scale capacity. Keep loads within the stated maximum capacity.'],
    ],
    'documents' => [
        ['title' => 'CTG-K and CTG-KH Series Datasheets', 'type' => 'Datasheet', 'file' => 'products/documents/01KXG4HC1A9BWQ4T8ZH3NC6DH3.pdf'],
    ],
    'models' => [
        ['model' => 'CTG-K / KH Industrial Platform', 'heading' => 'CTG-K / KH Series Industrial Platform Scale', 'batch' => 1, 'specs' => [
            'Model' => 'CTG 60KH1, CTG 100KH1, CTG 100K1, CTG 150K1, CTG 100K2, CTG 200K2, CTG 200K5, CTG 500K10',
            'Maximum capacity (kg)' => '60 to 500 (60KH1: 60; 100KH1: 100; 100K1: 100; 150K1: 150; 100K2: 100; 200K2: 200; 200K5: 200; 500K10: 500)',
            'Readability (g)' => '1 to 10 (60KH1, 100KH1, 100K1, 150K1: 1; 100K2, 200K2: 2; 200K5: 5; 500K10: 10)',
            'Repeatability, sd (g)' => '1 to 10 (same value as the readability)',
            'Linearity (g)' => '± 2 to ± 20 (60KH1, 100KH1, 100K1, 150K1: 2; 100K2, 200K2: 4; 200K5: 10; 500K10: 20)',
            'Tare range, subtractive (kg)' => 'Up to -60 to -500 (equal to the capacity of each model)',
            'Pan size (mm)' => 'CTG-K: 400 × 500; CTG-KH: 320 × 360',
            'Display' => 'Alphanumeric backlit LCD',
            'Response time (sec.)' => '2 to 3',
            'Interface' => 'CTG-K: RS 232C',
            'Application' => 'Counting and unit conversion',
            'Calibration and adjustment' => 'External calibration',
            'Power supply' => 'CTG-K: AC 230 V, ± 10%, 50 / 60 Hz; CTG-KH: 110, 230 V AC / 10.5 V (as printed)',
            'Weighing sensor' => 'High-precision single-point, environmentally protected load cell',
            'Overload safety' => '150% of the scale capacity',
        ]],
    ],
];
