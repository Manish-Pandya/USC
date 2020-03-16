<?php

//////////////////

$KNOWN_ACTIONS = array(
    // (11030) Purchase or transfer of transgenic rodents
    new DeleteAction(11030, 'Removed purchase or transfer of transgenic rodents from Exempt Experiments', 'Purchase or transfer of transgenic rodents'),

    //=================
    // (10285) RG1
    new AddAction(10285, 'Bacterial Agents'),
        new MoveAction(11002, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11002) Algae bacteria
        new MoveAction(10615, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10615) Bacillus cereus
        new MoveAction(10525, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10525) Bacillus licheniformis
        new MoveAction(10353, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10353) Bacillus subtilis
        new MoveAction(10818, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10818) Caulobacter crescentus
        new MoveAction(11128, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11128) Corynebacterium jeikeium
        new MoveAction(11124, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11124) Enhydrobacter aerosaccus
        new MoveAction(10351, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10351) Escherichia coli (nonpathogenic strains)
        new MoveAction(11149, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11149) Eubacterium limosum
        new MoveAction(11152, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11152) Fusobacterium ulcerans
        new MoveAction(10839, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10839) Geobacter sp.
        new MoveAction(11035, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11035) Halomonas gomseomensis
        new MoveAction(11125, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11125) Lactobacillus acidophilus
        new MoveAction(10829, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10829) Lysobacter sp.
        new MoveAction(11033, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11033) Marinobacter hydrocarbonoclasticus
        new MoveAction(11123, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11123) Micrococcus luteus
        new MoveAction(10830, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10830) Nonomuraea sp.
        new MoveAction(11148, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11148) Parabacteroides johnsonii
        new MoveAction(11146, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11146) Paraprevotella xylaniphila
        new MoveAction(11151, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11151) Phascolarctobacterium succinatutens
        new MoveAction(11127, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11127) Propionibacterium acnes
        new MoveAction(11118, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11118) Pseudomonas chlororaphis
        new MoveAction(11222, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11222) Rhizobium radiobacter
        new MoveAction(11150, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11150) Ruthenibacterium lactatiformans
        new MoveAction(10840, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10840) Shewanella oneidensis MR-1
        new MoveAction(11126, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11126) Staphylococcus epidermidis
        new MoveAction(11122, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11122) Staphylococcus hominis
        new MoveAction(11145, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11145) Streptomyces coelicolor
        new MoveAction(10831, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (10831) Streptomyces sp.
        new MoveAction(11220, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11220) Vibrio fischeri
        new MoveAction(11034, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11034) Vibrio gazogenes
        new MoveAction(11221, [10285, 'Bacterial Agents'], NULL, 'RG1/Bacterial'),   // (11221) Vibrio harveyi

    new AddAction(10285, 'Viruses'),
        new MoveAction(10526, [10285, 'Viruses'], NULL, 'RG1/Viruses'),  // (10526) Adeno-associated virus
        new MoveAction(10637, [10285, 'Viruses'], NULL, 'RG1/Viruses'),  // (10637) Baculovirus

    new AddAction(10285, 'Fungal Agents'),
        new MoveAction(10608, [10285, 'Fungal Agents'], NULL, 'RG1/Fungal'), // (10608) Aspergillus niger
        new MoveAction(10609, [10285, 'Fungal Agents'], NULL, 'RG1/Fungal'), // (10609) Aspergillus parasiticus
        new MoveAction(10833, [10285, 'Fungal Agents'], NULL, 'RG1/Fungal'), // (10833) Pichia pastoris
        new MoveAction(10352, [10285, 'Fungal Agents'], NULL, 'RG1/Fungal'), // (10352) Saccharomyces cerevisiae
        new MoveAction(10527, [10285, 'Fungal Agents'], NULL, 'RG1/Fungal'), // (10527) Saccharomyces uvarum

    new AddAction(10285, 'Parasitic Agents'),

    //=================
    // (10284) RG2
    new AddAction(10284, 'Bacterial Agents'),
        new MoveAction(11101, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11101) Acinetobacter baumannii
        new MoveAction(11131, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11131) Acinetobacter spp.
        new MoveAction(10425, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10425) Actinobacillus
        new MoveAction(11155, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11155) Alistipes senegalensis
        new MoveAction(11157, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11157) Bacteroides uniformis
        new MoveAction(11156, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11156) Bacteroides vulgatus
        new MoveAction(11133, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11133) Campylobacter spp.
        new MoveAction(11110, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11110) Chromobacterium violaceum
        new MoveAction(11065, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11065) Clostridium difficile
        new MoveAction(10645, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10645) Corynebacterium
        new MoveAction(10614, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10614) Enterobacter aerogenes
        new MoveAction(11102, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11102) Enterococcus faecium
        new MoveAction(11223, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11223) Escherichia coli (MDR)
        new MoveAction(11119, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11119) Escherichia coli 0157:H7
        new MoveAction(11099, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11099) Escherichia coli 086B7
        new MoveAction(11063, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11063) Fecal coliform (genera in feces)
        new MoveAction(10354, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10354) Francisella tularensis (holarctica LVS)
        new MoveAction(10509, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10509) Helicobacter pylori
        new MoveAction(10355, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10355) Klebsiella pneumoniae
        new MoveAction(11224, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11224) Klebsiella pneumoniae (MDR)
        new MoveAction(11130, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11130) Klebsiella spp.
        new MoveAction(10356, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10356) Listeria
        new MoveAction(10644, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10644) Listeria monocytogenes (live attenuated)
        new MoveAction(10357, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10357) Mycobacterium bovis BCG vaccine strain
        new MoveAction(10358, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10358) Mycobacterium marinum
        new MoveAction(11135, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11135) Mycobacterium spp.
        new MoveAction(11153, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11153) Parabacteroides distasonis
        new MoveAction(11154, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11154) Parabacteroides goldsteinii
        new MoveAction(11158, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11158) Propionibacterium propionicum
        new MoveAction(10611, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10611) Proteus mirabilis
        new MoveAction(10612, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10612) Proteus vulgaris
        new MoveAction(11100, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11100) Providencia alcalifaciens
        new MoveAction(10359, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10359) Pseudomonas aeruginosa
        new MoveAction(11200, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11200) Pseudomonas aeruginosa (MDR)
        new MoveAction(11132, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11132) Pseudomonas spp.
        new MoveAction(10607, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10607) Salmonella enterica
        new MoveAction(10360, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10360) Salmonella typhimurium
        new MoveAction(10361, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10361) Staphylococcus aureus
        new MoveAction(11225, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11225) Staphylococcus aureus (MDR)
        new MoveAction(10613, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10613) Staphylococcus aureus (MRSA)
        new MoveAction(11134, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11134) Staphylococcus spp.
        new MoveAction(10646, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10646) Streptococcus anginosus
        new MoveAction(10428, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10428) Streptococcus pneumoniae
        new MoveAction(10362, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10362) Streptococcus pyogenes
        new MoveAction(11136, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11136) Streptococcus spp.
        new MoveAction(11137, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (11137) Vibrio cholerae
        new MoveAction(10426, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10426) Vibrio parahaemolyticus
        new MoveAction(10363, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10363) Vibrio vulnificus
        new MoveAction(10427, [10284, 'Bacterial Agents'], NULL, 'RG2/Bacterial'),   // (10427) Yersinia enterocolitica

    new AddAction(10284, 'Viruses'),
        new MoveAction(10364, [10284, 'Viruses'], NULL, 'RG2/Viruses'),  // (10364) Coxsackie viruses types A and B
        new MoveAction(10643, [10284, 'Viruses'], NULL, 'RG2/Viruses'),  // (10643) Dengue virus serotypes 1, 2, 3, and 4
        new MoveAction(10489, [10284, 'Viruses'], NULL, 'RG2/Viruses'),  // (10489) Human papilloma virus
        new MoveAction(10488, [10284, 'Viruses'], NULL, 'RG2/Viruses'),  // (10488) Measles virus (attenuated)
        new MoveAction(11159, [10284, 'Viruses'], NULL, 'RG2/Viruses'),  // (11159) Vaccinia virus (Copenhagen)
        new MoveAction(10998, [10284, 'Viruses'], NULL, 'RG2/Viruses'),  // (10998) Zika virus

    new AddAction(10284, 'Fungal Agents'),
        new MoveAction(11008, [10284, 'Fungal Agents'], NULL, 'RG2/Fungal'),     // (11008) Aspergillus flavus
        new MoveAction(10828, [10284, 'Fungal Agents'], NULL, 'RG2/Fungal'),     // (10828) Aspergillus fumigatus
        new MoveAction(10480, [10284, 'Fungal Agents'], NULL, 'RG2/Fungal'),     // (10480) Cryptococcus neoformans

    new AddAction(10284, 'Parasitic Agents'),

    //=================
    // (103) Human-Derived Materials (Blood or OPIM)
    new AddAction(103, 'Human Cell Lines'),

    // MERGE Established + Immortalized (9x Immrotalized PIHR exist)
    new NoteAction(10294, "Merge with 'Immortalized cell lines' into 'Established cell lines'"), // (10294) Establinshed cell lines
    new NoteAction(10604, "Merge with 'Established cell lines' into 'Established cell lines'"), // (10604) Immortalized cell lines
    new MergeAction(10294, [10294, 10604], "Merge with 'Immortalized cell lines' into 'Established cell lines'"), //

    new MoveAction(10294, 'Human Cell Lines'),  // (10294) Establinshed cell lines
    new MoveAction(11015, 'Human Cell Lines'),  // (11015) Cancer cells
    new MoveAction(10295, 'Human Cell Lines'),  // (10295) Primary cell lines
    new MoveAction(10584, 'Human Cell Lines'),  // (10584) Stem cells

    //=================
    // (10466) Live Animals
    // (10466) / (11059) Fish
    new AddAction(11059, 'Minnows'),

    // Move Zebrafish to below Fish
    new MoveAction(10591, 'Fish', 11059),

    // Add subcategories to Pigs
    // (10469) Pigs
    new AddAction(10469, 'Inhalation Anesthetics in Pigs'),
    new AddAction(10469, 'Biological Agents in Pigs'),

    //=================
    // (10327) Other Biological Materials
    new AddAction(10327, 'Human-Derived Materials (Not Subject to BBP Standard)'),

    // MOVE all human materials in “Other Biological Materials” to this subcategory
    new MoveAction(11098, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human blastocoel fluid
    new MoveAction(11097, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human bone marrow
    new MoveAction(11103, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human bronchoalveolar lavage
    new MoveAction(11129, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human buccal swabs
    new MoveAction(10497, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human fecal samples
    new MoveAction(10502, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human finger nails
    new MoveAction(10507, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human foreskin
    new MoveAction(10498, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human hair
    new MoveAction(11144, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human nasal swabs
    new MoveAction(10500, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human saliva
    new MoveAction(11143, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human sputum
    new MoveAction(10481, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Human urine
    new MoveAction(10622, 'Human-Derived Materials (Not Subject to BBP Standard)'),   //Preserved human cadavers

    // MERGE (10338) Fixed human heart tissue and (10619) Fixed human tissue (slides) into one category -> “Fixed Human Tissue”
    new MergeAction(10338, [10338, 10619], "Merge 'Fixed human heart tissue' and 'Fixed human tissue (slides)'"),
    new RenameAction(10338, 'Fixed Human Tissue'),
    new MoveAction(10338, 'Human-Derived Materials (Not Subject to BBP Standard)'),

    //=================
    new AddAction(10327, 'Animal Blood, Tissue or Other Animal Products'),
    // MOVE all animal materials to this subcategory
    new MoveAction(11014, 'Animal Blood, Tissue or Other Animal Products'),  // Chicken embryos
    new MoveAction(10836, 'Animal Blood, Tissue or Other Animal Products'),  // Cow tissue
    new MoveAction(10616, 'Animal Blood, Tissue or Other Animal Products'),  // Fish tissue
    new MoveAction(10837, 'Animal Blood, Tissue or Other Animal Products'),  // Goat tissue
    new MoveAction(10485, 'Animal Blood, Tissue or Other Animal Products'),  // Guinea pig blood/tissue
    new MoveAction(10626, 'Animal Blood, Tissue or Other Animal Products'),  // Hamster cells
    new MoveAction(11160, 'Animal Blood, Tissue or Other Animal Products'),  // Horse blood
    new MoveAction(10479, 'Animal Blood, Tissue or Other Animal Products'),  // Established non-human primate cells
    new MoveAction(10838, 'Animal Blood, Tissue or Other Animal Products'),  // Rabbit tissue

    // MERGE (10958) Bird blood + (11025) Bird tissue -> 'Bird blood, tissue, other products'
    new MergeAction(10958, [10958, 11025], "Merge 'Bird blood', 'Bird tissue' into 'Bird blood, tissue, other products'"),
    new RenameAction(10958, 'Bird blood, tissue, other products' ),
    new MoveAction(10958, 'Animal Blood, Tissue or Other Animal Products'),    // Bird blood, tissue, other products

    // Separated canine and feline saliva into “Canine saliva” and “Feline saliva”
    new DeleteAction(11067, 'Separated canine and feline saliva into “Canine saliva” and “Feline saliva”', 'Feline and Canine saliva'),
    new AddAction(10327, 'Canine saliva', 'Animal Blood, Tissue or Other Animal Products', 'Separated canine and feline saliva into “Canine saliva” and “Feline saliva”'),
    new AddAction(10327, 'Feline saliva', 'Animal Blood, Tissue or Other Animal Products', 'Separated canine and feline saliva into “Canine saliva” and “Feline saliva”'),

    // DELETE (10624) Mouse cells, (10339) Rodent tissue, (10832) Rodent blood, (10625) Rat cells
    new DeleteAction(10624, 'Replace with Mouse/Rat hazards, remove separate rodent tissue/blood categories', 'Mouse cells'),
    new DeleteAction(10339, 'Replace with Mouse/Rat hazards, remove separate rodent tissue/blood categories', 'Rodent tissue'),
    new DeleteAction(10832, 'Replace with Mouse/Rat hazards, remove separate rodent tissue/blood categories', 'Rodent blood'),
    new DeleteAction(10625, 'Replace with Mouse/Rat hazards, remove separate rodent tissue/blood categories', 'Rat cells'),

    new AddAction(10327, 'Mouse Blood, Tissue or Other Products', 'Animal Blood, Tissue or Other Animal Products'),
    new MoveAction(10936, 'Mouse Blood, Tissue or Other Products'),  // (10936) Mouse fungal gut flora isolates

    new AddAction(10327, 'Rat Blood, Tissue or Other Products', 'Animal Blood, Tissue or Other Animal Products'),

    // MERGE (10935) Pig arteries, (10666) Pig cells, (10835) Pig tissue -> 'Pig Blood, Tissue or Other Products'
    new MergeAction(10935, [10935, 10666, 10835], "Merge 'Pig arteries', 'Pig cells', 'Pig tissue'"),
    new RenameAction(10935, 'Pig Blood, Tissue or Other Products' ),
    new MoveAction(10935, 'Animal Blood, Tissue or Other Animal Products'),  // Pig Blood, Tissue or Other Products

    //=================
    new AddAction(10327, 'Deceased Whole Animals'),
);

?>
