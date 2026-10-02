<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\Insight;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Vertical;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================
        // BRANDS
        // =========================================================
        $brandsData = [
            ['name' => 'BUCHI', 'description' => 'Swiss manufacturer of laboratory instruments for evaporation, spray drying, melting point and more.'],
            ['name' => 'Hettich', 'description' => 'German centrifuge manufacturer with over 100 years of experience.'],
            ['name' => 'Mettler Toledo', 'description' => 'Global leader in precision instruments — balances, titrators, pH meters.'],
            ['name' => 'Shimadzu', 'description' => 'Japanese analytical and measuring instruments — HPLC, GC, UV-Vis, TOC.'],
            ['name' => 'Eppendorf', 'description' => 'Premium liquid handling, cell manipulation, sample preparation and storage.'],
            ['name' => 'Sartorius', 'description' => 'Biopharma equipment — lab balances, pipettes, bioreactors, filtration.'],
            ['name' => 'Thermo Fisher', 'description' => 'World leader in scientific instruments, reagents, consumables and software.'],
            ['name' => 'Merck', 'description' => 'Chemicals, reagents, and life science products for research and production.'],
            ['name' => 'Borosil', 'description' => 'India\'s leading manufacturer of lab glassware and scientific products.'],
            ['name' => 'Labomatic', 'description' => 'Automation solutions for chromatography and liquid handling.'],
        ];

        $brands = [];
        foreach ($brandsData as $i => $data) {
            $brands[$data['name']] = Brand::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );
        }

        // =========================================================
        // VERTICALS
        // =========================================================
        $verticalsData = [
            ['name' => 'Analytical Chemistry', 'description' => 'HPLC, GC, spectroscopy, chromatography and analytical instrumentation for precise chemical analysis.'],
            ['name' => 'Life Sciences', 'description' => 'Cell biology, genomics, proteomics and molecular biology instruments and consumables.'],
            ['name' => 'Pharma & Biotech', 'description' => 'Equipment for drug discovery, development, quality control and manufacturing.'],
            ['name' => 'Material Science', 'description' => 'Instruments for characterization, testing and analysis of materials and polymers.'],
            ['name' => 'Environmental Testing', 'description' => 'Water analysis, air quality monitoring, soil testing and environmental compliance solutions.'],
            ['name' => 'Food & Beverage', 'description' => 'Quality control, safety testing and analysis solutions for the food industry.'],
            ['name' => 'Clinical Diagnostics', 'description' => 'Diagnostic instruments, reagents and solutions for clinical laboratories.'],
            ['name' => 'Education & Research', 'description' => 'Teaching labs, academic research instruments and educational training equipment.'],
        ];

        $verticals = [];
        foreach ($verticalsData as $i => $data) {
            $verticals[$data['name']] = Vertical::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }

        // =========================================================
        // CATEGORIES  (each category belongs to a brand, linked to verticals)
        // =========================================================
        $categoriesData = [
            // BUCHI
            ['brand' => 'BUCHI', 'name' => 'Rotary Evaporators', 'verticals' => ['Analytical Chemistry', 'Pharma & Biotech'], 'description' => 'Efficient solvent evaporation for synthesis, sample prep and distillation.'],
            ['brand' => 'BUCHI', 'name' => 'Spray Dryers', 'verticals' => ['Pharma & Biotech', 'Food & Beverage'], 'description' => 'Lab-scale spray drying for powder production and encapsulation.'],
            ['brand' => 'BUCHI', 'name' => 'Melting Point Systems', 'verticals' => ['Analytical Chemistry'], 'description' => 'Automatic melting point, boiling point and cloud point determination.'],

            // Hettich
            ['brand' => 'Hettich', 'name' => 'Centrifuges', 'verticals' => ['Life Sciences', 'Clinical Diagnostics'], 'description' => 'Microcentrifuges, universal centrifuges and floor-standing models.'],
            ['brand' => 'Hettich', 'name' => 'Incubators', 'verticals' => ['Life Sciences', 'Pharma & Biotech'], 'description' => 'CO2 incubators and temperature-controlled incubation systems.'],

            // Mettler Toledo
            ['brand' => 'Mettler Toledo', 'name' => 'Analytical Balances', 'verticals' => ['Analytical Chemistry', 'Pharma & Biotech'], 'description' => 'High-precision analytical and semi-micro balances.'],
            ['brand' => 'Mettler Toledo', 'name' => 'pH Meters', 'verticals' => ['Analytical Chemistry', 'Environmental Testing'], 'description' => 'Benchtop and portable pH, conductivity and dissolved oxygen meters.'],
            ['brand' => 'Mettler Toledo', 'name' => 'Titrators', 'verticals' => ['Analytical Chemistry', 'Food & Beverage'], 'description' => 'Automated potentiometric and Karl Fischer titration systems.'],

            // Shimadzu
            ['brand' => 'Shimadzu', 'name' => 'HPLC Systems', 'verticals' => ['Analytical Chemistry', 'Pharma & Biotech'], 'description' => 'High-performance liquid chromatography for separation and analysis.'],
            ['brand' => 'Shimadzu', 'name' => 'UV-Vis Spectrophotometers', 'verticals' => ['Analytical Chemistry', 'Life Sciences'], 'description' => 'UV-Visible spectrophotometers for quantitative and qualitative analysis.'],
            ['brand' => 'Shimadzu', 'name' => 'Gas Chromatographs', 'verticals' => ['Analytical Chemistry', 'Environmental Testing'], 'description' => 'GC and GC-MS systems for volatile compound analysis.'],

            // Eppendorf
            ['brand' => 'Eppendorf', 'name' => 'Pipettes & Tips', 'verticals' => ['Life Sciences', 'Clinical Diagnostics'], 'description' => 'Manual and electronic pipettes with certified tips.'],
            ['brand' => 'Eppendorf', 'name' => 'PCR & Thermal Cyclers', 'verticals' => ['Life Sciences'], 'description' => 'Mastercycler thermal cyclers for PCR amplification.'],

            // Sartorius
            ['brand' => 'Sartorius', 'name' => 'Lab Balances', 'verticals' => ['Analytical Chemistry', 'Education & Research'], 'description' => 'Cubis and Quintix series precision and analytical balances.'],
            ['brand' => 'Sartorius', 'name' => 'Filtration Systems', 'verticals' => ['Pharma & Biotech', 'Environmental Testing'], 'description' => 'Membrane filters, filter holders, vacuum filtration.'],

            // Thermo Fisher
            ['brand' => 'Thermo Fisher', 'name' => 'Microscopes', 'verticals' => ['Life Sciences', 'Material Science'], 'description' => 'Optical, fluorescence and electron microscopy solutions.'],
            ['brand' => 'Thermo Fisher', 'name' => 'Water Purification', 'verticals' => ['Analytical Chemistry', 'Life Sciences'], 'description' => 'Barnstead and Nanopure water purification systems.'],
            ['brand' => 'Thermo Fisher', 'name' => 'Deep Freezers', 'verticals' => ['Life Sciences', 'Clinical Diagnostics'], 'description' => 'Ultra-low temperature freezers for sample storage.'],

            // Merck
            ['brand' => 'Merck', 'name' => 'Lab Chemicals', 'verticals' => ['Analytical Chemistry', 'Education & Research'], 'description' => 'ACS-grade solvents, reagents and reference standards.'],
            ['brand' => 'Merck', 'name' => 'Chromatography Columns', 'verticals' => ['Analytical Chemistry', 'Pharma & Biotech'], 'description' => 'HPLC columns, SPE cartridges and GC columns.'],

            // Borosil
            ['brand' => 'Borosil', 'name' => 'Lab Glassware', 'verticals' => ['Education & Research', 'Analytical Chemistry'], 'description' => 'Beakers, flasks, condensers and specialty glassware.'],
            ['brand' => 'Borosil', 'name' => 'Hotplates & Stirrers', 'verticals' => ['Education & Research', 'Pharma & Biotech'], 'description' => 'Magnetic stirrers, hotplates and heating mantles.'],
        ];

        $categories = [];
        foreach ($categoriesData as $i => $data) {
            $cat = Category::firstOrCreate(
                ['brand_id' => $brands[$data['brand']]->id, 'slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'sort_order' => $i + 1,
                ]
            );

            // Attach verticals
            $verticalIds = array_map(fn ($v) => $verticals[$v]->id, $data['verticals']);
            $cat->verticals()->attach($verticalIds);

            $categories[$data['brand'] . ' - ' . $data['name']] = $cat;
        }

        // =========================================================
        // PRODUCTS  (2-3 products per category)
        // =========================================================
        $productsData = [
            // BUCHI Rotary Evaporators
            ['category' => 'BUCHI - Rotary Evaporators', 'name' => 'BUCHI Rotavapor R-300', 'short_description' => 'Premium rotary evaporator with motorized lift, digital display and industrial-grade vacuum sealing.', 'specs' => ['Flask Size' => '50 mL – 5 L', 'Speed' => '10 – 280 rpm', 'Heating Bath' => 'Up to 220°C', 'Display' => 'Color TFT'], 'is_top_pick' => true, 'top_pick_order' => 1],
            ['category' => 'BUCHI - Rotary Evaporators', 'name' => 'BUCHI Rotavapor R-100', 'short_description' => 'Reliable and affordable rotary evaporator for teaching and basic R&D labs.', 'specs' => ['Flask Size' => '50 mL – 4 L', 'Speed' => '20 – 280 rpm', 'Heating Bath' => 'Up to 180°C']],

            // BUCHI Spray Dryers
            ['category' => 'BUCHI - Spray Dryers', 'name' => 'BUCHI Mini Spray Dryer B-290', 'short_description' => 'Versatile lab-scale spray dryer for aqueous and organic solvents.', 'specs' => ['Evaporation' => '1 L/h water', 'Inlet Temp' => 'Up to 220°C', 'Nozzle' => 'Two-fluid nozzle']],

            // BUCHI Melting Point
            ['category' => 'BUCHI - Melting Point Systems', 'name' => 'BUCHI M-565', 'short_description' => 'Automatic melting point apparatus with one-click measurement.', 'specs' => ['Temp Range' => 'RT – 400°C', 'Samples' => '3 simultaneous', 'Camera' => 'Built-in video']],

            // Hettich Centrifuges
            ['category' => 'Hettich - Centrifuges', 'name' => 'Hettich MIKRO 200R', 'short_description' => 'Refrigerated microcentrifuge for molecular biology and clinical labs.', 'specs' => ['Max Speed' => '18,000 rpm', 'Max RCF' => '30,130 × g', 'Temp Range' => '-20 to +40°C', 'Capacity' => '24 × 1.5/2.0 mL'], 'is_top_pick' => true, 'top_pick_order' => 2],
            ['category' => 'Hettich - Centrifuges', 'name' => 'Hettich ROTANTA 460R', 'short_description' => 'Floor-standing refrigerated centrifuge for high-throughput labs.', 'specs' => ['Max Speed' => '15,000 rpm', 'Max RCF' => '24,400 × g', 'Capacity' => '4 × 750 mL']],

            // Hettich Incubators
            ['category' => 'Hettich - Incubators', 'name' => 'Hettich C170i CO2 Incubator', 'short_description' => 'Reliable CO2 incubator for cell culture with contamination control.', 'specs' => ['Volume' => '170 L', 'Temp Range' => '+5 to +50°C', 'CO2 Range' => '0 – 20%']],

            // Mettler Toledo Balances
            ['category' => 'Mettler Toledo - Analytical Balances', 'name' => 'Mettler Toledo XPR205', 'short_description' => 'Analytical balance with 0.01 mg readability and built-in quality assurance.', 'specs' => ['Capacity' => '220 g', 'Readability' => '0.01 mg', 'Repeatability' => '0.015 mg', 'Pan Size' => '80 mm'], 'is_top_pick' => true, 'top_pick_order' => 3],
            ['category' => 'Mettler Toledo - Analytical Balances', 'name' => 'Mettler Toledo ME204', 'short_description' => 'Everyday analytical balance for routine weighing applications.', 'specs' => ['Capacity' => '220 g', 'Readability' => '0.1 mg']],

            // Mettler Toledo pH Meters
            ['category' => 'Mettler Toledo - pH Meters', 'name' => 'Mettler Toledo SevenExcellence', 'short_description' => 'Multi-parameter benchtop meter for pH, conductivity and dissolved oxygen.', 'specs' => ['pH Range' => '-2 to 20', 'Resolution' => '0.001 pH', 'Channels' => 'Up to 3']],

            // Mettler Toledo Titrators
            ['category' => 'Mettler Toledo - Titrators', 'name' => 'Mettler Toledo T5', 'short_description' => 'Compact potentiometric titrator for standard titration tasks.', 'specs' => ['Burette Volume' => '20 mL', 'Resolution' => '0.1 μL', 'Methods' => 'Up to 100']],

            // Shimadzu HPLC
            ['category' => 'Shimadzu - HPLC Systems', 'name' => 'Shimadzu Nexera X3', 'short_description' => 'Ultra-high-performance liquid chromatograph with AI-assisted diagnostics.', 'specs' => ['Max Pressure' => '130 MPa', 'Flow Rate' => '0.0001 – 5 mL/min', 'Detector' => 'PDA / UV'], 'is_top_pick' => true, 'top_pick_order' => 4],
            ['category' => 'Shimadzu - HPLC Systems', 'name' => 'Shimadzu LC-2050', 'short_description' => 'Integrated HPLC for routine analysis with small footprint.', 'specs' => ['Max Pressure' => '70 MPa', 'Flow Rate' => '0.001 – 10 mL/min']],

            // Shimadzu UV-Vis
            ['category' => 'Shimadzu - UV-Vis Spectrophotometers', 'name' => 'Shimadzu UV-1900i', 'short_description' => 'Double-beam UV-Vis spectrophotometer with excellent stray light performance.', 'specs' => ['Wavelength' => '190 – 1100 nm', 'Bandwidth' => '1 nm', 'Photometric Range' => '-4 to 4 Abs'], 'is_top_pick' => true, 'top_pick_order' => 5],

            // Shimadzu GC
            ['category' => 'Shimadzu - Gas Chromatographs', 'name' => 'Shimadzu GC-2030', 'short_description' => 'Next-generation gas chromatograph with ClickTek maintenance-free design.', 'specs' => ['Detectors' => 'FID, TCD, ECD, FPD', 'Oven Temp' => '-80 to 450°C', 'Injection' => 'Split/Splitless']],

            // Eppendorf Pipettes
            ['category' => 'Eppendorf - Pipettes & Tips', 'name' => 'Eppendorf Research Plus', 'short_description' => 'Single-channel adjustable volume pipette with spring-loaded tip ejection.', 'specs' => ['Volume Range' => '0.1 – 1000 μL', 'Accuracy' => '±0.6%', 'Autoclavable' => 'Yes']],
            ['category' => 'Eppendorf - Pipettes & Tips', 'name' => 'Eppendorf Xplorer Plus', 'short_description' => 'Electronic pipette with intuitive speed control and multiple modes.', 'specs' => ['Volume Range' => '0.5 – 5000 μL', 'Modes' => 'Pipetting, Dispensing, Mixing', 'Battery' => '8+ hours']],

            // Eppendorf PCR
            ['category' => 'Eppendorf - PCR & Thermal Cyclers', 'name' => 'Eppendorf Mastercycler X50', 'short_description' => 'High-performance PCR thermal cycler with gradient function.', 'specs' => ['Block' => '96-well', 'Temp Range' => '4 – 99°C', 'Ramp Rate' => '5°C/s', 'Gradient' => '1 – 20°C'], 'is_top_pick' => true, 'top_pick_order' => 6],

            // Sartorius Balances
            ['category' => 'Sartorius - Lab Balances', 'name' => 'Sartorius Cubis II', 'short_description' => 'Modular premium lab balance with full touchscreen and customizable hardware.', 'specs' => ['Capacity' => '220 g', 'Readability' => '0.1 mg', 'Interface' => 'USB, Ethernet, RS-232']],

            // Sartorius Filtration
            ['category' => 'Sartorius - Filtration Systems', 'name' => 'Sartorius Stedim Microsart', 'short_description' => 'Membrane filtration system for bioburden and sterility testing.', 'specs' => ['Filter Diameter' => '47 mm / 50 mm', 'Membrane' => 'Cellulose Nitrate', 'Application' => 'QC Testing']],

            // Thermo Fisher Microscopes
            ['category' => 'Thermo Fisher - Microscopes', 'name' => 'Thermo Fisher EVOS M7000', 'short_description' => 'Fully automated digital inverted fluorescence microscope.', 'specs' => ['Magnification' => '2× – 100×', 'Fluorescence' => '7 LED channels', 'Camera' => 'CMOS 4.2 MP']],

            // Thermo Fisher Water Purification
            ['category' => 'Thermo Fisher - Water Purification', 'name' => 'Thermo Scientific Barnstead GenPure xCAD Plus', 'short_description' => 'Produces ASTM Type I ultrapure water from pretreated water.', 'specs' => ['Output' => 'Up to 2 L/min', 'Resistivity' => '18.2 MΩ·cm', 'TOC' => '< 5 ppb']],

            // Thermo Fisher Deep Freezers
            ['category' => 'Thermo Fisher - Deep Freezers', 'name' => 'Thermo Scientific TSX Series -86°C', 'short_description' => 'Ultra-low temperature freezer with V-drive compressors and natural refrigerants.', 'specs' => ['Temp Range' => '-50 to -86°C', 'Capacity' => '549 L', 'Shelves' => '5 fixed'], 'is_top_pick' => true, 'top_pick_order' => 7],

            // Merck Chemicals
            ['category' => 'Merck - Lab Chemicals', 'name' => 'Merck HPLC-Grade Acetonitrile', 'short_description' => 'LiChrosolv hypergrade acetonitrile for LC-MS and UHPLC.', 'specs' => ['Grade' => 'Hypergrade for LC-MS', 'Purity' => '≥ 99.9%', 'Pack Size' => '2.5 L']],
            ['category' => 'Merck - Lab Chemicals', 'name' => 'Merck ACS Methanol', 'short_description' => 'ACS-reagent grade methanol for general laboratory use.', 'specs' => ['Grade' => 'ACS Reagent', 'Purity' => '≥ 99.8%', 'Pack Size' => '2.5 L']],

            // Merck Columns
            ['category' => 'Merck - Chromatography Columns', 'name' => 'Merck Purospher STAR RP-18e', 'short_description' => 'Reversed-phase HPLC column for pharmaceutical and environmental analysis.', 'specs' => ['Particle Size' => '5 μm', 'Length' => '250 mm', 'ID' => '4.6 mm', 'pH Range' => '1.5 – 10']],

            // Borosil Glassware
            ['category' => 'Borosil - Lab Glassware', 'name' => 'Borosil Beaker Set (50-1000 mL)', 'short_description' => 'Low-form graduated borosilicate glass beakers with spout.', 'specs' => ['Material' => 'Borosilicate 3.3', 'Set' => '50, 100, 250, 500, 1000 mL', 'Graduation' => 'White enamel']],
            ['category' => 'Borosil - Lab Glassware', 'name' => 'Borosil Erlenmeyer Flask 500 mL', 'short_description' => 'Narrow-mouth Erlenmeyer flask for mixing, heating and storage.', 'specs' => ['Material' => 'Borosilicate 3.3', 'Capacity' => '500 mL', 'Mouth' => 'Narrow']],

            // Borosil Hotplates
            ['category' => 'Borosil - Hotplates & Stirrers', 'name' => 'Borosil Magnetic Stirrer with Hotplate', 'short_description' => 'Digital magnetic stirrer with ceramic-coated hotplate.', 'specs' => ['Plate Size' => '135 × 135 mm', 'Max Temp' => '340°C', 'Speed' => '100 – 1500 rpm', 'Max Volume' => '3 L'], 'is_top_pick' => true, 'top_pick_order' => 8],
        ];

        foreach ($productsData as $data) {
            Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $categories[$data['category']]->id,
                    'name' => $data['name'],
                    'short_description' => $data['short_description'],
                    'specs' => $data['specs'] ?? null,
                    'is_active' => true,
                    'is_top_pick' => $data['is_top_pick'] ?? false,
                    'top_pick_order' => $data['top_pick_order'] ?? null,
                ]
            );
        }

        // =========================================================
        // CLIENTS
        // =========================================================
        $clientNames = [
            'Sun Pharma', 'Cipla', 'Dr. Reddy\'s', 'Biocon',
            'Lupin', 'Zydus Lifesciences', 'CSIR', 'IIT Bombay',
            'AIIMS', 'Serum Institute', 'Piramal Group', 'Glenmark',
        ];

        foreach ($clientNames as $i => $name) {
            Client::firstOrCreate(
                ['name' => $name],
                [
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }

        // =========================================================
        // SLIDES
        // =========================================================
        $slidesData = [
            ['title' => '43 Years of Scientific Excellence', 'subtitle' => 'Trusted partner for laboratory equipment, instruments & chemicals across India.'],
            ['title' => 'Partnering with Global Leaders', 'subtitle' => 'Strategic alliances with BUCHI, Shimadzu, Eppendorf, Sartorius and 50+ brands.'],
            ['title' => 'End-to-End Lab Solutions', 'subtitle' => 'From analytical instruments to lab consumables — we equip your lab completely.'],
        ];

        foreach ($slidesData as $i => $data) {
            Slide::firstOrCreate(
                ['title' => $data['title']],
                [
                    'subtitle' => $data['subtitle'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }

        // =========================================================
        // INSIGHTS
        // =========================================================
        $insightsData = [
            ['type' => 'blog', 'title' => 'How to Choose the Right Rotary Evaporator for Your Lab', 'excerpt' => 'A comprehensive guide to selecting a rotary evaporator based on your application — from basic teaching to advanced research.', 'content' => '<p>Rotary evaporators are essential in most chemistry labs. But choosing the right one depends on several factors: flask size, heating bath temperature, digital vs analog controls, and of course, budget.</p><p>In this guide, we break down the key considerations for labs of all sizes.</p>'],
            ['type' => 'blog', 'title' => 'Understanding HPLC Column Selection', 'excerpt' => 'Choosing the right HPLC column can make or break your separation. Learn the basics of column chemistry and selection.', 'content' => '<p>The HPLC column is the heart of any liquid chromatography system. Selecting the correct column requires understanding particle size, stationary phase chemistry, column dimensions and your analyte properties.</p>'],
            ['type' => 'news', 'title' => 'Agarwal Brothers Receives Sartorius Excellence Award 2026', 'excerpt' => 'We are proud to announce that Agarwal Brothers has been recognized by Sartorius for outstanding sales and service performance.', 'content' => '<p>Agarwal Brothers received the Sartorius Partner Excellence Award 2026 at the annual distributor conference in Göttingen, Germany. This recognition highlights our commitment to delivering world-class solutions.</p>', 'event_date' => '2026-09-15'],
            ['type' => 'news', 'title' => 'New Shimadzu Nexera X3 UHPLC Now Available', 'excerpt' => 'The next-generation Shimadzu Nexera X3 with AI-assisted diagnostics is now in stock at Agarwal Brothers.', 'content' => '<p>We are excited to offer the new Shimadzu Nexera X3 UHPLC, featuring AI-based self-diagnostics, 130 MPa pressure capability and an ultra-compact design.</p>', 'event_date' => '2026-10-01'],
            ['type' => 'webinar', 'title' => 'Webinar: Best Practices in Laboratory Weighing', 'excerpt' => 'Join our expert panel to learn about GLP-compliant weighing, calibration and balance selection for regulated labs.', 'content' => '<p>This 60-minute webinar covers: selecting the right balance for your application, daily checks, calibration schedules and meeting GLP/GMP requirements.</p>', 'event_date' => '2026-10-20'],
        ];

        foreach ($insightsData as $data) {
            Insight::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'event_date' => $data['event_date'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
