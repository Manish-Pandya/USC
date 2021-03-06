<?php

const ADD = 'ADD';
const MOVE = 'MOVE';
const INACTIVATE = 'INACTIVATE';
const DELETE = 'DELETE';
const RENAME = 'RENAME';
const NOTE = 'NOTE';

const REORDER = 'REORDER';

class Action {
    public $type;
    public $hazard_id;
    public $desc;

    public function __construct($hazard_id, $type, $desc){
        $this->hazard_id = $hazard_id;
        $this->type = $type;
        $this->desc = $desc;
    }

    public function __toString(){
        return "$this->type #$this->hazard_id : $this->desc";
    }

    public function isPostAction(){
        return false;
    }
}

class AddAction extends Action {
    public $newHazardName;
    public $subParentName;
    public function __construct($hazard_id, $newHazardName, $subParentName = null, $desc = null){
        parent::__construct($hazard_id, ADD, $desc);

        $this->newHazardName = $newHazardName;
        $this->subParentName = $subParentName;
    }

    public function __toString(){
        return "Add new hazard '$this->newHazardName' to [#$this->hazard_id"
            . ($this->subParentName == null ? '' : " / '$this->subParentName'")
            . ']'
            . ($this->desc == null ? '' : " ($this->desc)");
    }
}

class MoveAction extends Action {
    public $targetName;
    public $targetId;
    public function __construct($hazard_id, $targetName, $targetId = null, $desc = null){
        parent::__construct($hazard_id, MOVE, $desc);

        $this->targetName = $targetName;
        $this->targetId = $targetId;
    }

    public function __toString(){
        return "Move hazard #$this->hazard_id to Parent '$this->targetName'"
            . ($this->targetId == null ? ' (NEW)' : " (Hazard #$this->targetId)")
            . ($this->desc == null ? '' : " ($this->desc)");
    }
}

class RenameAction extends Action {
    public function __construct($hazard_id, $newName){
        parent::__construct($hazard_id, RENAME, $newName);
    }
}

class InactivateAction extends Action {
    public function __construct($hazard_id, $reason){
        parent::__construct($hazard_id, INACTIVATE, $reason);
    }
}

class DeleteAction extends Action {
    public $hazard_name;
    public function __construct($hazard_id, $reason, $hazard_name){
        parent::__construct($hazard_id, DELETE, $reason);
        $this->hazard_name = $hazard_name;
    }

    public function __toString(){
        return "Delete hazard #$this->hazard_id ($this->hazard_name)"
            . ($this->desc == null ? '' : " - $this->desc");
    }
}

class ReorderAction extends Action {
    public function __construct($hazard_id, $desc = ''){
        parent::__construct($hazard_id, REORDER, $desc);
    }

    public function isPostAction(){
        return true;
    }
}
//////////////////

$KNOWN_ACTIONS = array(
    new InactivateAction( 10949, "(Unused Copy of HF)" ),
    new AddAction( 10331, "Benchtop, ducted for routine chemicals" ),
    new AddAction( 10331, "Special-use hoods" ),
    new MoveAction( 10882, "Special-use hoods" ),
    new MoveAction( 10523, "Special-use hoods" ),
    new MoveAction( 10456, "Special-use hoods" ),
    new MoveAction( 10457, "Special-use hoods" ),
    new MoveAction( 10881, "Special-use hoods" ),
    new DeleteAction( 10880, "JL Request Delete", "Routine chemical use hood" ),
    new AddAction( 10289, "Simple Asphyxiant" ),
    new MoveAction( 10290, "Simple Asphyxiant" ),
    new MoveAction( 10291, "Simple Asphyxiant" ),
    new MoveAction( 10292, "Simple Asphyxiant" ),
    new MoveAction( 10293, "Simple Asphyxiant" ),
    new MoveAction( 10678, "Simple Asphyxiant" ),
    new DeleteAction( 10867, "JL Request Delete", "Tetraflouromethane" ),
    new DeleteAction( 10982, "JL Request Delete", "Nitrous oxide" ),
    new RenameAction( 10422, "High Hazard liquids and solids"),
    new MoveAction( 10675, "Compressed Gasses" , 10289),
    new DeleteAction( 10676, "JL Request Delete", "High-hazard liquids and solids"),
    new MoveAction( 11094, "Hazardous consumer products", 10422),
    new MoveAction( 11161, "Other Environmental Toxin", 10422),
    new AddAction( 10422, "Fire Hazard" ),
    new AddAction( 10422, "Self-heating" , "Fire Hazard"),
    new AddAction( 10422, "Health Hazard" ),
    new AddAction( 10422, "Carcinogenicity" , "Health Hazard"),
    new AddAction( 10422, "Mutagenicity" , "Health Hazard"),
    new AddAction( 10422, "Reproductive toxicity" , "Health Hazard"),
    new AddAction( 10422, "Target organ toxicity" , "Health Hazard"),
    new AddAction( 10422, "Aspiration toxicity" , "Health Hazard"),
    new AddAction( 10422, "Respiratory sensitizer" , "Health Hazard"),
    new MoveAction( 10458, "Fire Hazard" ),
    new MoveAction( 10438, "Fire Hazard" ),
    new MoveAction( 10454, "Fire Hazard" ),
    new MoveAction( 10453, "Fire Hazard" ),
    new MoveAction( 10796, "Fire Hazard" ),
    new MoveAction( 10436, "Fire Hazard" ),
    new MoveAction( 10462, "Fire Hazard" ),
    new RenameAction( 10462, "Emits flammable gas" ),
    new MoveAction( 10461, "Fire Hazard" ),
    new MoveAction( 10680, "Fire Hazard" ),
    new MoveAction( 10455, "Fire Hazard" ),
    new DeleteAction( 10848, "JL Request Delete", "Reactive with particular substances" ),
    new DeleteAction( 10849, "JL Request Delete", "Sodium azide" ),
    new MoveAction( 10452, "Health Hazard" ),
    new RenameAction( 10452, "Acute toxicity (fatal)" ),

    new DeleteAction( 11051, "JL Request Delete - 20190531", "elemental mercury"),
    new DeleteAction( 11052, "JL Request Delete - 20190531", "inorganic mercury"),
    new DeleteAction( 11053, "JL Request Delete - 20190531", "organic mercury"),
    new DeleteAction( 11047, "JL Request Delete", "Mercury"),

    new DeleteAction( 10451, "JL Request Delete", "Other Health Hazards" ),
    new DeleteAction( 10975, "JL Request Delete", "Aniline" ),
    new DeleteAction( 11068, "JL Request Delete", "Aluminum Chloride" ),
    new DeleteAction( 10719, "JL Request Delete", "Arsenic and Arsenic Compounds" ),
    new DeleteAction( 11085, "JL Request Delete", "Arsenic (III) Oxide" ),
    new DeleteAction( 11077, "JL Request Delete", "Sodium Arsenate" ),
    new DeleteAction( 11078, "JL Request Delete", "Sodium Arsenite" ),
    new DeleteAction( 10718, "JL Request Delete", "Asbestos" ),
    new DeleteAction( 10970, "JL Request Delete", "Benzene" ),
    new DeleteAction( 11086, "JL Request Delete", "Beryllium Oxide" ),
    new DeleteAction( 11081, "JL Request Delete", "tert-Butyldimethylsilyl Chloride" ),
    new DeleteAction( 10977, "JL Request Delete", "Cadmium and cadmium compounds" ),
    new DeleteAction( 11090, "JL Request Delete", "Cadmium Bromide" ),
    new DeleteAction( 11088, "JL Request Delete", "Cadmium Chloride" ),
    new DeleteAction( 11089, "JL Request Delete", "Cadmium Fluoride" ),
    new DeleteAction( 11087, "JL Request Delete", "Cadmium Iodide" ),
    new DeleteAction( 11070, "JL Request Delete", "Cadmium Nitrate Tetrahydrate" ),
    new DeleteAction( 10972, "JL Request Delete", "Chloroform" ),
    new DeleteAction( 11069, "JL Request Delete", "Cobalt (II) Nitrate Hexahydrate" ),
    new DeleteAction( 10980, "JL Request Delete", "Chromium (VI) compounds" ),
    new DeleteAction( 11083, "JL Request Delete", "Copper and Copper Compounds" ),
    new DeleteAction( 11084, "JL Request Delete", "Copper (II) Acetate" ),
    new DeleteAction( 10973, "JL Request Delete", "Dichloromethane" ),
    new DeleteAction( 11072, "JL Request Delete", "N,N-Dimethylformamide" ),
    new DeleteAction( 10720, "JL Request Delete", "Formaldehyde/Paraformaldehyde/Formalin" ),
    new DeleteAction( 10991, "JL Request Delete", "Furan" ),
    new DeleteAction( 11073, "JL Request Delete", "Imidazole" ),
    new DeleteAction( 10978, "JL Request Delete", "Lead compounds" ),
    new DeleteAction( 11074, "JL Request Delete", "Lead (II) Nitrate" ),
    new DeleteAction( 10979, "JL Request Delete", "Nickel compounds" ),
    new DeleteAction( 11071, "JL Request Delete", "Nickel (II) Chloride Hexahydrate" ),
    new DeleteAction( 11075, "JL Request Delete", "Nickel Nitrate" ),
    new DeleteAction( 11076, "JL Request Delete", "Nitrilotriacetic Acid" ),
    new DeleteAction( 10974, "JL Request Delete", "Styrene" ),
    new DeleteAction( 10993, "JL Request Delete", "Tetrachloroethylene" ),
    new DeleteAction( 10985, "JL Request Delete", "Toluene" ),
    new DeleteAction( 10992, "JL Request Delete", "Trichloroethylene" ),
    new DeleteAction( 11079, "JL Request Delete", "Urethane" ),
    new DeleteAction( 11091, "JL Request Delete", "Carbon Black" ),
    new MoveAction( 10679, "Health Hazard" ),
    new RenameAction( 10679, "Skin corrosion/burns" ),
    new MoveAction( 10442, "Health Hazard" ),
    new RenameAction( 10714, "NFPA Class 3" ),
    new DeleteAction( 10677, "JL Request Delete", "USC-listed high-hazard chemicals" ),
    new MoveAction( 10431, "Acute Toxicity (fatal)" , 10452),
    new MoveAction( 10429, "Target organ toxicity" ),
    new DeleteAction( 10798, "JL Request Delete", "tert-Butyl lithium" ),
    new DeleteAction( 10799, "JL Request Delete", "Acrolein" ),
    new DeleteAction( 10800, "JL Request Delete", "Sodium azide" ),
    new DeleteAction( 10802, "JL Request Delete", "Hydrogen chloride gas" ),
    new DeleteAction( 10803, "JL Request Delete", "Chlorine gas" ),
    new DeleteAction( 10878, "JL Request Delete", "Uranyl acetate" ),
    new DeleteAction( 10887, "JL Request Delete", "Dust particles" ),
    new RenameAction( 10808, "Gas-Self reactive" ),
    new DeleteAction( 10809, "JL Request Delete", "Dichlorosilane" ),
    new DeleteAction( 10869, "JL Request Delete", "Tetraflourosilane" ),
    new RenameAction( 10424, "DEA Controlled Substances"),
    new AddAction( 10423, "Equipment with mechanical hazards" ),
    new AddAction( 10423, "Equipment with Stored Energy" ),
    new AddAction( 10423, "Lithium ion battery" , "Equipment with Stored Energy"),
    new AddAction( 10423, "Capacitor" , "Equipment with Stored Energy"),
    new AddAction( 10423, "Equipment generating hazardous chemicals" ),
    new AddAction( 10423, "Oil bath" , "Unattended Operations posing flood or fire risk", 11054),
    new AddAction( 10423, "Distillation" , "Unattended Operations posing flood or fire risk", 11054),
    new AddAction( 10423, "Digestion" , "Unattended Operations posing flood or fire risk", 11054),
    new MoveAction( 10449, "Equipment with mechanical hazards" ),
    new MoveAction( 10450, "Equipment with mechanical hazards" ),
    new MoveAction( 11032, "Equipment with Stored Energy" ),
    new MoveAction( 10446, "Equipment generating hazardous chemicals" ),
    new MoveAction( 10460, "Equipment generating hazardous chemicals" ),
    new MoveAction( 10894, "Equipment generating hazardous chemicals" ),
    new MoveAction( 10877, "Equipment generating hazardous chemicals" ),
    new MoveAction( 10459, "Equipment with mechanical hazards" ),
    new DeleteAction( 10945, "JL Request Delete", "High Temperature oven" ),
    new DeleteAction( 10853, "JL Request Delete", "High Temperature Furnace" ),
    new MoveAction( 10885, "Equipment with mechanical hazards" ),
    new MoveAction( 10886, "Equipment with mechanical hazards" ),
    new DeleteAction( 10946, "JL Request Delete", "Temperature Chiller" ),
    new MoveAction( 11043, "Equipment with mechanical hazards" ),
    new MoveAction( 11044, "Equipment with mechanical hazards" ),
    new MoveAction( 11045, "Equipment with mechanical hazards" ),
    new DeleteAction( 11066, "JL Request Delete", "Welding to close glass ampules" ),
    new MoveAction( 11104, "Equipment generating hazardous chemicals" ),
    new MoveAction( 11106, "Equipment with mechanical hazards" ),
    new MoveAction( 11108, "Equipment generating hazardous chemicals"),
    new DeleteAction( 10448, "JL Request Delete", "High Voltage" ),
    new MoveAction( 10563, "High Hazard Equipment and Processes" , 10423),
    new MoveAction( 11054, "High Hazard Equipment and Processes" , 10423),

    new DeleteAction(11166, "JL Request Delete: 20190705", "Guillotine"),
    new DeleteAction(11167, "JL Request Delete: 20190705", "UV Transilluminator"),
    new DeleteAction(11168, "JL Request Delete: 20190705", "Ultracentrifuge"),
    new DeleteAction(11169, "JL Request Delete: 20190705", "Cryostat / Microtome"),
    new DeleteAction(11170, "JL Request Delete: 20190705", "Schlenk Line"),
    new DeleteAction(11171, "JL Request Delete: 20190705", "Oil Bath"),
    new DeleteAction(11172, "JL Request Delete: 20190705", "3D Printing"),
    new DeleteAction(11173, "JL Request Delete: 20190705", "Drone"),
    new DeleteAction(11174, "JL Request Delete: 20190705", "Flight Simulator"),
    new DeleteAction(11175, "JL Request Delete: 20190705", "Cold Room"),
    new DeleteAction(11176, "JL Request Delete: 20190705", "Fiber Cutting"),

    new DeleteAction(11095, "JL Request Delete: 20190805", "Pesticides"),

    new ReorderAction( 10000, 'Reorder all leaf nodes')
);

?>
