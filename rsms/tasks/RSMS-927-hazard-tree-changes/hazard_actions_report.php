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

function code( $msg, $a ){
    echo $msg;
    echo wrap(var_dump($a));
}

function wrap( $val ){
    echo "<pre style='white-space:pre-wrap'><code>";
    echo $val;
    echo "</code></pre>";
}

const TASK_NUM = "TASKS.RSMS-927";

require_once '../../src/Application.php';
require_once 'actions.php';

require_once 'domain/HazardChangeManager.php';
require_once 'domain/A_ActionProcessor.php';
require_once 'domain/AddActionProcessor.php';
require_once 'domain/MoveActionProcessor.php';
require_once 'domain/InactivateActionProcessor.php';
require_once 'domain/DeleteActionProcessor.php';
require_once 'domain/RenameActionProcessor.php';
require_once 'domain/ReorderActionProcessor.php';

// Group known actions by hazard so we can display them below relevant ones
$ACTIONS_BY_HAZARD = array();
foreach($KNOWN_ACTIONS as $action){
    if( !array_key_exists($action->hazard_id, $ACTIONS_BY_HAZARD) ){
        $ACTIONS_BY_HAZARD[$action->hazard_id] = array();
    }

    $ACTIONS_BY_HAZARD[$action->hazard_id][] = $action;
}

function display_hazards( Array $hazards, $list_rules = true ){
    echo "<ul class='hazards'>";
    foreach ( $hazards as $hazard ){
        display_hazard_as_listitem($hazard, $list_rules);
    }
    echo "</ul>";
}

function display_hazard_as_listitem ( $hazard, $list_rules ){
    echo "<li>";

    // Details
    $id = $hazard->getKey_id();
    $cls = $hazard->getChecklist() != null ? 'class="checklist"' : '';

    echo "<a name='$id'></a><span $cls>". "(" . $hazard->getKey_id() . ")&nbsp;" . $hazard->getName() . "</span>";

    // Actions
    if( $list_rules ){
        if( isset( $GLOBALS['ACTIONS_BY_HAZARD'][$id]) ){
            echo "<ol id='$id-actions' class='actions'>";
                foreach( $GLOBALS['ACTIONS_BY_HAZARD'][$id] as $action ){
                    echo "<li>$action</li>";
                }
            echo "</ol>";
        }
    }

    // Children
    $children = $hazard->getActiveSubHazards();
    if( !empty($children) ){
        display_hazards($children, $list_rules);
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

$LOG = LogUtil::get_logger(TASK_NUM, __FILE__);

try {

    // Cache all hazards
    $dao = new GenericDAO(new Hazard());
    $ALL_HAZARDS = array();
    foreach( $dao->getAll() as $h ){
        $ALL_HAZARDS[$h->getKey_id()] = $h;
    }

    echo "<div class='legend'>";
        echo "<div>(ID) Hazard Name</div>";
        echo "<div class='checklist'>Checklist Exists</div>";
        echo "<div class='actions'>Action</div>";
    echo "</div>";

    echo "<div id='hazard-trees'>";

        $LOG->info("***START TRANSACTION***");
        DBConnection::get()->beginTransaction();

        echo "<div id='existing'>";
            echo "<h3>Existing Hazard Tree</h3>";
            $chem_phys_hazards = $dao->getById(10009);
            display_hazards( array($chem_phys_hazards) );
        echo "</div>";

        // TODO Attempt to approximate new tree

        $manager = new HazardChangeManager();
        $manager->process_actions($KNOWN_ACTIONS);

        DBConnection::get()->rollback();
        $LOG->info("***ROLLBACK TRANSACTION***");

        GenericDAO::$_ENTITY_CACHE->flush();

        echo "<div id='changed'>";
            echo "<h3>Updated Hazard Tree</h3>";
            $chem_phys_hazards = $dao->getById(10009);
            display_hazards( array($chem_phys_hazards), false );
        echo "</div>";


    echo "</div>";  // End hazard-trees div
}
catch(Exception $e){
    $LOG->error("An exception occured: $e");
    DBConnection::get()->rollback();
    $LOG->info("***ROLLBACK TRANSACTION***");
}
catch(Error $err){
    $LOG->error("An error occured: $err");
    DBConnection::get()->rollback();
    $LOG->info("***ROLLBACK TRANSACTION***");
    throw $err;
}

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

