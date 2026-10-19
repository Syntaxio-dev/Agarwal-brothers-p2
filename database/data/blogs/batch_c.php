<?php

return [
    [
        'brand' => 'Ika',
        'title' => 'How a Rotary Evaporator Works: Principle, Parts and Tips for Safe Evaporation',
        'slug' => 'how-a-rotary-evaporator-works-principle-parts-tips',
        'excerpt' => 'Vacuum, a warm bath, a rotating flask and a cold condenser work together to remove solvent gently. Here is how each part helps, and how to set them sensibly.',
        'seo_description' => 'Learn how a rotary evaporator works: vacuum, bath, rotation and condenser, plus practical tips on settings, flask filling and safe, efficient solvent removal.',
        'html' => <<<'HTML'
<p>A rotary evaporator (rotavap) is one of the most common instruments in a chemistry or QC laboratory. It removes solvent from a sample without cooking the sample. This article explains the principle, the main parts and a few practical habits that make evaporation faster and safer. It is general guidance; always follow the manual of your own instrument.</p>

<h2>The principle: lower pressure, lower boiling point</h2>
<p>A rotary evaporator is essentially a distillation carried out under reduced pressure. A liquid boils when its vapour pressure equals the pressure around it, so when a pump lowers the pressure inside the apparatus, the solvent boils at a lower temperature than it would in open air [4]. This is why heat-sensitive materials are protected: applying vacuum lowers the boiling temperature, and the lower the process pressure, the lower the temperature needed [1][2].</p>

<h2>The four working parts</h2>
<p>Most rotary evaporators combine four functions: heating, rotation, vacuum and cooling [3][4].</p>
<ul>
<li><strong>Heating bath.</strong> A water (or oil) bath warms the flask. Modern baths add protections; IKA's HB 10 bath, for example, is described with a dry-run control that stops heating if the liquid level drops, and an adjustable safety temperature [3].</li>
<li><strong>Rotating flask.</strong> Turning the flask spreads the liquid into a thin film over a large surface, which speeds evaporation, and the motion also helps prevent bumping under vacuum [4].</li>
<li><strong>Vacuum source.</strong> A water aspirator or, more commonly today, a vacuum pump removes air and vapour from the system [4].</li>
<li><strong>Condenser and receiving flask.</strong> Solvent vapour meets a cooled surface, turns back to liquid and is collected, leaving the dissolved material behind in the evaporating flask [1].</li>
</ul>

<h2>Choosing bath, vacuum and cooling together</h2>
<p>These settings work as a set. VACUUBRAND describes a widely used rule of thumb called the delta-20 rule: set the heating bath about 20 &deg;C above the desired evaporation temperature, and run the cooling at least 20 &deg;C below it. Their example is an evaporation at 40 &deg;C, which suggests a 60 &deg;C bath and cooling at 20 &deg;C or lower [1].</p>
<p>The ideal pressure is the one that matches the vapour pressure of your solvent at the chosen temperature. Lower is not automatically better; too strong a vacuum can cause bumping and foaming [1]. Because mixtures change the real vapour pressure, it helps to check the pressure value against the actual solvent mixture rather than the pure solvent alone [2]. Vacuum can also be adjusted quickly, whereas bath temperature responds slowly, so vacuum is the practical control lever during a run [2].</p>
<p>A useful visual check on the condenser: VACUUBRAND suggests watching the condensate on the cooling coil. A good balance is when about two-thirds of the coil is wet and the upper third stays dry [1].</p>

<h2>Rotation speed and flask filling</h2>
<p>The source does not give fixed rpm values, because the right speed depends on the liquid. As a guide, use higher speeds for low-viscosity liquids and lower speeds for very viscous solutions, so that material does not build up on the flask wall and heat transfer is maintained [1]. Do not fill the evaporating flask beyond about half of its capacity; overfilling raises the risk of bumping and contaminating the distillate [1].</p>

<h2>Automatic vacuum control</h2>
<p>Valve-regulated systems tend to overshoot and undershoot the target pressure. Speed-controlled pumps adjust their motor speed to hold the pressure steadily, and some controllers detect the boiling point of the solvent automatically. IKA describes this as giving faster, more efficient and safer distillation with less pump noise and wear [3], and VACUUBRAND makes a similar point about its adaptive pump control [1][2].</p>

<h2>Safe start-up and shut-down habits</h2>
<ul>
<li>Test for leaks first: evacuate the empty, dry apparatus, close the vacuum line and watch the pressure. A fast rise points to a leak [1].</li>
<li>Apply vacuum before lowering the flask into the bath. Otherwise vapour spreads through the system before the pump is working, which makes the pump work harder and the process longer [1].</li>
<li>Watch the receiving flask level; overfilling can cause backflow towards the pump [1].</li>
<li>Check the pump's inlet separator and exhaust condenser regularly if your pump has them [1].</li>
<li>Use the lift-out or end-stop features of your instrument to protect the glassware [3].</li>
</ul>

<h2>Quick checklist</h2>
<ul>
<li>Inspect glassware for cracks and check seals before every run.</li>
<li>Run a quick leak test after assembling.</li>
<li>Fill the flask no more than half.</li>
<li>Set bath, vapour and cooling temperatures with the delta-20 rule in mind.</li>
<li>Start the vacuum before the flask touches the bath.</li>
<li>Choose a pressure suited to your solvent mixture, not simply the lowest possible.</li>
<li>Empty the receiving flask before it gets full.</li>
</ul>

<h2>Conclusion</h2>
<p>A rotary evaporator is simple in idea: reduce the pressure, warm gently, spread the liquid into a film and condense the vapour. Good results come from setting these four elements in balance rather than pushing any one of them to the limit. Treat the numbers above as starting points and refer to your instrument manual and solvent safety data for final settings.</p>
HTML,
        'sources' => [
            ['label' => 'VACUUBRAND - 10 tips for optimal rotary evaporation', 'url' => 'https://www.vacuubrand.com/news/all-blog-posts/10-tips-for-optimal-rotary-evaporation', 'accessed' => '2026-10-20'],
            ['label' => 'VACUUBRAND - How vacuum supports rotary evaporation', 'url' => 'https://www.vacuubrand.com/news/bloguebersicht/gentle-product-evaporation', 'accessed' => '2026-10-20'],
            ['label' => 'IKA - Rotary Evaporators brochure (RV 8 / RV 10)', 'url' => 'https://www.ika.com/ika/pdf/flyer-catalog/Rotary%20Evaporators_Brochure_EN_wop_screen.pdf', 'accessed' => '2026-10-20'],
            ['label' => 'Chemistry LibreTexts - 5.6: Rotary Evaporation', 'url' => 'https://chem.libretexts.org/Bookshelves/Organic_Chemistry/Organic_Chemistry_Lab_Techniques_(Nichols)/05:_Distillation/5.06:_Rotary_Evaporation', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'LECO',
        'title' => 'Nitrogen and Protein Analysis: Dumas Combustion vs Kjeldahl',
        'slug' => 'nitrogen-protein-analysis-dumas-combustion-vs-kjeldahl',
        'excerpt' => 'Both methods measure nitrogen and convert it to protein, but they differ in speed, reagents and results. A balanced look to help you choose and report correctly.',
        'seo_description' => 'Dumas combustion vs Kjeldahl for nitrogen and protein: how each works, time, hazardous chemicals, result differences and which to report.',
        'html' => <<<'HTML'
<p>Protein content on a food or feed label is rarely measured directly. Instead, laboratories measure total nitrogen and multiply by a conversion factor. Two methods dominate: the classical Kjeldahl method and the Dumas combustion method. This article compares them fairly, including where each has limits. It is general guidance, not a replacement for the standard that your customer or regulator specifies.</p>

<h2>How the Kjeldahl method works</h2>
<p>Kjeldahl was developed in 1883 and remained the standard for a very long time [1][3]. The sample is digested with concentrated sulfuric acid and a catalyst at high temperature (the digestion is described as running at roughly 360-410 &deg;C), which converts organic nitrogen into ammonium. Sodium hydroxide is then added, the ammonia is distilled into an acid trap, and the amount captured is determined by titration [4]. It measures organic and ammonia nitrogen and, in its basic form, does not capture nitrate or some ring-structured nitrogen compounds [3][4].</p>

<h2>How the Dumas (combustion) method works</h2>
<p>In the Dumas method the sample is burned at high temperature in oxygen. Nitrogen oxides are reduced to nitrogen gas, other combustion gases are trapped or separated, and a thermal conductivity detector measures the nitrogen [1][3]. LECO states that its FP828 and FP928 analysers give a result in under three minutes per sample on the FP828, and that sample masses up to 2 grams can be used [1][2]. There is no wet digestion step, so no acid digestion or distillation is needed [1][5].</p>

<h2>Time, chemicals and waste</h2>
<ul>
<li><strong>Time.</strong> A FOSS white paper puts Dumas at a few minutes per measurement against an hour or more for Kjeldahl [3]; LECO says a Kjeldahl run can take hours [1].</li>
<li><strong>Chemicals.</strong> Kjeldahl uses concentrated sulfuric acid, a catalyst, and strong base [4][5]. Wikipedia lists selenium and mercury-based catalysts among those used, and notes that titanium dioxide is the approved catalyst in AOAC protein methods [4]. LECO adds that the most efficient catalysts were phased out for safety and environmental reasons, which can lower nitrogen recovery if they are not replaced adequately [1].</li>
<li><strong>Automation and labour.</strong> Combustion analysers are easier to automate and need little operator input [3]. Both are vendor statements, so confirm with your own workload.</li>
</ul>

<h2>Do they give the same answer?</h2>
<p>Often close, but not identical. Dumas measures total nitrogen, including inorganic forms such as nitrate and nitrite, while Kjeldahl measures organic and ammonia nitrogen and does not fully recover some compounds. Because of this, Dumas results are usually a little higher [3][5]. In AAFCO feed proficiency samples quoted by FOSS, one chicken feed sample read 17.29% crude protein by Kjeldahl and 17.64% by Dumas, with comparable scatter between labs [3].</p>
<p>Neither method measures true protein: both register non-protein nitrogen, so adulterants rich in nitrogen can inflate the result [3][4].</p>

<h2>Conversion factors and official methods</h2>
<p>Nitrogen is converted to protein with a factor. A general factor of 6.25 is agreed for most foods and feeds, while ISO/TS 16634-2 gives 5.7 for wheat, rye and their milled products [3]. Using the same factor for both techniques is common practice, which is one reason differences between methods can appear [3].</p>
<p>Standards exist for both approaches. Dumas methods are listed in ISO, AOAC, AOCS and other standards for food and feed [3]. However, the same white paper notes that the European Commission confirmed Kjeldahl as the community method for official feed controls (Regulation (EC) No 152/2009), and where Kjeldahl is the official method Dumas can be used only if the differences for your sample type are negligible [3]. Check which method your customer or regulator names.</p>

<h2>Quick checklist</h2>
<ul>
<li>Confirm which method or standard the specification requires.</li>
<li>State the method and the conversion factor on every report.</li>
<li>Do not mix Kjeldahl and Dumas results in one trend without checking the offset for your matrix.</li>
<li>Consider nitrate-rich or unusual samples (such as vegetables), where results can differ more [3].</li>
<li>If you keep Kjeldahl, plan fume extraction, acid handling and waste disposal.</li>
<li>If you move to Dumas, run a comparison on your own typical samples first.</li>
</ul>

<h2>Conclusion</h2>
<p>Dumas combustion is faster and avoids wet-chemistry reagents, while Kjeldahl remains a recognised reference in many official settings. Neither is simply "better": the right choice depends on your samples, throughput, safety setup and what the specification demands. Before switching, validate on your own materials and report the method clearly.</p>
HTML,
        'sources' => [
            ['label' => 'LECO - Kjeldahl vs Dumas: Determining Protein', 'url' => 'https://www.leco.com/news/kjeldahl-vs-dumas-determining-protein/', 'accessed' => '2026-10-20'],
            ['label' => 'LECO - Protein Analysis Instruments for Nitrogen Determination', 'url' => 'https://www.leco.com/application/protein-analysis/', 'accessed' => '2026-10-20'],
            ['label' => 'FOSS white paper - Dumas or Kjeldahl for reference analysis? (2017)', 'url' => 'https://www.fossanalytics.com/-/media/files/documents/papers/laboratories-segment/the-dumas-method-for-nitrogenprotein-analysis_gb.pdf', 'accessed' => '2026-10-20'],
            ['label' => 'Wikipedia - Kjeldahl method', 'url' => 'https://en.wikipedia.org/wiki/Kjeldahl_method', 'accessed' => '2026-10-20'],
            ['label' => 'VELP - Dumas and Kjeldahl method comparison: protein determination in feed', 'url' => 'https://www.velp.com/en-ww/dumas-and-kjeldahl-method-comparison-protein-determination-in-feed.aspx', 'accessed' => '2026-10-20'],
        ],
    ],
    [
        'brand' => 'Labconco',
        'title' => 'Fume Hood, Biosafety Cabinet or Laminar-Flow Cabinet: What Is the Difference?',
        'slug' => 'fume-hood-biosafety-cabinet-laminar-flow-cabinet-difference',
        'excerpt' => 'They look alike but protect different things. Learn what a fume hood, a biosafety cabinet and a clean bench each protect, and why choosing wrongly is risky.',
        'seo_description' => 'Fume hood, biosafety cabinet and laminar-flow clean bench compared: airflow, HEPA filtration and whether they protect person, product or environment.',
        'html' => <<<'HTML'
<p>Fume hoods, biosafety cabinets and laminar-flow clean benches are all enclosures with moving air, so they are easy to confuse. But each is built to protect something different. Using the wrong one can put people at risk or ruin a sterile process [1][4]. This guide explains the differences in plain terms. It is general guidance; your safety officer or biosafety committee should make the final choice for your work.</p>

<h2>Start with the question: what needs protecting?</h2>
<p>There are three possible things to protect: the person working, the product or sample, and the surrounding environment. Labconco's guide frames the three devices around exactly this question [1].</p>
<table>
<thead><tr><th>Device</th><th>Protects person</th><th>Protects product</th><th>Protects environment</th><th>HEPA filtration</th></tr></thead>
<tbody>
<tr><td>Chemical fume hood</td><td>Yes (from chemical fumes)</td><td>No</td><td>Exhausted outside</td><td>No</td></tr>
<tr><td>Biosafety cabinet (Class II)</td><td>Yes</td><td>Yes</td><td>Yes</td><td>Yes (supply and exhaust)</td></tr>
<tr><td>Laminar-flow clean bench</td><td>No</td><td>Yes</td><td>No</td><td>Yes (supply air)</td></tr>
</tbody>
</table>

<h2>Chemical fume hood</h2>
<p>A fume hood is a ventilated, enclosed workspace that captures and exhausts harmful chemical fumes, vapours and particulate matter. Air is drawn away from the worker at the front opening, across the work surface and out through ductwork to be released outdoors [1][4]. There is no HEPA filtration of intake or exhaust air, so it is suited to chemical work where sterility does not matter, and it should not be used where the sterility of the product is a concern [4]. Basic habits include keeping airflow undisturbed, not storing large equipment inside, never putting your head inside, and keeping the sash closed as much as possible [1].</p>

<h2>Biosafety cabinet</h2>
<p>A biosafety cabinet (BSC) uses HEPA filters on intake and exhaust air and protects the worker, the product and the environment from particulates and aerosols such as bacteria and viruses [1][4]. In a Class II cabinet, the most common type, room air is drawn in at the front to protect the operator, HEPA-filtered air flows downward over the work surface to protect the product, and the exhaust is HEPA filtered before it is recirculated or sent outside [1]. A university safety guide notes that in many BSCs about 70% of the air is recirculated into the room, which cleans the air of infectious aerosols but does not remove chemical vapours or gases [4].</p>
<p>Class II cabinets have several types. Type A2 is the most common and recirculates part of its air; Type B2 is fully ducted and exhausts all air after HEPA filtration, which suits work involving limited volatile chemicals; Type C1 can run in either A or B mode [2]. Labconco notes that cabinet classifications and use parameters are defined by the CDC's BMBL and by NSF/ANSI 49, the North American biosafety cabinet standard [2]. Never assume a standard BSC can replace a fume hood for chemical vapours [1][4].</p>

<h2>Laminar-flow clean bench</h2>
<p>A clean bench passes HEPA-filtered air over the work surface in a smooth, uniform stream (laminar flow). In a horizontal bench the air moves back to front, towards the operator; in a vertical bench it moves from top to bottom [1][3]. It protects the product only. Because air can blow towards the worker, it offers no personnel protection and should be used only for non-hazardous work such as media preparation or non-infectious tissue culture [1][4]. It must never be used with infectious materials, toxins or volatile chemicals [4].</p>

<h2>Why the wrong choice matters</h2>
<ul>
<li>Using a BSC for chemical vapours, or a fume hood for work that needs a sterile product, can compromise safety and contaminate processes [1].</li>
<li>A clean bench used with a hazardous sample can expose the user to it [4].</li>
<li>Obstructions or items left inside an enclosure can disturb airflow and cause turbulence [3].</li>
</ul>

<h2>Quick checklist</h2>
<ul>
<li>Write down what the work involves: chemicals, biological agents, or a sterile product only.</li>
<li>Chemical vapours: choose a fume hood.</li>
<li>Infectious or biological material: choose a biosafety cabinet of the right class and type.</li>
<li>Sterile, non-hazardous product only: a clean bench may be enough.</li>
<li>Work with both chemicals and biological agents: ask your safety officer, since only some cabinet types are suitable [1][2].</li>
<li>Do not clutter the enclosure or block airflow.</li>
<li>Have cabinets and hoods tested and certified as required.</li>
</ul>

<h2>Conclusion</h2>
<p>Think of the three devices as answering different questions: the fume hood keeps chemicals away from you, the biosafety cabinet keeps biology contained while also keeping the sample clean, and the clean bench keeps the sample clean but gives you no protection. Match the enclosure to the hazard, and involve your biosafety or chemical safety officer when in doubt.</p>
HTML,
        'sources' => [
            ['label' => 'Labconco - What\'s the Difference Between a Fume Hood, Biosafety Cabinet, and Laminar Airflow Bench?', 'url' => 'https://www.labconco.com/articles/whats-the-difference-between-a-fume-hood-and-a-b', 'accessed' => '2026-10-20'],
            ['label' => 'Labconco - What Are the Biosafety Cabinet Classes?', 'url' => 'https://www.labconco.com/articles/what-are-the-biosafety-cabinet-classes', 'accessed' => '2026-10-20'],
            ['label' => 'Labconco - Laminar Flow in the Laboratory: What You Need to Know', 'url' => 'https://www.labconco.com/articles/laminar-flow-in-the-laboratory', 'accessed' => '2026-10-20'],
            ['label' => 'TCU Environmental Health & Safety - Laboratory Hoods: What\'s the Difference?', 'url' => 'https://ehs.tcu.edu/biological/docs/tcu-lab-hood-differences.pdf', 'accessed' => '2026-10-20'],
        ],
    ],
];
