<?php

// Brands from the owner's "Product Master" sheet (column "Brands"), in the sheet's order.
// In the sheet a name in brackets is the company the brand is bought through (Anton Paar(Bishop), Kewaunee(Labindia) ...),
// that is internal, so only the brand name is used here. Choices made while reading the sheet:
//  - "Waters(TA Instruments)" is added as "TA Instruments" (plain "Waters" is already on the list)
//  - "Self(Narang Scientific Works)", "Self(SSSIW)", "Self(Exacta)" are the company's own makes, added by those names
//  - not added: "TBD - Brand Sourcing Needed" (a placeholder) and four unreadable fragments of the sheet
//    ("RlCl P", "esove(oe armer)", "Edf", "ppenor"), to be added by hand once the owner confirms the names.

return [
    'Aczet', 'Anton Paar', 'Atago', 'Brand GmbH', 'Carbolite Gero', 'Cole Parmer', 'DLAB', 'Eutech', 'Hanna', 'Horiba',
    'Ika', 'Labindia', 'Labtronics', 'LECO', 'Malvern Panalytical', 'Microlit', 'Rakiro', 'Retsch', 'Sartorius', 'Supertek',
    'Tempo', 'Thermofisher Scientific', 'Toshcon', 'VacuuBrand', 'Waters', 'TA Instruments', 'Weiss Technik', 'Pelican',
    'Tulin', 'Xylem', 'Velp', 'Glassco', 'Remi',
    'Kewaunee', 'Hair', 'Magritek', 'Interscience', 'Elvatech', 'Narang Scientific Works', 'Labconco', 'Mbraun',
    'Jacomex', 'Rockwell', 'Yatherm Scientific', 'Plastocraft', 'SSSIW', 'Exacta', 'Venture Scientific', 'Lovibond',
    'Borosil', 'Aditya Scientific', 'Zeal', 'HTC', 'Tanaka', 'Pilodist', 'Tannas', 'KEM', 'Flucon', 'Falex',
];
