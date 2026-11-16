<?php

/**
 * Aczet CM-A-S STENT Weighing Micro Balance (model CM 6A-S) - ONE product, ONE model (14 Nov 2026).
 *
 * *** THIN SOURCES - READ BEFORE LOADING ***
 * aczet.com has NO usable page for this model. https://aczet.com/product/stent-weighing-micro-balance exists as a page called
 * "STENT Weighing Micro Balance" but it is an EMPTY shell (title + "No Accessories Here", no overview, no specs, no documents), and its menu
 * entry is commented out in the site's HTML. There is no CM-S datasheet either. So:
 *  - FACTS ABOUT THE CM 6A-S ITSELF come from ONE dealer listing only (IndiaMART, seller VEGA Calibration And Validation Services LLP, Mumbai,
 *    https://www.indiamart.com/proddetail/aczet-stent-weighing-micro-balance-cm-6a-s-2853050056162.html): capacity 6 g, readability 1 µg,
 *    pan 40 mm dia, "Graphical Backlite" display, operating temperature 18 to 30 C, calibration "Internal/External/Perfect Self Auto Calibration",
 *    "Usage For: Stent Weighing", 1 year warranty. (The same listing also prints "6 kg" and "platform 400 x 400 mm": those are obvious seller
 *    mistakes and are NOT used.) A search snippet of an Industrybuying listing (a Cloudflare site, NOT opened) repeats 6 g / 1 µg / 40 mm / graphical
 *    backlit display / "CM-S Series".
 *  - The BASE MODEL CM 6A is documented by Aczet in the datasheet "CM-A SERIES Touch Screen Micro Balances"
 *    (https://aczet.com/upload/product_documentation/Touch_Screen_Micro_Balance_CM_-_A.pdf, rendered and read, attached below as the document).
 *    The overview / features below use only statements that datasheet makes about the CM-A platform (two-unit design, PSAC, infrared sensor,
 *    10 applications, industries) and general facts about stent weighing (differential weighing before / after coating, from the Sartorius
 *    application page https://www.sartorius.com/en/applications/applied-industries/medical-devices/backweighing-solutions).
 *  - Aczet's own micro balance pages (https://aczet.com/product/micro-balance, /filter-micro-balance) are the source of the accessory names
 *    (CI-01 antistatic ionizer, VRD-AVT 600 anti-vibration table, CPR data printer, OIML test weights).
 *
 * CONFLICTS / UNCONFIRMED (owner to confirm with Aczet or the dealer before switching the product on):
 *  1. Capacity: the dealer lists 6 g, Aczet's CM 6A is 6.1 g. 6 g is used because that is what the CM-A-S listing says.
 *  2. Pan: the CM 6A datasheet says 15 Ø ~ 30 Ø (the standard CM 6 is 30 mm); the dealer lists 40 mm dia for the 6A-S. 40 mm is used and flagged.
 *  3. Display: the dealer says "graphical backlit display" (like the non-touch CM series) but the "A" in the name and the CM-A datasheet mean a
 *     5.7" touch screen. The Display row is therefore LEFT OUT.
 *  4. Repeatability, linearity, minimum weight, response time, interface, weight, size: NOT stated for the 6A-S anywhere. The CM 6A values are kept
 *     below in $cm6aReference (NOT loaded) in case the owner / Aczet confirms they are the same: repeatability 1 µg (2 g) / 1.6 µg (2 to 5 g),
 *     linearity 5 µg, tare range full capacity, min. sample weight as per USP 2 mg, response 8 - 10 sec, 5.7" touch screen, RS 232C,
 *     sensitivity drift 2 ppm/C, A/C adaptor 230 V or 115 V +-20 % 50-60 Hz, ISO GLP / GMP, weighing chamber 90 mm dia, 10.20 / 14.70 kg,
 *     balance size 565 x 565 x 355 mm.
 *  5. What exactly makes it a "stent" balance (a 40 mm pan only? a holder? software?) is not stated anywhere; the text says only that it is listed
 *     for stent weighing and never claims a holder or a stent software is included.
 *  6. The paste said "CM 6A-S, ~6 g x 1 µg, stent weighing": confirmed by the dealer listing only (no Aczet page).
 *
 * Price on the dealer page (Rs 11,70,000) is NOT used.
 */

$category = 'Micro Balance';
$brand    = 'Aczet';
$group    = 'CM-A Series';
$type     = 'Stent Weighing Micro Balance';

$cm6aReference = [
    'Repeatability (µg)'         => '1 (2 g) / 1.6 (2 g to 5 g)',
    'Linearity (µg)'             => '5',
    'Minimum sample weight, as per USP (mg)' => '2',
    'Response time (sec.)'       => '8 - 10',
    'Display'                    => '5.7" touch screen display',
    'Interface'                  => 'RS 232C',
    'Sensitivity drift (ppm/°C)' => '2',
    'Weighing chamber (mm)'      => 'Ø 90',
    'Weight, net / gross (kg)'   => '10.20 / 14.70',
];

$shortDescription = 'The Aczet CM 6A-S is a stent weighing micro balance with 6 g capacity and 1 µg (0.001 mg) readability, listed with a 40 mm diameter weighing pan. It belongs to Aczet\'s CM-A micro balance family, which separates the electronics from the mechanical measuring cell, calibrates itself with Perfect Self Auto Calibration (PSAC) and offers an infrared sensor for touch-free weighing. It is meant for the differential weighing that drug-eluting stent work needs: weigh the stent before and after coating and read the added mass in micrograms.';

$overview = '<p>The <strong>Aczet CM 6A-S Stent Weighing Micro Balance</strong> is a micro balance listed for <strong>stent weighing</strong>. It has a capacity of <strong>6 g</strong>, a readability of <strong>1 µg (0.001 mg)</strong> and a <strong>40 mm diameter weighing pan</strong>, a little larger than the 30 mm pan of the standard CM 6 micro balance.</p>'
    . '<p><strong>Why a micro balance for stents</strong></p>'
    . '<p>Medical stents are small and light, and the coating or drug layer on a drug-eluting stent weighs only a tiny fraction of the stent itself. The usual way to find the coating mass is differential weighing: the stent is weighed before and after the coating or treatment and the difference is the mass that was added. That is only meaningful on a balance that reads to the microgram and holds its value, which is what a micro balance of this class is for.</p>'
    . '<p><strong>The CM-A family behind it</strong></p>'
    . '<ul>'
    . '<li><p><strong>Two-unit design:</strong> the electronics and the precise mechanical measuring cell are separate components, which removes the effect of heat from the electronics on the measurement.</p></li>'
    . '<li><p><strong>Brilliant Mono-Tech</strong> weighing sensor built to match high international standards.</p></li>'
    . '<li><p><strong>Perfect Self Auto Calibration (PSAC):</strong> the balance calibrates itself with its built-in weight when the ambient temperature changes by a set value or a set time has passed; external calibration is also possible.</p></li>'
    . '<li><p><strong>Infrared sensor:</strong> operate the balance with a wave of your hand, so you do not have to touch it while handling a delicate sample.</p></li>'
    . '<li><p><strong>Ten pre-programmed applications</strong> such as filling, statistics, check weighing, percent weighing and formulation, plus a built-in clock for ISO / GLP / GMP documentation.</p></li>'
    . '</ul>'
    . '<p><strong>Accessories that matter for micro weighing</strong></p>'
    . '<p>Static charge on small parts disturbs micro weighing. Aczet offers an optional CI-01 antistatic ionizer for its micro balances, together with an anti-vibration weighing table, a dot matrix data printer, OIML test weights and qualification documents (IQ, OQ, PQ) for regulated work.</p>'
    . '<p>The stent weighing details of this model, such as its exact pan, display and any holder, are confirmed by our team against Aczet\'s current documents before you order. Tell us your stent size and workflow and we will recommend the right set-up.</p>';

$features = [
    ['title' => '1 µg readability, 6 g capacity', 'text' => 'Reads in micrograms, so the small mass added by a stent coating becomes visible.'],
    ['title' => '40 mm diameter pan', 'text' => 'Listed with a 40 mm pan, larger than the 30 mm pan of the standard CM 6 micro balance.'],
    ['title' => 'Two-unit design', 'text' => 'Electronics and the mechanical measuring cell are separate, which removes the heat of the electronics from the weighing cell.'],
    ['title' => 'Perfect Self Auto Calibration (PSAC)', 'text' => 'Calibrates itself with a built-in weight after a temperature change or a set time; internal and external calibration are listed.'],
    ['title' => 'Infrared hands-free sensor', 'text' => 'Start weighing with a wave of the hand, so you touch the balance less while handling delicate samples.'],
];

$advantages = [
    ['title' => 'Made for differential weighing', 'text' => 'Before and after weighing of a stent shows the coating mass in micrograms.'],
    ['title' => 'Stays calibrated', 'text' => 'PSAC adjusts the balance by itself when temperature or time changes.'],
    ['title' => 'Less handling', 'text' => 'The infrared sensor reduces touching of the balance while you work with small, delicate parts.'],
    ['title' => 'Routine work built in', 'text' => 'Ten built-in applications such as statistics, check weighing and percent weighing save routine steps.'],
    ['title' => 'Support for regulated labs', 'text' => 'Built-in clock for ISO / GLP / GMP documentation, and qualification documents (IQ, OQ, PQ) are available from Aczet.'],
];

$faqs = [
    ['question' => 'Is the CM 6A-S made for weighing stents?', 'answer' => 'Yes, the CM 6A-S is listed for stent weighing. It reads to 1 µg with a 6 g capacity, which suits the before and after weighing of small medical parts.'],
    ['question' => 'How is the coating mass of a stent measured?', 'answer' => 'With differential (back) weighing: the stent is weighed before the coating or treatment and again afterwards, and the difference is the added mass.'],
    ['question' => 'What is the pan size?', 'answer' => 'The CM 6A-S is listed with a 40 mm diameter pan. The standard CM 6 micro balance has a 30 mm pan. Please confirm the exact pan and any holder for your stent size with our team before ordering.'],
    ['question' => 'How is the balance calibrated?', 'answer' => 'It is listed with internal, external and Perfect Self Auto Calibration (PSAC). PSAC adjusts the balance with its built-in weight when the ambient temperature changes by a set value or a set time has passed.'],
    ['question' => 'How can I reduce static charge on stents?', 'answer' => 'Aczet offers an optional CI-01 antistatic ionizer for its micro balances, which removes electrostatic charge from samples and containers.'],
    ['question' => 'What operating temperature is listed?', 'answer' => 'The listing gives 18 °C to 30 °C. Micro balances are sensitive to their surroundings, so choose the installation place carefully; an anti-vibration weighing table is available as an accessory.'],
    ['question' => 'Can the balance be supplied with qualification documents?', 'answer' => 'Aczet lists IQ, OQ and PQ qualification documents and a 21 CFR Part 11 PC software among the accessories of its micro balances. Ask our team which options apply to this model.'],
];

$documents = [
    ['title' => 'CM-A Series Datasheet', 'type' => 'Datasheet', 'file' => 'products/documents/01KXE5R7A9BWQ4T8ZH3NC6DGU2.pdf'],
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
        ['model' => 'CM Stent Weighing Micro', 'heading' => 'CM-A-S Stent Weighing Micro Balance', 'batch' => 1, 'specs' => [
            'Model'                      => 'CM 6A-S',
            'Intended use'               => 'Stent weighing',
            'Weighing capacity (g)'      => '6',
            'Readability (µg)'           => '1',
            'Pan size (mm)'              => 'Ø 40',
            'Calibration and adjustment' => 'Internal, external and Perfect Self Auto Calibration (PSAC)',
            'Operating temperature (°C)' => '18 to 30',
        ]],
    ],
];
