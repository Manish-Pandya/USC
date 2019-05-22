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

require_once '../../src/Application.php';
require_once 'actions.php';

// Group known actions by hazard so we can display them below relevant ones
$ACTIONS_BY_HAZARD = array();
foreach($KNOWN_ACTIONS as $action){
    if( !array_key_exists($action->hazard_id, $ACTIONS_BY_HAZARD) ){
        $ACTIONS_BY_HAZARD[$action->hazard_id] = array();
    }

    $ACTIONS_BY_HAZARD[$action->hazard_id][] = $action;
}

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
    if( isset( $GLOBALS['ACTIONS_BY_HAZARD'][$id]) ){
        echo "<ol id='$id-actions' class='actions'>";
            foreach( $GLOBALS['ACTIONS_BY_HAZARD'][$id] as $action ){
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

