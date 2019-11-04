<?php

const ADD = 'ADD';
const MOVE = 'MOVE';
const INACTIVATE = 'INACTIVATE';
const DELETE = 'DELETE';
const RENAME = 'RENAME';
const NOTE = 'NOTE';

class Action {
    public $action;
    public $desc;
    public $hazard;
    public function __construct($action, $desc, $hazard = null){
        $this->action = $action;
        $this->desc = $desc;
        $this->hazard = $hazard;
    }

    public function __toString(){
        return "$this->action: " . ($this->hazard != null ? "($this->hazard) " : '') . "$this->desc";
    }
}

$KNOWN_ACTIONS = array(
    // Copy of HF which jocelyn created
    10949 => array(new Action(INACTIVATE, "(Unused Copy of HF)")),

    /*vvv Fume Hoods vvv*/
    10331 => array(
        new Action(ADD, "Benchtop, ducted for routine chemicals"),
        new Action(ADD, "Special-use hoods")
    ),
    10882 => array( new Action(MOVE, "Special-use hoods") ),
    10523 => array( new Action(MOVE, "Special-use hoods") ),
    10456 => array( new Action(MOVE, "Special-use hoods") ),
    10457 => array( new Action(MOVE, "Special-use hoods") ),
    10881 => array( new Action(MOVE, "Special-use hoods") ),
    10880 => array( new Action(DELETE, "JL Request Delete", "Routine chemical use hood") ),
    /*^^^ Fume Hoods ^^^*/

    // Compressed Gasses
    10289 => array(
        new Action(ADD, "Simple Asphyxiant"),
    ),

    10290 => array( new Action(MOVE, "Simple Asphyxiant")),
    10291 => array( new Action(MOVE, "Simple Asphyxiant")),
    10292 => array( new Action(MOVE, "Simple Asphyxiant")),
    10293 => array( new Action(MOVE, "Simple Asphyxiant")),
    10678 => array( new Action(MOVE, "Simple Asphyxiant")),
    10867 => array( new Action(DELETE, "JL Request Delete", "Tetraflouromethane")),
    10982 => array( new Action(DELETE, "JL Request Delete", "Nitrous oxide")),

    // High-hazard gasses
    10675 => array( new Action(MOVE, "Compressed Gasses", 10289) ),

    // High-hazard liquids and solids 
    10676 => array(
        new Action(MOVE, "Chemical and Physical Hazards", 10009),
        new Action(ADD, "Fire Hazard"),
        new Action(ADD, "Self-heating", "Fire Hazard"),

        new Action(ADD, "Health Hazard"),
        new Action(ADD, "Carcinogenicity", "Health Hazard"),
        new Action(ADD, "Mutagenicity", "Health Hazard"),
        new Action(ADD, "Reproductive toxicity", "Health Hazard"),
        new Action(ADD, "Target organ toxicity", "Health Hazard"),
        new Action(ADD, "Aspiration toxicity", "Health Hazard"),
        new Action(ADD, "Respiratory sensitizer", "Health Hazard"),
    ),

    // Fire Hazard
    10458 => array( new Action(MOVE, 'Fire Hazard')), // Oxidizers
    10438 => array( new Action(MOVE, 'Fire Hazard')), // Flammable liquid (5 gal size or bigger container or total volume exceeding 10 gal)
    10454 => array( new Action(MOVE, 'Fire Hazard')), // Flammable solid
    10453 => array( new Action(MOVE, 'Fire Hazard')), // Pyrophoric liquid
    10796 => array( new Action(MOVE, 'Fire Hazard')), // Pyrophoric solid
    10436 => array( new Action(MOVE, 'Fire Hazard')), // Peroxide formers
    10462 => array(
        new Action(MOVE, 'Fire Hazard'),
        new Action(RENAME, 'Emits flammable gas')
    ),                                                // Water Reactive
    10461 => array( new Action(MOVE, 'Fire Hazard')), // Self-reactives
    10680 => array( new Action(MOVE, 'Fire Hazard')), // Organic Peroxides
    10455 => array( new Action(MOVE, 'Fire Hazard')), // Explosives

    10848 => array( new Action(DELETE, 'JL Request Delete', 'Reactive with particular substances')),
    10849 => array( new Action(DELETE, 'JL Request Delete', 'Sodium azide')),

    // Health Hazard
    10452 => array(
        new Action(MOVE, 'Health Hazard'),
        new Action(RENAME, 'Acute toxicity (fatal)')
    ),                                                  // Acutely toxic
    10451 => array(
        new Action(INACTIVATE, 'Inactivate and split into several new hazards')
    ),                                                  // Carcinogen, mutagen, reproductive toxin, target organ toxin, aspiration toxin, respiratory sensitizer

    10975 => array( new Action(DELETE, "JL Request Delete", "Aniline")),
    11068 => array( new Action(DELETE, "JL Request Delete", "Aluminum Chloride")),
    10719 => array( new Action(DELETE, "JL Request Delete", "Arsenic and Arsenic Compounds")),
    11085 => array( new Action(DELETE, "JL Request Delete", "Arsenic (III) Oxide")),
    11077 => array( new Action(DELETE, "JL Request Delete", "Sodium Arsenate")),
    11078 => array( new Action(DELETE, "JL Request Delete", "Sodium Arsenite")),
    10718 => array( new Action(DELETE, "JL Request Delete", "Asbestos")),
    10970 => array( new Action(DELETE, "JL Request Delete", "Benzene")),
    11086 => array( new Action(DELETE, "JL Request Delete", "Beryllium Oxide")),
    11081 => array( new Action(DELETE, "JL Request Delete", "tert-Butyldimethylsilyl Chloride")),
    10977 => array( new Action(DELETE, "JL Request Delete", "Cadmium and cadmium compounds")),
    11090 => array( new Action(DELETE, "JL Request Delete", "Cadmium Bromide")),
    11088 => array( new Action(DELETE, "JL Request Delete", "Cadmium Chloride")),
    11089 => array( new Action(DELETE, "JL Request Delete", "Cadmium Fluoride")),
    11087 => array( new Action(DELETE, "JL Request Delete", "Cadmium Iodide")),
    11070 => array( new Action(DELETE, "JL Request Delete", "Cadmium Nitrate Tetrahydrate")),
    10972 => array( new Action(DELETE, "JL Request Delete", "Chloroform")),
    11069 => array( new Action(DELETE, "JL Request Delete", "Cobalt (II) Nitrate Hexahydrate")),
    10980 => array( new Action(DELETE, "JL Request Delete", "Chromium (VI) compounds")),
    11083 => array( new Action(DELETE, "JL Request Delete", "Copper and Copper Compounds")),
    11084 => array( new Action(DELETE, "JL Request Delete", "Copper (II) Acetate")),
    10973 => array( new Action(DELETE, "JL Request Delete", "Dichloromethane")),
    11072 => array( new Action(DELETE, "JL Request Delete", "N,N-Dimethylformamide")),
    10720 => array( new Action(DELETE, "JL Request Delete", "Formaldehyde/Paraformaldehyde/Formalin")),
    10991 => array( new Action(DELETE, "JL Request Delete", "Furan")),
    11073 => array( new Action(DELETE, "JL Request Delete", "Imidazole")),
    10978 => array( new Action(DELETE, "JL Request Delete", "Lead compounds")),
    11074 => array( new Action(DELETE, "JL Request Delete", "Lead (II) Nitrate")),
    10979 => array( new Action(DELETE, "JL Request Delete", "Nickel compounds")),
    11071 => array( new Action(DELETE, "JL Request Delete", "Nickel (II) Chloride Hexahydrate")),
    11075 => array( new Action(DELETE, "JL Request Delete", "Nickel Nitrate")),
    11076 => array( new Action(DELETE, "JL Request Delete", "Nitrilotriacetic Acid")),
    10974 => array( new Action(DELETE, "JL Request Delete", "Styrene")),
    10993 => array( new Action(DELETE, "JL Request Delete", "Tetrachloroethylene")),
    10985 => array( new Action(DELETE, "JL Request Delete", "Toluene")),
    10992 => array( new Action(DELETE, "JL Request Delete", "Trichloroethylene")),
    11079 => array( new Action(DELETE, "JL Request Delete", "Urethane")),
    11091 => array( new Action(DELETE, "JL Request Delete", "Carbon Black")),

    10679 => array(
        new Action(MOVE, 'Health Hazard'),
        new Action(RENAME, 'Skin corrosion/burns')
    ),                                                  // Corrosives
    10442 => array( new Action(MOVE, 'Health Hazard')), // Cryogen

    10714 => array( new Action(RENAME, "NFPA Class 3")),

    10677 => array( new Action(DELETE, "JL Request Delete", "USC-listed high-hazard chemicals")),

    // Mercury (usc-listed)
    10431 => array( new Action(MOVE, "Acute Toxicity (fatal)", 10452)),

    // HF (usc-listed)
    10429 => array( new Action(MOVE, "Target organ toxicity")),

    10798 => array( new Action(DELETE, "JL Request Delete", "tert-Butyl lithium")),
    10799 => array( new Action(DELETE, "JL Request Delete", "Acrolein")),
    10800 => array( new Action(DELETE, "JL Request Delete", "Sodium azide")),
    10802 => array( new Action(DELETE, "JL Request Delete", "Hydrogen chloride gas")),
    10803 => array( new Action(DELETE, "JL Request Delete", "Chlorine gas")),
    10878 => array( new Action(DELETE, "JL Request Delete", "Uranyl acetate")),
    10887 => array( new Action(DELETE, "JL Request Delete", "Dust particles")),

    // Gas-Water reactive => Gas-Self reactive
    10808 => array(
        new Action("RENAME", "Gas-Self reactive"),
    ),

    10809 => array(new Action(DELETE, "JL Requet Delete", "Dichlorosilane")),
    10869 => array(new Action(DELETE, "JL Requet Delete", "Tetraflourosilane")),

    // vvv High Hazard Equipment and Processes vvv
    10423 => array(
        new Action(ADD, "Equipment with mechanical hazards"),
        new Action(ADD, "Equipment with Stored Energy"),
        new Action(ADD, "Lithium ion battery", "Equipment with Stored Energy"),
        new Action(ADD, "Capacitor", "Equipment with Stored Energy"),
        new Action(ADD, "Equipment generating hazardous chemicals"),
        new Action(ADD, "Oil bath", "Unattended Operations posing flood or fire risk"),
        new Action(ADD, "Distillation", "Unattended Operations posing flood or fire risk"),
        new Action(ADD, "Digestion", "Unattended Operations posing flood or fire risk"),
    ),

    10449 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Lathe
    10450 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Machine Press

    11032 => array( new Action(MOVE, 'Equipment with Stored Energy')),      // Uninterrupted Power Supply (UPS)

    10446 => array( new Action(MOVE, 'Equipment generating hazardous chemicals')), // Ozone Generator
    10460 => array( new Action(MOVE, 'Equipment generating hazardous chemicals')), // Hydrogen Generator

    10894 => array( new Action(MOVE, 'Equipment generating hazardous chemicals')), // Mercury Generator
    10877 => array( new Action(MOVE, 'Equipment generating hazardous chemicals')), // Porosimeter

    10459 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Vaccum pump
    10945 => array( new Action(MOVE, "Unattended operations posing flood or fire risk")), // High Temperature oven
    10853 => array( new Action(MOVE, "Unattended operations posing flood or fire risk")), // High Temperature Furnace
    10885 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Cutter
    10886 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Grinder
    10946 => array( new Action(MOVE, "Unattended operations posing floor or fire risk")), // Temperature Chiller
    11043 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Table saw
    11044 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Drill press
    11045 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Undular plume
    11066 => array( new Action(DELETE, "JL Request Delete", "Welding to close glass ampules")),
    11104 => array( new Action(MOVE, "Equipment generating hazardous chemicals")), // Glove box
    11106 => array( new Action(MOVE, "Equipment with mechanical hazards")), // Robots

    // ^^^ High Hazard Equipment and Processes ^^^

    // High Voltage - dupes?
    10448 => array(new Action(DELETE, "JL Requet Delete", "High Voltage")),
    10563 => array(new Action(MOVE, 'High Hazard Equipment and Processes', 10423)),

    11054 => array( new Action(MOVE, "High Hazard Equipment and Processes", 10423)), // Unattended operations posing flood or fire risk
);
?>
