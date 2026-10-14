<?php

// Client categories = "Layer 2" of the owner's "Product Master - Industry Categorization" list, in the order of that list
// (grouped by their Layer 1 heading in the comments). Used by the migration that loads them and by the dummy-data seeder.
// Two obvious typos of the source file were fixed: "Refinereries" and "Solar PVC" (read as Solar PV).

return [
    // Manufacturing: process industries
    'Textiles & Apparel',
    'Cement, Concrete & Building Materials',
    'Ceramics, Glass & Refractories',
    'Chemicals & Petrochemicals',
    'Paints, Inks, Coatings & Adhesives',
    'Plastics, Polymers & Rubber',
    'Metals & Metallurgy',
    'Paper & Pulp',
    'Leather & Tanning',
    'Fertilizers & Pesticides/Agrochemicals',
    'Explosives & Pyrotechnics',
    'Soaps, Detergents & Surfactants',

    // Manufacturing: engineering and consumer products
    'Semiconductor Fabrication',
    'Electronics & Electrical Component',
    'Solar PV, Battery & Energy Storage',
    'Electrical Equipment & Transformer',
    'Precision Engineering & Tool Rooms',
    'Optics & Photonics',
    'Automotive OEMs & Ancillary/Component',
    'Aerospace & Defense Component',
    'Tyre & Rubber',
    'Gems & Jewellery',
    'Cosmetics & Personal Care',
    'Packaging Material',

    // Energy & Power Generation
    'Thermal, Hydro & Nuclear Power Plants',
    'Renewable Energy',
    'Oil & Gas Refineries',

    // Mining & Minerals
    'Marble, Granite & Stone',
    'Coal & Lignite Mining/Washeries',
    'Metal Ore Mining',
    'Oil & Gas Mining',

    // Food, Beverage & Agro-Processing
    'Food Processing & Packaged Foods',
    'Dairy & Dairy Products',
    'Beverages',
    'Edible Oils & Oilseed Processing',
    'Spice Processing',
    'Agro/Grain/Flour Mills',
    'Seed Testing & Certification',
    'Animal Feed',
    'Fisheries & Aquaculture',
    'Sugar Mills',
    'Tea & Coffee Processing',

    // Pharma, Biotech & Healthcare
    'Bulk Drug/API & Formulation',
    'Biotechnology & Biosimilars',
    'CROs/CMOs',
    'Ayurvedic, Herbal, Nutraceutical & Cosmetics',
    'Medical Device',

    // Waste & Effluent Treatment/Recycle
    'Water Treatment/RO Plant (WTP)',
    'Emission Treatment Plant (ETP)',
    'Solid Waste Management',
    'E-Waste Recycling',
    'Battery Recycling',

    // Healthcare & Diagnostics
    'Hospitals & Clinics',
    'Diagnostic & Pathology Labs',
    'Blood Banks',
    'Veterinary Hospitals & Animal Health',

    // Academic & Research Institutions
    'Universities/Colleges/Schools',
    'Research Institutes',
    'Agricultural Universities',

    // Government & Public Sector
    'Forensic Science Laboratories',
    'Mining Department Laboratories',
    'Public Health Engineering Department (PHED)',
    'Pollution Control Board',
    'Airport/Customs',
    'Standards/Certification Departments',
    'Railways (RDSO) Testing Labs',
    'Geological Survey Department',
    'Soil-Testing Labs',

    // Third-Party Testing & Certification
    'Export Quality Certification Bodies',
    'NABL-Accredited Independent Testing Labs',
    'All Labs',
];
