<?php

/**
 * Sartorius Cubis II Ultra-Micro (module 2.7S) - ONE product for the whole model line (owner's rule, 13 Nov 2026).
 *
 * The five models are display x draft shield combinations of the same weighing module, so they share every value
 * and sit in one product; the variants are listed in the "Model" row, the overview and the FAQs.
 * Values come from the Sartorius "Cubis II Datasheet" (Version 12, 2023, page 4 "Ultra-Micro Balances 0.0001 mg",
 * draft shield codes page 18, accessories page 19). The model numbers come from Sartorius' own shop and
 * retailer listings (the datasheet has no model table). "MTS" is NOT expanded to any words: no source says what it stands for.
 * Spec protocol: typical values are written first, tolerances in brackets, exactly as printed.
 */

$category = 'Ultra-Micro Lab Balances';
$brand    = 'Sartorius';
$group    = 'Cubis II Series';
$type     = 'Ultra-Micro Balance';

$shortDescription = 'The Sartorius Cubis II ultra-micro balance weighs up to 2.1 g with a readability of 0.1 µg (0.0001 mg). It is built on a modular premium platform: you choose the display (MCA 7-inch colour touchscreen or MCE touchscreen), the draft shield (motorized round glass, or manual stainless steel for filter weighing) and the software. Automatic internal adjustment (isoCAL), guided weighing applications and built-in data integrity controls make it suited to the most demanding weighing work in research, pharma and quality control laboratories.';

$overview = '<p>The <strong>Sartorius Cubis II Ultra-Micro Balance</strong> is the finest-resolution balance of the Cubis II modular premium range. Its <strong>2.7S weighing module</strong> has a capacity of <strong>2.1 g</strong> and a readability of <strong>0.1 µg (0.0001 mg)</strong>, with a typical repeatability of 0.15 µg and a typical linearity deviation of 0.7 µg. It is made for work where a few micrograms matter: weighing filters and membranes, preparing reference standards, particulate and gravimetric analysis, and low-dose pharmaceutical testing.</p>'
    . '<p><strong>One weighing module, five ready combinations</strong></p>'
    . '<ul>'
    . '<li><p><strong>MCA2.7S-2S00-M</strong>: MCA display with a motorized round glass draft shield.</p></li>'
    . '<li><p><strong>MCA2.7S-2S00-F</strong>: MCA display with a manual stainless steel draft shield for weighing filters.</p></li>'
    . '<li><p><strong>MCE2.7S-2S00-M</strong>: MCE display with a motorized round glass draft shield.</p></li>'
    . '<li><p><strong>MCE2.7S-2S00-F</strong>: MCE display with a manual stainless steel draft shield for weighing filters.</p></li>'
    . '<li><p><strong>MCA2.7S-M-MTS</strong>: MCA display, motorized glass draft shield and the QP99 software package.</p></li>'
    . '</ul>'
    . '<p>Optional accessories for the F draft shield include titanium filter weighing pans (52 mm and 75 mm) and a safe-lock tube holder for reaction tubes up to 2 mL, so the same balance covers filter weighing and small-volume sample work.</p>'
    . '<p><strong>Display and software</strong></p>'
    . '<p>The <strong>MCA</strong> has a 7-inch colour touch display with an intuitive interface, a set of basic weighing applications and optional licensable QApp packages for specific applications. The <strong>MCE</strong> has a touchscreen for routine weighing and the factory-installed basic applications, without licensable packages.</p>'
    . '<p><strong>Built for stable results</strong></p>'
    . '<ul>'
    . '<li><p>Monolithic weighing system with a fast typical measurement time of 10 seconds.</p></li>'
    . '<li><p>Glass draft shields with a conductive coating protect the sample from outside electrostatic effects.</p></li>'
    . '<li><p>Automatic internal adjustment (isoCAL) and automated motorized levelling for easy, error-free operation.</p></li>'
    . '<li><p>Technical controls for 21 CFR Part 11 compliance, an integrated audit trail and user management, available directly on the balance.</p></li>'
    . '</ul>'
    . '<p>An ultra-micro balance needs a calm place: away from heat, direct sunlight, drafts, vibrations, heavy foot traffic and strong electromagnetic fields. Our team can help you choose the right combination and plan the installation.</p>';

$features = [
    ['title' => '0.1 µg readability, 2.1 g capacity', 'text' => 'The 2.7S weighing module reads to 0.0001 mg with a typical repeatability of 0.15 µg, for the finest weighing tasks in the lab.'],
    ['title' => 'Modular premium platform', 'text' => 'Choose the MCA 7-inch colour touchscreen with optional QApp software, or the MCE touchscreen for routine weighing, and match the draft shield to your work.'],
    ['title' => 'Motorized glass or filter draft shield', 'text' => 'The M shield is a motorized round glass shield with a learning function; the F shield is a manual stainless steel shield for weighing filters up to 50 mm.'],
    ['title' => 'Automatic internal adjustment (isoCAL)', 'text' => 'The balance adjusts itself after a temperature change of 1.5 K or after 12 hours, so results stay reliable without manual calibration routines.'],
    ['title' => 'Data integrity and 21 CFR Part 11 controls', 'text' => 'Integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance help regulated laboratories meet their requirements.'],
];

$advantages = [
    ['title' => 'Right balance for every job', 'text' => 'Choose the display, draft shield and software you need today and the platform that fits the way your lab works.'],
    ['title' => 'Fast, stable weighing', 'text' => 'Typical stabilization time of 7 seconds and typical measurement time of 10 seconds keep ultra-micro weighing efficient.'],
    ['title' => 'Protection from charged samples', 'text' => 'Conductive-coated glass draft shields reduce the effect of electrostatic charges on the result.'],
    ['title' => 'Ready for regulated work', 'text' => 'Audit trail with 300,000 data points, alibi memory for 300,000 records and user management are built in.'],
    ['title' => 'Easy to operate', 'text' => 'Guided workflows (QApps), automated motorized levelling and a teachable draft shield reduce mistakes and training time.'],
    ['title' => 'Built to last', 'text' => 'Die-cast aluminium, stainless steel and Optiwhite float glass construction suited to a precision laboratory.'],
];

$faqs = [
    ['question' => 'What is the readability and capacity of the Cubis II Ultra-Micro balance?', 'answer' => 'The 2.7S weighing module has a capacity of 2.1 g and a readability (scale interval) of 0.0001 mg, which is 0.1 µg.'],
    ['question' => 'What is the difference between the MCA and MCE versions?', 'answer' => 'The MCA has a 7-inch colour touch display with an intuitive interface, basic weighing applications and optional licensable QApp software packages. The MCE has a touchscreen for routine weighing with the factory-installed basic applications and no licensable packages.'],
    ['question' => 'What do the M and F at the end of the model name mean?', 'answer' => 'M is an automatic, motorized round glass draft shield with a learning function. F is a manual stainless steel draft shield made for weighing filters with diameters up to 50 mm.'],
    ['question' => 'What is the MCA2.7S-M-MTS model?', 'answer' => 'It is the MCA version with the motorized glass draft shield and the QP99 software package. Please ask our team for its availability and what the package contains.'],
    ['question' => 'Can it be used to weigh filters?', 'answer' => 'Yes, choose a model with the F draft shield. It suits filters up to 50 mm (standard Ø 50 mm filter pan), and titanium filter pans of 52 mm and 75 mm are available as accessories for the F draft shield only.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It uses automatic internal adjustment (isoCAL), which is triggered by a temperature change of 1.5 K or after 12 hours. The recommended external test load is 2 g.'],
    ['question' => 'Where should an ultra-micro balance be installed?', 'answer' => 'Indoors on a stable bench, away from heating, direct sunlight, drafts from windows, doors or air conditioning, vibrations, heavy foot traffic, electromagnetic fields and very dry air.'],
    ['question' => 'Does it support regulated laboratory work?', 'answer' => 'Yes. The balance offers an integrated audit trail, user management and technical controls for 21 CFR Part 11 compliance directly on the balance.'],
];

$documents = [
    ['title' => 'Cubis II Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXC2UM8A5GH3VQ7N4ZB9RTPE.pdf'],
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
        ['model' => 'Cubis II Ultra-Micro (2.7S)', 'heading' => 'Cubis II Ultra-Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                          => 'MCA2.7S-2S00-M, MCA2.7S-2S00-F, MCE2.7S-2S00-M, MCE2.7S-2S00-F, MCA2.7S-M-MTS',
            'Weighing module'                => '2.7S',
            'Readability (mg)'               => '0.0001 (0.1 µg)',
            'Weighing capacity (g)'          => '2.1',
            'Pan size (mm)'                  => 'Ø 20 (standard) / Ø 50 (filter pan)',
            'Repeatability up to 5% load (mg)' => '0.00015 typical (0.0002 tolerance)',
            'Repeatability near Max (mg)'    => '0.00018 typical (0.00025 tolerance)',
            'Linearity (mg)'                 => '0.0007 typical (0.0009 tolerance)',
            'Eccentric loading, 1 g test weight (mg)' => '0.0005 typical (0.0007 tolerance)',
            'Sensitivity drift, +10 °C to +30 °C (ppm/K)' => '1',
            'Stabilization time, typical (sec.)' => '7',
            'Measurement time, typical (sec.)' => '10',
            'Minimum weight, USP (mg)'       => '0.082 optimum / 0.3 typical',
            'Calibration and adjustment'     => 'Internal, automatic (isoCAL)',
            'Recommended external test load (g)' => '2 (class E2)',
            'Display'                        => 'MCA: 7" colour touch TFT / MCE: TFT touchscreen',
            'Draft shield'                   => 'M: motorized round glass / F: manual stainless steel (filters)',
            'Interfaces'                     => 'RS232 (9-pin), USB-A, USB-B',
            'Power supply'                   => '100-240 V, 50-60 Hz',
            'Weighing module size, L × W × H (mm)' => '340 × 139 × 129',
        ]],
    ],
];
