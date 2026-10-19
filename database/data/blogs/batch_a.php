<?php

return [
    [
        'brand' => 'Sartorius',
        'title' => 'How to choose an analytical or semi-micro balance: readability, capacity, repeatability',
        'slug' => 'how-to-choose-analytical-semi-micro-balance',
        'excerpt' => 'Readability alone does not tell you how small a sample you can weigh reliably. Learn how readability, capacity and repeatability work together before you pick a balance.',
        'seo_description' => 'A practical guide for Indian labs: how readability, capacity and repeatability decide whether an analytical or semi-micro balance suits your samples.',
        'html' => <<<'HTML'
<p>Buying a laboratory balance often starts with one question: how many decimal places does it show? That is only one part of the story. A balance that displays 0.01 mg is not automatically able to weigh a 0.01 mg sample reliably. This guide explains the three numbers that matter most - readability, capacity and repeatability - and how they connect to the smallest sample you can weigh with confidence.</p>

<h2>Know the balance categories</h2>
<p>Sartorius groups its laboratory balances by readability, which it defines as the smallest difference in mass that can still be read on the display. Its current range, as listed on its laboratory balances page, runs roughly like this [1]:</p>
<table>
<thead><tr><th>Category</th><th>Readability</th><th>Maximum capacity</th></tr></thead>
<tbody>
<tr><td>Ultra-micro</td><td>0.0001 mg</td><td>2.1 g</td></tr>
<tr><td>Micro</td><td>0.001 mg to 0.002 mg</td><td>21 g to 111 g</td></tr>
<tr><td>Semi-micro</td><td>0.005 mg to 0.01 mg</td><td>120 g to 220 g</td></tr>
<tr><td>Analytical</td><td>0.1 mg</td><td>60 g to 520 g</td></tr>
<tr><td>Precision</td><td>1 mg to 100 mg</td><td>up to 14.2 kg</td></tr>
<tr><td>High-capacity</td><td>100 mg to 1000 mg</td><td>up to 70.2 kg</td></tr>
</tbody>
</table>
<p>Other sources quote slightly different typical ranges for each class, so always read the datasheet of the specific model [3]. The general pattern is the same: the finer the readability, the lower the capacity.</p>

<h2>Readability: choose what your work needs</h2>
<p>Start from the smallest mass difference you must read. A 0.1 mg analytical balance suits most routine analytical weighing, while a semi-micro balance (0.01 mg) is worth considering when your samples are small or your tolerance is tight. Paying for more readability than your method needs adds cost and makes the balance more sensitive to draughts, vibration and static, without improving your results [3].</p>

<h2>Capacity: match your heaviest load</h2>
<p>Capacity is the maximum load the balance can carry, and it includes the container as well as the sample. Sartorius advises matching capacity to your heaviest load [1]. If you weigh very small amounts of substance in large, heavy vessels, a micro-class balance is the better fit; for larger samples, a precision balance is usually enough [1]. Remember that the tare vessel counts towards the maximum, so check that the vessel plus sample stays comfortably within the range.</p>

<h2>Repeatability: the number behind "how small can I weigh"</h2>
<p>Repeatability is the spread (standard deviation) of repeated weighings of the same load under unchanged conditions [3]. It matters because any weighing carries some uncertainty, and that uncertainty weighs more heavily on a small sample. Sartorius illustrates this with an analytical balance: an uncertainty of 0.1 mg on a 100 g sample is only 0.0001 % of the result, but the same 0.1 mg on a 1 mg sample is 10 % [2].</p>
<p>That is why a minimum sample weight is defined. In the pharmacopoeia approach described by Sartorius, a repeatability test of at least 10 readings is done, and the result is acceptable when twice the standard deviation, divided by the smallest net weight you intend to weigh, is not more than 0.10 % [2]. The theoretical minimum weight is then about 2000 times the standard deviation, with a lower limit of 820 times the readability so that a very lucky test does not give a minimum of zero [2].</p>
<p>A simpler rule of thumb found in general lab guidance is to keep the sample at least 1000 times the readability, which keeps the relative uncertainty below about 0.1 % [3]. For a 0.1 mg balance that is around 100 mg, and for a 0.01 mg balance around 10 mg [3]. Treat this as a quick screening rule only. For regulated work, use the measured minimum weight of your own balance, in your own room.</p>

<h2>Net weight, not tare difference</h2>
<p>Sartorius points out that minimum sample weights apply to the net amount. You cannot, for example, weigh 0.5 g of substance in a 50 g vessel on a balance whose minimum is 1 g, and treat the difference between the full and empty vessel as a valid result [2]. Plan the smallest net amount you will really weigh, then choose a balance whose minimum weight sits comfortably below it. Sartorius also advises against simply resetting your smallest desired weight to whatever the latest repeatability test produced, because that result can change from test to test [2].</p>

<h2>Conditions in your laboratory</h2>
<ul>
<li>Vibration, air currents, temperature changes and static can all shift readings, and static can matter more at higher resolution [3].</li>
<li>Let cold or warm samples reach room temperature before weighing, to avoid convection effects [3].</li>
<li>Sartorius states that its balances can be used between 5 and 40 degrees, though a constant temperature is preferred [1].</li>
<li>Balances with the isoCAL function recalibrate automatically when conditions change, but occasional external calibration with certified weights is still recommended [1].</li>
<li>Repeatability published for a balance may not be reached on difficult samples such as fine powders. Ask for data measured under conditions close to your own [3].</li>
</ul>

<h2>Quick checklist before you choose</h2>
<ul>
<li>Write down the smallest net sample you will weigh and the tolerance your method needs.</li>
<li>Choose the readability class from that tolerance, not from the longest display.</li>
<li>Add the weight of your largest vessel to the largest sample, and check that it fits within capacity.</li>
<li>Check the repeatability and minimum weight figures in the datasheet, and confirm them on site.</li>
<li>Check the bench: stable, away from air-conditioning vents, doors and vibrating equipment.</li>
<li>Plan calibration: internal where available, plus periodic external checks with certified weights.</li>
<li>If you work under a pharmacopoeia or an accreditation scheme, check what that scheme requires for routine tests.</li>
</ul>

<h2>Conclusion</h2>
<p>Readability tells you what the display can show, capacity tells you what the balance can carry, and repeatability tells you how far to trust the last digits. Decide your smallest real sample first, pick the balance class whose repeatability supports it, and then confirm performance in your own laboratory. This article is general guidance only; always rely on the manufacturer's datasheet for the exact specification of any model.</p>
HTML,
        'sources' => [
            ['label' => 'Sartorius - Laboratory Balances & Scales (categories, selection advice)', 'url' => 'https://www.sartorius.com/en/products/weighing/laboratory-balances', 'accessed' => '2026-10-20'],
            ['label' => 'Sartorius white paper (April 2026) - Minimum Sample Weights According to Various Pharmacopoeias, OIML R 76, and EURAMET cg-18', 'url' => 'https://www.sartorius.com/resource/blob/580758/6e89cff42ec6efa2863140b467dc5463/minimum-weight-white-paper-en-sartorius-pdf-data.pdf', 'accessed' => '2026-10-20'],
            ['label' => 'Lab Manager - Analytical Lab Balances: How to Choose, Calibrate, and Weigh Accurately', 'url' => 'https://www.labmanager.com/analytical-lab-balances-how-to-choose-calibrate-and-weigh-accurately-35320', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'Anton Paar',
        'title' => 'Rotational vs capillary viscometers: how viscosity is measured and which to pick',
        'slug' => 'rotational-vs-capillary-viscometers-which-to-pick',
        'excerpt' => 'Capillary viscometers time a liquid flowing under gravity; rotational viscometers measure torque on a spinning part. See what each measures and when to use which.',
        'seo_description' => 'Compare capillary and rotational viscometers: what they measure, which liquids suit each, and what to check before choosing one for your lab.',
        'html' => <<<'HTML'
<p>Viscosity is a liquid's resistance to flow, and it matters in oils, paints, syrups, pharmaceutical liquids and many other products. Two instrument families dominate routine laboratories: capillary viscometers and rotational viscometers. They measure different things in different ways, so choosing the wrong one can give results that are hard to compare or simply not valid for your sample.</p>

<h2>Two kinds of viscosity</h2>
<p>The European Pharmacopoeia defines dynamic viscosity as the shearing stress needed to move one layer of liquid relative to another at a given rate of shear. Its unit is the pascal second (Pa&middot;s), with the millipascal second (mPa&middot;s) most commonly used [1]. Kinematic viscosity is the dynamic viscosity divided by the density of the liquid at the same temperature, and it is usually expressed in square millimetres per second [1].</p>
<p>In older literature you will still meet centipoise (cP) for dynamic viscosity and centistokes (cSt) for kinematic viscosity [2]. The two are linked through density, so the same liquid gives different numbers in each system unless you know its density [2].</p>

<h2>Newtonian and non-Newtonian liquids</h2>
<p>A Newtonian liquid has the same viscosity whatever the shear rate; lubricating oils, glycerin and alcohol are given as examples [2]. A non-Newtonian liquid changes viscosity with shear, which is typical of products such as ketchup [2]. This single distinction decides much of the choice. The European Pharmacopoeia says a capillary viscometer may be used for Newtonian liquids, whereas a rotating viscometer can be used for both Newtonian and non-Newtonian liquids [1].</p>

<h2>How a capillary viscometer works</h2>
<p>Ostwald and Ubbelohde viscometers are glass U-tubes held vertically in a temperature-controlled bath. The liquid flows through a narrow capillary between two marks, and the flow time gives the kinematic viscosity [3]. In the pharmacopoeia method, the viscometer constant is determined with a calibration liquid, and kinematic viscosity is the constant multiplied by the flow time [1].</p>
<p>The method is simple but needs discipline [1][3]:</p>
<ul>
<li>The measurement is done at 20 &plusmn; 0.1 &deg;C unless otherwise prescribed, so tight bath control is essential.</li>
<li>Flow time is measured with a stopwatch to the nearest one-fifth of a second.</li>
<li>Two consecutive readings must not differ by more than 1 per cent, and the flow time is the average of at least three readings.</li>
<li>Each viscometer size covers only a limited range. In the pharmacopoeia table, size 1 is intended for roughly 3.5 to 10 mm&sup2;/s, while size 5 covers 20,000 to 100,000 mm&sup2;/s, so you may need several capillaries.</li>
<li>The flow time should not be too short: the minimum is 350 s for size 1 and 200 s for the other sizes.</li>
<li>The flow has to stay laminar, and opaque or staining liquids can make the marks hard to read [3].</li>
</ul>
<p>Because gravity is the only driving force, capillary instruments give kinematic viscosity under one fixed condition [2][3].</p>

<h2>How a rotational viscometer works</h2>
<p>A rotational viscometer measures the torque needed to turn a rotor at a constant rotational speed in the liquid [1]. Common designs include spindles, concentric cylinders (cup and bob) and cone-and-plate geometries [3]. The result is dynamic viscosity [2].</p>
<p>For non-Newtonian liquids the pharmacopoeia says it is indispensable to state the shear stress or shear rate at which the viscosity was measured [1]. A value without its shear rate is incomplete. Rotational instruments can also need regular calibration and maintenance [3].</p>

<h2>Which one should you pick?</h2>
<table>
<thead><tr><th>Question</th><th>Leans towards capillary</th><th>Leans towards rotational</th></tr></thead>
<tbody>
<tr><td>Sample behaviour</td><td>Newtonian liquid [1]</td><td>Newtonian or non-Newtonian [1]</td></tr>
<tr><td>Result you need</td><td>Kinematic viscosity [3]</td><td>Dynamic viscosity [2]</td></tr>
<tr><td>Shear dependence</td><td>Single flow condition only [3]</td><td>Can be measured at chosen speeds [1]</td></tr>
<tr><td>Temperature control</td><td>Precise bath required [1][3]</td><td>Required too; check how the instrument handles it [3]</td></tr>
</tbody>
</table>
<p>A viscometer measures under one flow condition. If the product's behaviour changes strongly with flow and you need the full picture, the instrument you need may be a rheometer rather than a viscometer [3].</p>

<h2>Quick checklist</h2>
<ul>
<li>Identify whether your liquid is Newtonian; if unsure, test at more than one speed on a rotational instrument.</li>
<li>Check which result your specification or standard calls for: kinematic or dynamic, and at what temperature.</li>
<li>Estimate the viscosity range so you can choose the right capillary size or spindle.</li>
<li>Plan temperature control; viscosity changes strongly with temperature.</li>
<li>Calibrate with a reference liquid of known viscosity and record the date.</li>
<li>Note the shear rate or speed with every non-Newtonian result.</li>
<li>Check the density if you need to convert between kinematic and dynamic values.</li>
</ul>

<h2>Conclusion</h2>
<p>Use a capillary viscometer for Newtonian liquids when you need kinematic viscosity under tightly controlled temperature; use a rotational viscometer when you need dynamic viscosity, when the liquid is non-Newtonian, or when you want to compare different shear rates. Always follow the method named in your specification and the instrument manufacturer's manual. This article is general guidance and not a substitute for either.</p>
HTML,
        'sources' => [
            ['label' => 'European Pharmacopoeia 7.0 - chapters 2.2.8 Viscosity, 2.2.9 Capillary viscometer method, 2.2.10 Rotating viscometer method (PDF extract)', 'url' => 'https://drugfuture.com/Pharmacopoeia/EP7/DATA/20209E.PDF', 'accessed' => '2026-10-20'],
            ['label' => 'CSC Scientific - What is the difference between dynamic and kinematic viscosity?', 'url' => 'https://www.cscscientific.com/csc-scientific-blog/whats-the-difference-between-dynamic-and-kinematic-viscosity', 'accessed' => '2026-10-20'],
            ['label' => 'chemeurope - Viscometer (encyclopedia)', 'url' => 'https://www.chemeurope.com/en/encyclopedia/Viscometer.html', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'Borosil',
        'title' => 'Why borosilicate glass 3.3 is the standard for laboratory glassware',
        'slug' => 'why-borosilicate-3-3-glass-is-lab-standard',
        'excerpt' => 'Borosilicate 3.3 combines low thermal expansion with strong chemical resistance. Here is what that means for heating, cleaning and everyday use in your lab.',
        'seo_description' => 'Why borosilicate glass 3.3 is used for lab glassware: composition, low thermal expansion, chemical resistance, standards and safe-use tips.',
        'html' => <<<'HTML'
<p>Open any chemistry cupboard in India and most of the beakers, flasks and bottles will be made of the same material: borosilicate glass 3.3. The number is not a model code; it describes how much the glass expands when heated. This article explains what makes this glass suitable for the laboratory, what it cannot do, and how to look after it.</p>

<h2>What borosilicate 3.3 is made of</h2>
<p>Borosilicate glass contains boron oxide as a major component alongside silica. Borosil Scientific gives the composition of its 3.3 grade as roughly 80.6 % silica (SiO<sub>2</sub>), 13 % boron oxide (B<sub>2</sub>O<sub>3</sub>), 4 % sodium oxide and 2.3 % aluminium oxide [1]. Compared with the higher-expansion borosilicate grades the same company lists (4.9 and 5.4), the 3.3 grade has more boron oxide and less sodium oxide [1].</p>

<h2>The meaning of "3.3"</h2>
<p>The figure refers to the coefficient of thermal expansion, 3.3 &times; 10<sup>-6</sup> per kelvin, measured between 20 and 300 &deg;C [1][3]. Borosil Scientific describes 3.3 as the lowest-expansion grade among those it lists (the others are 4.9 and 5.4) [1]. A glass that expands less produces smaller internal stress when one part of the vessel is hotter than another, which is why it copes better with heating on a hot plate or a sudden change in temperature than ordinary glass would. A separate manufacturer, Steroglass, quotes the same property as 32.5 &times; 10<sup>-7</sup> per &deg;C, which is the same value written differently [2].</p>

<h2>Useful temperature points</h2>
<ul>
<li>Annealing point: 565 &deg;C [1][2][3].</li>
<li>Softening point: about 820 &deg;C [1][2].</li>
<li>Density at 25 &deg;C: 2.23 g/cm<sup>3</sup> [1].</li>
<li>One supplier recommends a maximum working temperature of 500 &deg;C, for short periods only [3].</li>
</ul>
<p>These are material properties, not a promise that every piece of glassware can be heated to those limits. Individual items can have their own limits, so follow the maker's instructions. Steroglass notes that heating above 500 &deg;C may leave permanent residual stress after cooling, so repeated high-temperature use of the same piece is best avoided [2].</p>

<h2>Chemical resistance</h2>
<p>Borosil Scientific states that its borosilicate glass is highly resistant to most chemicals, with hydrofluoric acid, phosphoric acid and hot strong caustic solutions as the exceptions, where quartz is the better material [1]. Steroglass describes resistance to neutral and acidic solutions, including chlorine, bromine, iodine and many organic substances, even during long reactions above 100 &deg;C, and again lists hydrofluoric acid, very hot phosphoric acid and hot concentrated alkali as attackers of the surface [2]. A distributor lists the glass as hydrolytic class HGB 1 under ISO 719 [3]. In practice, this means the glass releases very little into water-based samples, which supports accurate analytical work.</p>

<h2>Standards and classifications</h2>
<p>Several specifications refer to this glass [1][3]:</p>
<ul>
<li>ASTM E438: Borosil Scientific classifies its 3.3 glass as Type 1 Class A, while its 4.9 and 5.4 grades are Type 1 Class B [1].</li>
<li>USP and European Pharmacopoeia: all three grades are stated to be Type 1 [1].</li>
<li>ISO 3585: Borosil describes this standard as the one defining glass for laboratory glassware, and states that its chemical glassware complies with it, along with USP Type 1 and ASTM E438 Type 1 Class A [1].</li>
</ul>
<p>When compliance matters for an audit, ask the supplier for the certificate that covers your batch rather than relying on the general description.</p>

<h2>Everyday care</h2>
<ul>
<li>Heat and cool gradually; avoid placing a hot vessel on a cold, wet surface.</li>
<li>Inspect for chips, cracks and star marks before heating, because damaged glass is weaker.</li>
<li>Avoid hydrofluoric acid, hot phosphoric acid and hot strong caustic solutions in glass [1].</li>
<li>Repeated dry and wet sterilisation cycles are normally tolerated by this glass [2], but follow the instructions for each item.</li>
</ul>

<h2>Quick checklist when buying or using</h2>
<ul>
<li>Check that the glass is stated as borosilicate 3.3, not a generic or higher-expansion grade.</li>
<li>Look for the standards stated on the product page or certificate (for example ASTM E438, USP Type 1, ISO 3585).</li>
<li>Check the marked class of volumetric items (A or B) against your method's accuracy needs.</li>
<li>Know your chemistry: avoid hydrofluoric acid, hot phosphoric acid and hot strong caustic solutions in glass.</li>
<li>Inspect before every heating cycle, and discard damaged pieces.</li>
<li>Stay well below the maker's stated temperature limit for each item.</li>
</ul>

<h2>Conclusion</h2>
<p>Borosilicate 3.3 is the common choice for laboratory glassware because its low thermal expansion reduces thermal stress, its composition resists most reagents and water, and it is described by recognised classifications used in pharmaceutical and analytical work. It is not indestructible and has specific weaknesses, so use it within the limits given by the manufacturer. This article gives general guidance only; the product certificate and manual are the authority for any individual item.</p>
HTML,
        'sources' => [
            ['label' => 'Borosil Scientific - What is Borosilicate Glass? (types, composition and properties)', 'url' => 'https://www.borosilscientific.com/a-comprehensive-guide-to-borosilicate-glass-understanding-types-composition-and-properties/', 'accessed' => '2026-10-20'],
            ['label' => 'Steroglass - 3.3 Borosilicate glass: a valuable material for scientific and laboratory instruments', 'url' => 'https://www.steroglass.it/en/magazine/33-borosilicate-glass-valuable-material-scientific-and-laboratory-instruments', 'accessed' => '2026-10-20'],
            ['label' => 'ProSciTech - Laboratory glassware standards (3.3 borosilicate data)', 'url' => 'https://proscitech.com.au/pages/laboratory-glassware-standards', 'accessed' => '2026-10-20'],
        ],
    ],
];
