<head>
    <style>
        .checklist { background: cyan; }

        #hazard-trees { display: flex; }

        ul.hazards > li {
            margin: 3px;
        }

        .actions {
            list-style: none;
            font-style: italic;
            color: red;
            width: fit-content;
        }

        .legend {
            padding: 5px;
            width: fit-content;
            background: lightgray;
            border: 1px solid;
        }
    </style>
</head>
<?php
require_once '../src/Application.php';

function code( $msg, $a ){
    echo $msg;
    echo wrap(var_dump($a));
}

function wrap( $val ){
    echo "<pre style='white-space:pre-wrap'><code>";
    echo $val;
    echo "</code></pre>";
}

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

const ADD = 'ADD';
const MOVE = 'MOVE';
const INACTIVATE = 'INACTIVATE';
const DELETE = 'DELETE';
const RENAME = 'RENAME';
const NOTE = 'NOTE';

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

function display_hazards( Array $hazards ){
    echo "<ul class='hazards'>";
    foreach ( $hazards as $hazard ){
        display_hazard_as_listitem($hazard);
    }
    echo "</ul>";
}

function display_hazard_as_listitem ( $hazard ){
    echo "<li>";

    // Details
    $id = $hazard->getKey_id();
    $cls = $hazard->getChecklist() != null ? 'class="checklist"' : '';

    echo "<a name='$id'></a><span $cls>". "(" . $hazard->getKey_id() . ")&nbsp;" . $hazard->getName() . "</span>";

    // Actions
    if( isset( $GLOBALS['KNOWN_ACTIONS'][$id]) ){
        echo "<ol id='$id-actions' class='actions'>";
            foreach( $GLOBALS['KNOWN_ACTIONS'][$id] as $action ){
                echo "<li>$action</li>";
            }
        echo "</ol>";
    }

    // Children
    $children = $hazard->getActiveSubHazards();
    if( !empty($children) ){
        display_hazards($children);
    }

    echo "</li>";
}

function &getHazard( $nameOrId ){
    if( is_numeric($nameOrId) ){
        return $GLOBALS['ALL_HAZARDS'][$nameOrId];
    }
    else if( array_key_exists($nameOrId, $GLOBALS['NEW_HAZARDS'])) {
        return $GLOBALS['NEW_HAZARDS'][$nameOrId];
    }
    else {
        $GLOBALS['LOG']->error("Cannot find hazard '$nameOrId'");
    }
}
/////////////////////////////////

$LOG = LogUtil::get_logger(__FILE__);
DBConnection::get()->beginTransaction();
try {

    // Cache all hazards
    $dao = new GenericDAO(new Hazard());
    $ALL_HAZARDS = array();
    foreach( $dao->getAll() as $h ){
        $ALL_HAZARDS[$h->getKey_id()] = $h;
    }

    // Get chem/phys root
    $chem_phys_hazards = &getHazard(10009);

    echo "<div class='legend'>";
        echo "<div>(ID) Hazard Name</div>";
        echo "<div class='checklist'>Checklist Exists</div>";
        echo "<div class='actions'>Action</div>";
    echo "</div>";

    echo "<div id='hazard-trees'>";

        echo "<div id='existing'>";
            echo "<h3>Existing Hazard Tree</h3>";
            display_hazards( array($chem_phys_hazards) );
        echo "</div>";

        // TODO Attempt to approximate new tree

        //////////////////////////
        // Mock-process actions
        $LOG->info("Mock-Processing Hazard Changes");

        // First, look for all NEW actions, and mock up a new hazard
        $NEW_HAZARDS = array();

        // Iterate the actions to process NEW ones
        $KEYCOUNT = 90000;

        $LOG->info("Processing NEW actions...");
        foreach($KNOWN_ACTIONS as $hazard_id => $actions){
            $hazard = &getHazard($hazard_id);

            $newActions = array_filter($actions, function($v, $k){
                return $v->action == ADD;
            }, ARRAY_FILTER_USE_BOTH);

            foreach($newActions as $action){
                $newHazard = new Hazard();
                $newHazard->setKey_id($KEYCOUNT++);
                $newHazard->setName( $action->desc );
            
                // Find parent hazard
                // If action supplies a sub-parent (via action->hazard_id), it is a previously-added hazard.
                $parent = &getHazard( $action->hazard_id ?? $hazard_id );
                $newHazard->setParent_hazard_id( $parent->getKey_id() );

                $subs = $parent->getActiveSubHazards();
                $subs[] = $newHazard;
                $parent->setSubHazards($subs);

                //
                $LOG->info("Added NEW Hazard '$action->desc' $newHazard TO PARENT $parent");
                $NEW_HAZARDS[$newHazard->getName()] = $newHazard;
                $GLOBALS['ALL_HAZARDS'][$newHazard->getKey_id()] = $newHazard;
            }
        }

        // Process Moves
        $LOG->info("Processing MOVE actions...");
        foreach($KNOWN_ACTIONS as $hazard_id => $actions){
            $hazard = &getHazard($hazard_id);

            $moveActions = array_filter($actions, function($v, $k){
                return $v->action == MOVE;
            }, ARRAY_FILTER_USE_BOTH);

            foreach($moveActions as $action){
                $currentParent = &getHazard($hazard->getParent_hazard_id());

                /* MOVE
                    desc: name of new parent
                    hazard: ID of new parent (if paren't isn't NEW)
                */
                // Find new parent by passing in either the ID or (new) Name
                $parent = &getHazard( $action->hazard ?? $action->desc );

                if( !isset($parent )) {
                    $LOG->error("Unable to match parent for $action");
                }

                // First remove hazard from existing parent
                $subs = $currentParent->getActiveSubHazards();

                $key = array_search($hazard, $subs, TRUE);
                if( $key !== FALSE ){
                    $LOG->info("REMOVE $hazard FROM $currentParent");
                    unset($subs[$key]);
                }

                $LOG->info("MOVE $hazard TO $parent");
                $hazard->setParent_hazard_id($parent->getKey_id());
                $newSubs = $parent->getActiveSubHazards() ?? array();
                $newSubs[] = $hazard;
                $parent->setSubHazards($newSubs);
            }
        }

        // Process RENAMES
        $LOG->info("Processing RENAME actions...");
        foreach($KNOWN_ACTIONS as $hazard_id => $actions){
            $hazard = &getHazard($hazard_id);

            $renameActions = array_filter($actions, function($v, $k){
                return $v->action == RENAME;
            }, ARRAY_FILTER_USE_BOTH);

            foreach($renameActions as $action){
                $LOG->info("RENAME $hazard TO '$action->desc'");
                $hazard->setName($action->desc);
            }
        }

        echo "<div id='changed'>";
            echo "<h3>Updated Hazard Tree</h3>";
            $chem_phys_hazards = &getHazard(10009);
            display_hazards( array($chem_phys_hazards) );
        echo "</div>";

    echo "</div>";  // End hazard-trees div

    // Describe all actions
    $LOG->info("Printing action details...");
    echo "<div id='actions'>";
        echo "<ul>";
        foreach($KNOWN_ACTIONS as $hazard_id => $actions){
            $hazard = &getHazard($hazard_id);
            $hazard_name = $hazard->getName();

            foreach($actions as $action){
                switch( $action->action){
                    case ADD: {
                        // Create a new hazard
                        // This should already have been processed
                        $created_hazard = $NEW_HAZARDS[$action->desc];
                        $target_newparent_name = $action->hazard;
                        $_subpath = ($target_newparent_name == null ? '' : " / $target_newparent_name");

                        $color = $created_hazard == null ? 'red' : 'green';
                        echo "<li style='color: $color'>Create a new Hazard '$action->desc' below Parent '$hazard_name' $_subpath</li>";
                        break;
                    }
                    case MOVE: {
                        // Moving a hazard
                        $target_hazard_id = $action->hazard;
                        if( is_numeric($target_hazard_id) ){
                            // Target is an existing hazard
                        }
                        else {
                            // Target is a new hazard
                        }

                        echo "<li>Change Parent of '$hazard_name' to $action->desc </li>";
                        break;
                    }
                    case RENAME: {
                        $color = $hazard_name != $action->desc ? 'red' : 'green';
                        echo "<li style='color:$color'>Change Name of '$hazard_name' to '$action->desc'</li>";
                        break;
                    }
                    case INACTIVATE: {
                        echo "<li>Inactivate hazard '$hazard_name'</li>";
                        break;
                    }
                    default:
                        echo "<li>UNRECOGNIZED ACTION '$action'</li>";
                }
            }
        }
        echo "</ul>";
    echo "</div>";
}
catch(Exception $e){
    $LOG->error($e);
}

$LOG->info("Rollback transaction.");
DBConnection::get()->rollback();

$LOG->info("Mock-processing done.");
/*

For each hazard movement (Hazard, OldParent, NewParent)
    Find hazard
    Find new parent

    // Validate assignment - assignments include all branches in a tree!
    // Walk up assignment tree, removing assignments which are no longer necessary
    // TODO: Ask Jocelyn about this - they may still want branch hazards selected
    //    even if leaves are unselected
    For each PI with hazard/room assignment
        Find OldParent assignment
        If assigned no other children of OldParent
            Delete OldParent assignment

*/


?>

