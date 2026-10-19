<?php

return [
    [
        'brand' => 'Hanna',
        'title' => 'pH meter calibration and electrode care: a practical guide',
        'slug' => 'ph-meter-calibration-and-electrode-care-practical-guide',
        'excerpt' => 'How to calibrate a pH meter with fresh buffers, read slope and offset, clean the electrode and store it correctly, based on manufacturer guidance.',
        'seo_description' => 'Practical pH meter calibration and electrode care: buffers, slope and offset limits, cleaning soaks and correct storage, with sources.',
        'html' => <<<'HTML'
<p>A pH meter is only as good as its last calibration and the condition of its electrode. Most "bad meter" complaints in QC and teaching labs turn out to be a dirty electrode, an old buffer or a dried-out glass bulb. This guide pulls together the routine that Hanna Instruments describes in its electrode maintenance material, with one independent reference on temperature. It is general guidance, so always read the manual of your own meter and electrode.</p>

<h2>Why calibration and cleanliness come first</h2>
<p>Hanna's guide states that the most common cause of inaccurate pH measurement is a dirty or improperly cleaned electrode, and that an electrode which looks clean is not necessarily clean [1]. During calibration the meter simply assumes the electrode is clean, so a fouled sensor can still be "calibrated" and then give wrong readings once the contamination washes off [1]. The guide sums the routine up as: clean regularly, calibrate often, condition always [1].</p>

<h2>Calibrating step by step</h2>
<ol>
<li><strong>Prepare the electrode.</strong> For a new electrode, remove the protective cap and look for salt crystals, which form when storage solution evaporates and are normal; rinse them off with water. If the electrode is dry, soak the bulb and junction in storage solution for at least one hour, with an overnight soak being best [1].</li>
<li><strong>Check the fill solution on refillable electrodes.</strong> Top it up if the level is low and open the fill cap during calibration and measurement, which gives a better electrolyte flow and a faster, steadier reading [1]. Use the right electrolyte: the guide says single-junction electrodes typically use 3.5 M KCl with AgCl, and double-junction electrodes use 3.5 M KCl [1].</li>
<li><strong>Use two or more points.</strong> Calibrate in pH 7.01 first, because this sets the offset; the second buffer sets the slope [1]. Choose buffers that bracket the pH you expect in your samples. The guide's example is a sample near pH 8, which calls for pH 7.01 and pH 10.01 buffers [1].</li>
<li><strong>Rinse and stir.</strong> Rinse the electrode with deionised water before and after every solution; the guide says distilled, RO or similar purified water also works for rinsing. A stirrer keeps the solution uniform and speeds the response [1].</li>
</ol>

<h2>Reading slope and offset</h2>
<p>Hanna recommends that the offset stays within &plusmn;30 mV and that the slope is between 85% and 105% [1]. The offset can be checked by putting the meter in mV mode and reading the electrode in pH 7.01 [1]. If the offset is still outside the range after cleaning, changing the fill solution and using fresh buffer, the electrode may need replacing [1]. Also look at the glass bulb and stem; scratches or cracks mean the electrode should be replaced [1].</p>
<p>The reference point for slope is the theoretical value: about 59.16 mV per pH unit at 25 &deg;C, rising to 54.20 mV at 0 &deg;C and 64.12 mV at 50 &deg;C [3]. That is why a temperature sensor matters. The same source advises calibrating at the same temperature at which you will measure, and notes that the meter compensates for the electrode's temperature behaviour but not for the way the sample's own pH changes with temperature [3].</p>

<h2>Looking after your buffers</h2>
<p>The guide says calibration is only as good as the buffer. Buffers below pH 7.01 should be used within about 3 to 6 months of opening, and those above pH 7.01 within about 1 to 3 months [1]. Alkaline buffers are less stable because carbon dioxide from the air dissolves in them and shifts their pH [1]. To avoid cross-contamination, do not dip the electrode into the stock bottle and never pour used buffer back; pour a small amount into a separate container and change it often [1].</p>

<h2>Cleaning the electrode</h2>
<p>Match the cleaner to the deposit. Hanna lists a general-purpose cleaning solution, an enzymatic protein cleaner, an inorganic cleaner (useful for precipitates caused by silver in the fill solution in a ceramic junction) and an oil-and-fat cleaner, each typically as a soak of about 15 minutes, up to 30 minutes for general cleaning [1][2]. After any cleaning, rinse well and then rehydrate in storage solution: at least 2 to 3 hours before calibration according to the maintenance guide, overnight for best results [1]. Do not wipe the glass with a cloth or tissue, since this can damage the sensing glass [2].</p>

<h2>Storing the electrode</h2>
<p>Keep the glass bulb and junction wet and covered in storage solution, in a beaker or storage cap [1]. If storage solution is not available, Hanna says pH 4.01 or pH 7.01 buffer can be used, but not a buffer above pH 7.01 [1]. Never store an electrode in deionised, distilled or RO water. The guide explains that the 3.5 M KCl reference fill creates an osmotic effect, drawing water in and letting electrolyte leak out, which changes the reference potential and can break down the sensing glass over time [1].</p>

<h2>Quick checklist</h2>
<ul>
<li>Inspect the bulb and stem for cracks; check for salt crystals on a new electrode.</li>
<li>Refill and open the fill cap on refillable electrodes before use.</li>
<li>Use fresh buffer in a clean container; note the date you opened each bottle.</li>
<li>Calibrate at pH 7.01 first, then with a second buffer bracketing your samples.</li>
<li>Accept offset within &plusmn;30 mV and slope of 85% to 105%; otherwise clean, refresh buffer and repeat.</li>
<li>Rinse before and after every solution; do not wipe the bulb.</li>
<li>Calibrate and measure at the same temperature, with a temperature probe.</li>
<li>Store in storage solution, never in distilled or deionised water.</li>
</ul>

<h2>Conclusion</h2>
<p>Reliable pH readings come from a boring routine done consistently: a clean, hydrated electrode, fresh buffers that bracket the sample, and a quick look at slope and offset after every calibration. When the numbers drift outside the recommended window, clean and recalibrate before suspecting the meter, and replace the electrode when cleaning no longer brings it back.</p>
HTML,
        'sources' => [
            ['label' => 'Hanna Instruments - pH Electrode Maintenance Guide (PDF)', 'url' => 'https://pages.hannainst.com/hubfs/006-finished-content/pH_Guides/ph-electrode-maintenance-guide--hanna-instruments.pdf', 'accessed' => '2026-10-20'],
            ['label' => 'Hanna Instruments Knowledge Base - How do I clean my pH electrode?', 'url' => 'https://knowledge.hannainst.com/en/knowledge/ph-maintenance-troubleshooting-how-do-i-clean-my-ph-electrode', 'accessed' => '2026-10-20'],
            ['label' => 'Metrohm - How temperature influences the pH value', 'url' => 'https://www.metrohm.com/en/discover/blog/2023/how-temperature-influences-the-ph-value.html', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'Malvern Panalytical',
        'title' => 'Laser diffraction particle size analysis: how it works and what results mean',
        'slug' => 'laser-diffraction-particle-size-analysis-how-it-works-what-results-mean',
        'excerpt' => 'How laser diffraction turns scattered light into a size distribution, what Dv10, Dv50 and Dv90 tell you, and why optical models and sampling matter.',
        'seo_description' => 'Laser diffraction explained: scattering angles, Mie theory, Dv10/Dv50/Dv90, dispersion and sampling, with ISO 13320 context and sources.',
        'html' => <<<'HTML'
<p>Laser diffraction is one of the most widely used methods for measuring the size of powders, suspensions and emulsions, from pharmaceutical actives to cement and pigments. A single measurement takes under a minute, but reading the report correctly needs a little background. This article explains the principle, the numbers on a typical report and the choices that affect them, using Malvern Panalytical's technical material and the ISO 13320 scope description.</p>

<h2>The principle: angle tells you size</h2>
<p>A laser beam passes through a dispersed sample, and the instrument records how the intensity of the scattered light varies with angle. Large particles scatter light at small angles to the beam, and small particles scatter it at wider angles [1]. Software then works backwards from the measured pattern to the particle sizes that would produce it, using an optical model [1]. The result is reported as the diameter of a volume-equivalent sphere [1]. Malvern describes the technique as suitable for materials from hundreds of nanometres up to several millimetres, with measurements taking less than a minute and good repeatability because a very large number of particles is sampled each time [1].</p>

<h2>Mie theory and Fraunhofer: which model, and why it matters</h2>
<p>Two optical models are in common use. Mie theory is a full solution of how light is scattered by a sphere; it needs the refractive index of the particle (including its absorption) and of the dispersant [1][2]. The Fraunhofer approximation is simpler and needs no optical properties of the sample, but it is accurate for large particles only and should be used with caution below about 50 &micro;m or for relatively transparent particles [1][2]. Malvern's whitepaper notes that the ISO 13320 standard recognises the superiority of Mie theory and recommends it over the whole range of 0.1 to 3000 &micro;m [2]. If the sample's refractive index is not known, it can be measured or estimated by fitting the model to the data [1]. In practice, record the refractive index values you used in the method, because a different choice can change the reported distribution.</p>

<h2>Reading Dv10, Dv50 and Dv90</h2>
<p>Because laser diffraction is an ensemble technique, it reports the distribution by volume, not by particle count [4]. Malvern defines Dv10 as the 10th percentile of the cumulative volume distribution: the size below which 10% of the sample volume lies, useful for tracking the finest particles [3]. The software reports Dv10, Dv50 and Dv90 by default [3]. By the same definition, Dv50 is the size below which half of the volume lies (the median), and Dv90 is the size below which 90% of the volume lies, which tracks the coarse end.</p>
<p>Volume weighting has a consequence: the volume distribution is always shifted towards larger sizes compared with a number distribution, and one 100-micron particle has the same volume as a million 1-micron particles [4]. A few coarse particles can therefore move Dv90 noticeably even when they are rare by count. A tight specification often quotes all three values, so a shift at either end shows up even if the median stays put.</p>

<h2>Dispersion and sampling decide repeatability</h2>
<p>The instrument can only measure what is presented to it. Wet dispersion suspends particles in a liquid, using wetting, stirring and sometimes ultrasound for fine or tightly bound agglomerates. Dry dispersion uses a flow of clean dry air and generally involves higher energy. Malvern notes that fully dispersing particles smaller than 1 micron with dry dispersion is very challenging, and that wet methods should be validated as a reference for fragile particles [5]. Concentration is controlled through obscuration, the proportion of light blocked or scattered by the particles, and recommended ranges depend on particle size and on wet or dry dispersion [3].</p>
<p>Every technique measures a subsample, so it must represent the batch. Malvern's guide points out that unreliable results often trace back to how the sample was taken, and mentions a spinning riffler as a way of dividing powders into representative portions [5].</p>

<h2>Know the limits</h2>
<p>The optical model assumes spherical particles. The ISO 13320:2020 scope describes the method as applicable to roughly 0.1 &micro;m to 3 mm, extendable with special instrumentation, and notes that for non-spherical particles the distribution will differ from sedimentation or sieving results [6]. Malvern likewise explains that a non-spherical particle cannot be fully described by a single number, so the result depends on the technique used [4]. Do not expect laser diffraction numbers to match a sieve analysis exactly; compare like with like.</p>

<h2>Quick checklist</h2>
<ul>
<li>Report the optical model and the refractive index (and absorption) values used.</li>
<li>Quote Dv10, Dv50 and Dv90, not Dv50 alone.</li>
<li>Remember results are volume-based; compare with other methods carefully.</li>
<li>Choose wet or dry dispersion deliberately, and check that agglomerates are broken up without breaking primary particles.</li>
<li>Keep obscuration within the range recommended for the particle size and dispersion type.</li>
<li>Sub-sample representatively, for example with a riffler.</li>
<li>Record the method, so that results are repeatable between analysts and shifts.</li>
</ul>

<h2>Conclusion</h2>
<p>Laser diffraction is fast and repeatable, but its numbers depend on the optical model, the dispersion and the sampling as much as on the instrument. Treat Dv10, Dv50 and Dv90 as a set, document the method settings, and be cautious when comparing with sieving or imaging results.</p>
HTML,
        'sources' => [
            ['label' => 'Malvern Panalytical - Laser Diffraction Particle Size Analysis (technology page)', 'url' => 'https://www.malvernpanalytical.com/en/products/technology/light-scattering/laser-diffraction', 'accessed' => '2026-10-20'],
            ['label' => 'Malvern Panalytical - Mie theory: the first 100 years (whitepaper)', 'url' => 'https://www.malvernpanalytical.com/br/learn/knowledge-center/whitepapers/WP100108MieTheoryHundred.html', 'accessed' => '2026-10-20'],
            ['label' => 'Malvern Panalytical - Laser Diffraction: Frequently Asked Questions', 'url' => 'https://www.malvernpanalytical.com/en/learn/knowledge-center/insights/laser-diffraction-particle-length-analysis-frequently-asked-questions', 'accessed' => '2026-10-20'],
            ['label' => 'Malvern Panalytical - What is particle size?', 'url' => 'https://www.malvernpanalytical.com/en/learn/knowledge-center/insights/what-is-particle-size', 'accessed' => '2026-10-20'],
            ['label' => 'Malvern Panalytical - Basic Guide to Particle Size Analysis (part 3)', 'url' => 'https://www.malvernpanalytical.com/en/learn/knowledge-center/insights/particle-size-analysis-basics-guide-3', 'accessed' => '2026-10-20'],
            ['label' => 'ISO 13320:2020 scope summary (Estonian Centre for Standardisation, EVS)', 'url' => 'https://www.evs.ee/en/iso-13320-2020', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'Retsch',
        'title' => 'Sample preparation: why grinding, milling and sieving decide accuracy',
        'slug' => 'sample-preparation-grinding-milling-sieving-analytical-accuracy',
        'excerpt' => 'Why a representative, homogeneous, correctly sized sample matters more than the instrument that follows, and how to choose between crushers, mills and sieves.',
        'seo_description' => 'Sample preparation basics: representative sampling, choosing crushers and mills, grinding only as fine as needed, contamination and sieving.',
        'html' => <<<'HTML'
<p>An analytical instrument can only report on the portion of material that reaches it. If that portion is not representative, or if it was contaminated or altered on the way, even a well calibrated instrument gives a precise answer to the wrong question. This article looks at the grinding, milling and sieving steps that sit before analysis, using Retsch's product overview as the main reference and independent teaching and technical material for the wider context.</p>

<h2>Why preparation limits accuracy</h2>
<p>Retsch states that reliable and accurate analysis can only be guaranteed by reproducible sample preparation. It describes the task as turning a laboratory sample into a representative part sample with homogeneous analytical fineness [1]. A standard analytical chemistry text makes the same point from the other side: collecting the wrong sample, or losing analyte during preparation, introduces a determinate error, and too few or wrongly sized samples hurt precision [2]. Malvern Panalytical, writing about particle sizing, adds that every technique measures a subsample and that unreliable results often trace back to how samples were taken; it mentions a spinning riffler as a way of dividing powders into representative portions [3].</p>

<h2>Choosing the right size-reduction principle</h2>
<p>Retsch lists the questions to settle before picking a mill: the nature of the sample (dry, tough, abrasive, fibrous, brittle, hard, soft, temperature-sensitive), feed size, required final fineness, sample volume, throughput and the subsequent analysis, including how much contamination from tool wear is acceptable [1]. Its guidance on principle is straightforward [1]:</p>
<ul>
<li>Hard, brittle materials are best reduced by impact and friction, for example in a planetary ball mill.</li>
<li>Soft and elastic materials suit knife or cutting mills.</li>
<li>Jaw crushers are intended for coarse, primary reduction of hard, brittle materials.</li>
<li>Knife mills suit quick homogenisation of samples with a high liquid, oil or fat content, and the overview notes a cryokit option for cold grinding.</li>
<li>Ball mills can work dry or wet, and some models target very fine final finenesses.</li>
</ul>
<p>The overview also notes that the final fineness reached depends on the sample material and instrument configuration, so apparently similar samples can give different results [1].</p>

<h2>Staged grinding and not grinding too fine</h2>
<p>Large particles cannot always be brought to analytical fineness in one step. Sometimes one mill with different settings can do coarse and fine grinding; in other cases two mills or crushers are needed [1]. Retsch also gives a useful rule of thumb: grind only as fine as necessary, not as fine as possible [1]. Over-grinding adds time, heat and wear without improving the result, and heat can drive off volatile components in temperature-sensitive samples.</p>

<h2>Contamination and wear</h2>
<p>Mechanical size reduction inevitably wears the grinding tools, and Retsch says abrasion generally lies in the ppm or ppb range, with material introduced such as steel components or heavy metals like chromium [1]. That can matter greatly if you are measuring trace metals and hardly at all for a moisture or protein test. Jaw crushers, for instance, are offered with breaking jaws of different materials (manganese steel, stainless steel, NiHard4, tungsten carbide, zirconium oxide) so the tool can be matched to the analysis, and the jaws should be harder than the material crushed [1]. Choose the tool material by asking what element you will determine afterwards.</p>

<h2>Sieving for particle size</h2>
<p>After milling, particle size distribution is often checked by sieving. Retsch's range covers vibratory sieve shakers for dry and wet sieving, a tap sieve shaker, and air jet sieving machines described as suited to dry sieving of fine powders, where a controlled air stream disperses the sample [1]. Test sieves come in several diameters (100, 200, 203 (8 inch), 305, 400 and 450 mm), and evaluation software records the weighing data and produces reports [1]. For laser diffraction or image analysis, which measure differently from sieves, the overview points to the sister company Microtrac [1]; results from different principles will not agree exactly, so compare like with like.</p>

<h2>Quick checklist</h2>
<ul>
<li>Define the analysis first, then choose the preparation route.</li>
<li>Take a representative subsample (riffle or divide) before and after reduction.</li>
<li>Match the principle to the material: impact and friction for hard and brittle, cutting for soft and elastic.</li>
<li>Reduce in stages when the feed is coarse; grind only as fine as needed.</li>
<li>Pick grinding tools and jars whose wear will not interfere with the target analyte.</li>
<li>Watch heat for volatile or temperature-sensitive samples, and consider cold grinding.</li>
<li>Clean tools between samples to avoid cross-contamination.</li>
<li>Document sieve, mill and settings so the preparation is reproducible.</li>
</ul>

<h2>Conclusion</h2>
<p>Good analytical results begin before the sample reaches the instrument. A representative, homogeneous sample ground by the right principle, to the right fineness and with the right tool material, removes avoidable error that no calibration can fix later. Write the preparation route into the method, as carefully as the measurement itself.</p>
HTML,
        'sources' => [
            ['label' => 'Retsch - Product Overview: Solutions for Sample Preparation and Grain Size Analysis (PDF)', 'url' => 'https://www.retsch.com/files/15583/product-overview.pdf', 'accessed' => '2026-10-20'],
            ['label' => 'LibreTexts Analytical Chemistry - Obtaining and Preparing Samples for Analysis', 'url' => 'https://chem.libretexts.org/Courses/Shasta_College/Analytical/07%3A_Obtaining_and_Preparing_Samples_for_Analysis', 'accessed' => '2026-10-20'],
            ['label' => 'Malvern Panalytical - Basic Guide to Particle Size Analysis (part 3), sampling section', 'url' => 'https://www.malvernpanalytical.com/en/learn/knowledge-center/insights/particle-size-analysis-basics-guide-3', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'Atago',
        'title' => 'How a refractometer measures Brix and where it is used',
        'slug' => 'how-a-refractometer-measures-brix-and-where-it-is-used',
        'excerpt' => 'The refractive index principle behind Brix readings, why temperature matters, what Brix does and does not tell you, and where refractometers are used.',
        'seo_description' => 'How refractometers measure Brix: refractive index, the Brix scale, temperature compensation, limits for non-sugar samples and typical uses.',
        'html' => <<<'HTML'
<p>Brix is one of the quickest quality checks in food, beverage and process labs: put a drop on a prism, wait a few seconds and read a number. Behind that number is a simple optical idea and a few conditions that decide whether the reading can be trusted. This article explains how a refractometer arrives at Brix, why temperature matters, and where the method fits, using Atago's product material and a general reference on the Brix scale.</p>

<h2>The principle: light bends more in denser solutions</h2>
<p>Atago describes refractometry as based on the principle that as the density of a substance increases, its refractive index rises proportionately [2]. Inside the instrument, light passes through the sample at a prism surface and the boundary between a bright and a dark region on the detector marks an angle that depends on the sample, which the instrument converts to a refractive index [2]. For sugar solutions, tables relate refractive index to concentration, and refractometers are calibrated from these tables to read directly in degrees Brix [3].</p>

<h2>What a Brix number means</h2>
<p>One degree Brix means 1 gram of sucrose in 100 grams of solution, so Brix expresses strength as a percentage by mass [3]. For refractometer work, the underlying refractive index is the value at the sodium D line (589.3 nm) at 20 &deg;C [3]. A handheld meter such as Atago's dual-scale PAL-BX/RI shows both Brix (0.0 to 93.0%, accuracy &plusmn;0.1%) and refractive index (1.3306 to 1.5284), and needs only a couple of drops of sample [1]. Benchtop models resolve much finer differences; Atago's RX series catalogue quotes Brix accuracy as fine as &plusmn;0.010% on its most accurate models [2].</p>

<h2>Why temperature matters</h2>
<p>Refractive index changes with temperature, so a Brix reading depends on it too. For sucrose solutions near 10 &deg;Bx or less, a 1 &deg;C change shifts the reading by roughly 0.06 &deg;Bx, and some other liquids, such as beer, shift about three times as much [3]. There are two ways to deal with this. Benchtop instruments can control the sample temperature: Atago's RX series uses a built-in Peltier thermo-module that heats or cools the sample to the set temperature before measuring [2]. Handheld instruments use automatic temperature compensation (ATC); the PAL-BX/RI lists compensation for Brix over 10 to 100 &deg;C [1].</p>
<p>One detail is easy to miss: on the PAL-BX/RI the displayed refractive index is not temperature compensated, and the manual says it is the index of the sample at the temperature displayed [4]. So if you record refractive index rather than Brix, record the temperature too.</p>

<h2>Where refractometers are used</h2>
<p>Brix is used for wine, fruit juice, carbonated drinks, starch and sugar processing, fresh produce, maple syrup and honey, and also for metalworking cutting fluids [3]. Atago's catalogue says its RX series was developed with the beverage industry in mind and also mentions low-Brix industrial solutions such as surfactants and anti-rust coatings, and high-refractive-index samples such as fragrances and organic solvents [2]. Some instruments let you enter data points to display a concentration scale other than Brix, such as alcohol or salinity [2]. In an Indian setting the same principle applies to a juice line, a sugar or confectionery QC lab or a coolant tank.</p>

<h2>Limits of the Brix reading</h2>
<p>Brix is defined for sucrose. If the dissolved solids are not pure sucrose, the reading only approximates the dissolved solids content, because different solutes change the refractive index differently [3]. Alcohol also raises refractometer readings, so a fermenting or fermented sample needs a different approach [3]. For such samples a Brix value is best used as a consistent process indicator rather than an absolute concentration, unless you have a method validated for that product.</p>

<h2>Quick checklist</h2>
<ul>
<li>Clean and dry the prism, then check the instrument with distilled water at about 20 &deg;C, as accuracy specifications are quoted with water at 20 &deg;C; follow your manual for adjustment [1][4].</li>
<li>Let the sample come to a stable temperature; use ATC or a temperature-controlled benchtop model.</li>
<li>Cover the prism fully with sample and avoid air bubbles.</li>
<li>Record temperature along with refractive index readings.</li>
<li>Remember that Brix is a sucrose-equivalent figure; be cautious with samples containing alcohol or non-sugar solids.</li>
<li>Wipe the prism clean after each sample to avoid carry-over.</li>
<li>Use a consistent method and operator procedure for comparisons between batches.</li>
</ul>

<h2>Conclusion</h2>
<p>A refractometer does not measure sugar directly; it measures refractive index and converts it to a sucrose-equivalent percentage. That makes it fast and dependable when temperature is controlled or compensated and when the sample is mainly sugar in water. Where other solids or alcohol are present, treat the reading as an indicator and agree the expected range with your own process data.</p>
HTML,
        'sources' => [
            ['label' => 'ATAGO - Dual scale (BX/RI) Refractometer PAL-BX/RI product page', 'url' => 'https://www.atago.net/en/atagodirect-index.php?key=ALF39437', 'accessed' => '2026-10-20'],
            ['label' => 'ATAGO - Digital Refractometers RX-alpha series catalogue (PDF)', 'url' => 'https://www.atago.net/english/images/catalog/rx-alpha_en.pdf', 'accessed' => '2026-10-20'],
            ['label' => 'Wikipedia - Brix (general reference on the scale)', 'url' => 'https://en.wikipedia.org/wiki/Brix', 'accessed' => '2026-10-20'],
            ['label' => 'Nova-Tech / ATAGO PAL-BX/RI Digital Pocket Refractometer manual summary (ManualsDir)', 'url' => 'https://www.manualsdir.com/manuals/340319/nova-tech-atago-pal-bxri-digital-pocket-refractometer.html', 'accessed' => '2026-10-20'],
        ],
    ],
];
