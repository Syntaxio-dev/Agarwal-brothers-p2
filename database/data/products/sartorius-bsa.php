<?php

/**
 * Sartorius BSA Series — all 28 models from the brochure.
 *
 * batch 1 = Analytical (4 models), batch 2 = Precision (24 models).
 * BSA224S is the owner's example (product id 36) and is SKIPPED by the loader.
 *
 * Shared fields (short_description, overview, features, advantages, faqs, documents)
 * are the same for the whole series (cleaned from the example product).
 */

$category = 'General Laboratory Weighing [BSA Series]';
$brand    = 'Sartorius';
$group    = 'BSA Series';
$type     = 'Standard Laboratory Balance';

// --- Shared texts (cleaned from the owner's example product id 36) ---

$shortDescription = 'The new Sartorius BSA is a truly versatile balance with all-around capabilities. It features a winning combination of Sartorius\' legendary technology, an abundant package of features and options, and an operating design that enables especially efficient operation. BSA is a lab balance you can rely on, one that will work perfectly over the years, delivering accurate results day in and day out.';

$overview = '<p>The <strong>Sartorius BSA and BSA II series</strong> balances are standard analytical and precision laboratory balances designed for everyday lab work, quality control, and academic testing. Positioned as mid-tier workhorses, they offer premium German engineering and monolithic weigh cell technology at an accessible price point.</p><p><strong>Core Specifications</strong></p><ul><li><p><strong>Analytical Models:</strong> Readability of <strong>0.1 mg</strong> (0.0001 g), capacity up to 220 g, and a fully enclosed glass draft shield.</p></li><li><p><strong>Precision Models:</strong> Readability from <strong>1 mg to 0.1 g</strong>, capacity up to 6,200 g, and a flat open pan design.</p></li><li><p><strong>Stabilization Time:</strong> Typically delivers stable results in <strong>under 2.5 seconds</strong>.</p></li></ul><p><strong>Primary Advantages</strong></p><ul><li><p><strong>Monolithic Sensor:</strong> The single-block weigh cell replaces multi-part link systems. This reduces measurement drift, minimizes thermal expansion errors, and protects against overload damage.</p></li><li><p><strong>Internal Calibration:</strong> Models ending in <strong>-CW</strong> feature motorized internal test weights. Users can perform automatic daily calibrations with a single keypress to meet quality standards.</p></li><li><p><strong>Durable Build:</strong> The upper housing is constructed from chemical-resistant PBT plastic, allowing it to withstand exposure to harsh solvents like acetone.</p></li><li><p><strong>Built-in Functions:</strong> Includes onboard firmware routines for piece counting, percentage calculations, density determination, and dynamic animal weighing.</p></li><li><p><strong>Data Compliance:</strong> Generates ISO/GLP-compliant printouts of calibration logs and raw data when connected to an external printer.</p></li></ul><p><strong>Key Limitations</strong></p><ul><li><p><strong>Legacy Port:</strong> Uses a 25-pin RS-232 interface, which requires a specific converter or adapter cable to connect directly to modern USB-C PC setups.</p></li><li><p><strong>Basic Interface:</strong> The text-based configuration menu and 14-segment backlit screen are functional but lack modern touchscreen navigation.</p></li></ul>';

$features = [
    ['title' => 'Fast Stabilization Times', 'text' => 'Delivers rapid, stable readouts (typically within 1.5 to 2.5 seconds depending on the specific model) to keep high-throughput lab tasks moving efficiently.'],
    ['title' => 'ISO/GLP Compliant Printouts', 'text' => 'When connected to a compatible Sartorius printer, it automatically generates compliant printouts of calibration data, timestamps, and measurements for regulatory auditing.'],
    ['title' => 'Monolithic Weighing System', 'text' => 'Features a unique single-block cell design that replaces dozens of individual parts. This drastically reduces thermal expansion errors and increases mechanical durability.'],
    ['title' => 'Optimized Draft Shield', 'text' => 'Analytical models feature an ergonomic glass draft shield with smooth-sliding doors, ensuring minimal air-current interference while remaining easy to disassemble for thorough cleaning.'],
    ['title' => 'Overload Protection', 'text' => 'Equipped with built-in overload protection mechanisms that safeguard the internal monolithic cell from accidental downward force or dropping heavy samples onto the pan.'],
];

$advantages = [
    ['title' => 'Monolithic Weigh Cell', 'text' => 'Proprietary single-block sensor technology ensures long-term mechanical stability, fast response times, and high measurement repeatability.'],
    ['title' => 'Automatic Internal Calibration', 'text' => 'Models equipped with motorized internal weights (-CW or I-1x codes) allow one-touch calibration via the CAL key to maintain accuracy without manual weights.'],
    ['title' => 'High Readability & Wide Range', 'text' => 'Delivers analytical precision down to 0.1 mg (0.0001 g) alongside broad precision weighing capacities ranging up to 6,200 g.'],
];

$faqs = [
    ['question' => 'How long does the balance need to warm up?', 'answer' => 'To achieve accurate, repeatable results, the balance must remain plugged in and allowed to warm up for at least 30 minutes before you begin operation. This allows the internal components to reach optimal operating temperature.'],
    ['question' => 'How do I properly level the balance?', 'answer' => 'You must relevel the instrument every time you relocate it. Adjust only the two front feet until the air bubble is perfectly centered inside the level indicator circle. If your model has a rectangular pan, retract the rear feet first, level with the front feet, and then extend the rear feet until they touch the surface for stability.'],
    ['question' => 'How often should I calibrate my BSA balance?', 'answer' => 'For routine daily use, it is best practice to calibrate the balance at least once a day. You should also recalibrate immediately if the room temperature shifts significantly or if the balance is moved to a new station.'],
    ['question' => 'What is the difference between internal and external calibration models?', 'answer' => 'Models ending with -CW or featuring code I-1x include motorized, internal calibration weights. You can calibrate these automatically by pressing the CAL key. Standard models do not have internal weights and require manual calibration using certified, external test weights.'],
];

$documents = [
    ['title' => 'BSA Brochure', 'type' => 'Brochure', 'file' => 'products/documents/01M5EMD96QWBCYTSFZ5WYC1RQJ.pdf'],
];

// --- Models: batch 1 = Analytical, batch 2 = Precision ---
// Spec values are from the brochure page 6, verified against the rendered page image.
// Known oddities (reported to the owner):
//   - BSA124S repeatability printed as "0.,0001" in the PDF — loaded as "0.0001" (obvious typo).
//   - BSA2201-CW / BSA5201-CW / BSA8201-CW linearity = 0.1 while non-CW = 0.3 (loaded as printed).

return [
    'category'          => $category,
    'brand'             => $brand,
    'model_group'       => $group,
    'product_type'      => $type,
    'short_description' => $shortDescription,
    'overview'          => $overview,
    'features'          => $features,
    'advantages'        => $advantages,
    'faqs'              => $faqs,
    'documents'         => $documents,

    'models' => [
        // ── Batch 1: Analytical balances ──────────────────────────────

        ['model' => 'BSA224S', 'batch' => 1, 'specs' => [
            'Model' => 'BSA224S',
            'Readability (g)' => '0.0001',
            'Weighing capacity (g)' => '220',
            'Pan size (mm)' => '∅ 90',
            'Repeatability (g)' => '0.0001',
            'Linearity (g)' => '0.0002',
            'Response time (avg., sec.)' => '2.5',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA224S-CW', 'batch' => 1, 'specs' => [
            'Model' => 'BSA224S-CW',
            'Readability (g)' => '0.0001',
            'Weighing capacity (g)' => '220',
            'Pan size (mm)' => '∅ 90',
            'Repeatability (g)' => '0.0001',
            'Linearity (g)' => '0.0002',
            'Response time (avg., sec.)' => '2.5',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA124S', 'batch' => 1, 'specs' => [
            'Model' => 'BSA124S',
            'Readability (g)' => '0.0001',
            'Weighing capacity (g)' => '120',
            'Pan size (mm)' => '∅ 90',
            'Repeatability (g)' => '0.0001',  // brochure prints "0.,0001" — obvious typo
            'Linearity (g)' => '0.0002',
            'Response time (avg., sec.)' => '2.5',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA124S-CW', 'batch' => 1, 'specs' => [
            'Model' => 'BSA124S-CW',
            'Readability (g)' => '0.0001',
            'Weighing capacity (g)' => '120',
            'Pan size (mm)' => '∅ 90',
            'Repeatability (g)' => '0.0001',
            'Linearity (g)' => '0.0002',
            'Response time (avg., sec.)' => '2.5',
            'Calibration and adjustment' => 'Internal',
        ]],

        // ── Batch 2: Precision balances ───────────────────────────────

        ['model' => 'BSA223S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA223S',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '220',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA223S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA223S-CW',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '220',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA323S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA323S',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '320',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA323S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA323S-CW',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '320',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA423S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA423S',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '420',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA423S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA423S-CW',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '420',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA623S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA623S',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '620',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA623S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA623S-CW',
            'Readability (g)' => '0.001',
            'Weighing capacity (g)' => '620',
            'Pan size (mm)' => '∅ 115',
            'Repeatability (g)' => '0.001',
            'Linearity (g)' => '0.002',
            'Response time (avg., sec.)' => '1.3',
            'Calibration and adjustment' => 'Internal',
        ]],

        ['model' => 'BSA822', 'batch' => 2, 'specs' => [
            'Model' => 'BSA822',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '820',
            'Pan size (mm)' => '∅ 150',
            'Repeatability (g)' => '0.02',
            'Linearity (g)' => '0.03',
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA822-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA822-CW',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '820',
            'Pan size (mm)' => '∅ 150',
            'Repeatability (g)' => '0.02',
            'Linearity (g)' => '0.03',
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'Internal',
        ]],

        ['model' => 'BSA2202S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA2202S',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '2,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA2202S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA2202S-CW',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '2,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA3202S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA3202S',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '3,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA3202S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA3202S-CW',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '3,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA4202S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA4202S',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '4,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA4202S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA4202S-CW',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '4,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA6202S', 'batch' => 2, 'specs' => [
            'Model' => 'BSA6202S',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '6,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA6202S-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA6202S-CW',
            'Readability (g)' => '0.01',
            'Weighing capacity (g)' => '6,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.01',
            'Linearity (g)' => '0.02',
            'Response time (avg., sec.)' => '1.1',
            'Calibration and adjustment' => 'Internal',
        ]],

        ['model' => 'BSA2201', 'batch' => 2, 'specs' => [
            'Model' => 'BSA2201',
            'Readability (g)' => '0.1',
            'Weighing capacity (g)' => '2,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.1',
            'Linearity (g)' => '0.3',
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA2201-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA2201-CW',
            'Readability (g)' => '0.1',
            'Weighing capacity (g)' => '2,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.1',
            'Linearity (g)' => '0.1',  // brochure prints 0.1 for -CW vs 0.3 for non-CW
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA5201', 'batch' => 2, 'specs' => [
            'Model' => 'BSA5201',
            'Readability (g)' => '0.1',
            'Weighing capacity (g)' => '5,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.1',
            'Linearity (g)' => '0.3',
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA5201-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA5201-CW',
            'Readability (g)' => '0.1',
            'Weighing capacity (g)' => '5,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.1',
            'Linearity (g)' => '0.1',  // brochure prints 0.1 for -CW vs 0.3 for non-CW
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'Internal',
        ]],
        ['model' => 'BSA8201', 'batch' => 2, 'specs' => [
            'Model' => 'BSA8201',
            'Readability (g)' => '0.1',
            'Weighing capacity (g)' => '8,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.1',
            'Linearity (g)' => '0.3',
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'External',
        ]],
        ['model' => 'BSA8201-CW', 'batch' => 2, 'specs' => [
            'Model' => 'BSA8201-CW',
            'Readability (g)' => '0.1',
            'Weighing capacity (g)' => '8,200',
            'Pan size (mm)' => '180 × 180',
            'Repeatability (g)' => '0.1',
            'Linearity (g)' => '0.1',  // brochure prints 0.1 for -CW vs 0.3 for non-CW
            'Response time (avg., sec.)' => '1',
            'Calibration and adjustment' => 'Internal',
        ]],
    ],
];
