<?php

require_once '../../src/Application.php';
require_once 'actions.php';

echo "<pre><code>";
echo "<ul>\n";
foreach($KNOWN_ACTIONS as $hazard_id => $actions){
    foreach($actions as $action){
        switch($action->action){
            case ADD: {
                $sub = $action->hazard != null ? ", \"$action->hazard\"" : '';
                echo "new AddAction( $hazard_id, \"$action->desc\" $sub);";
                break;
            }

            case MOVE: {
                $existing_id = $action->hazard != null ? ", $action->hazard" : '';
                echo "new MoveAction( $hazard_id, \"$action->desc\" $existing_id);";
                break;
            }

            case INACTIVATE: {
                echo "new InactivateAction( $hazard_id, \"$action->desc\" );";
                break;
            }
            case DELETE: {
                echo "new DeleteAction( $hazard_id, \"$action->desc\", \"$action->hazard\" );";
                break;
            }

            case RENAME: {
                echo "new RenameAction( $hazard_id, \"$action->desc\" );";
                break;
            }

            default: {
                echo "TODO: $action->action";
            }
        }

        echo "\n";
    }
}
echo "</ul>\n";
echo "</code></pre>";
?>
