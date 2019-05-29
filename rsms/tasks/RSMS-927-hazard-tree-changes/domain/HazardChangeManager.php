<?php

const TASK_NUM = "RSMS-927";

class HazardChangeManager {
    private $meta;

    public function __construct(){
        $this->hazardDao = new GenericDAO(new Hazard());
        $this->appActionManager = new ActionManager();

        $this->meta = new stdClass();
        $this->meta->hazard = new Hazard();
        $this->meta->f_id = Field::create('key_id', 'hazard');
        $this->meta->f_name = Field::create('name', 'hazard');
        $this->meta->f_parent_hazard = Field::create('parent_hazard_id', 'hazard');

        // Processors
        $this->processors = array(
            ADD => new AddActionProcessor( $this->appActionManager, $this->meta ),
            MOVE => new MoveActionProcessor( $this->appActionManager, $this->meta ),
            INACTIVATE => new InactivateActionProcessor( $this->appActionManager, $this->meta ),
            DELETE => new DeleteActionProcessor( $this->appActionManager, $this->meta ),
            RENAME => new RenameActionProcessor( $this->appActionManager, $this->meta ),
            NOTE => null,
        );
    }

    public function process_actions( Array $actions ) {
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        $this->unresolved_actions = $actions;
        $this->repeatable_unresolved_actions = array();
        $this->resolved_actions = array();
        $this->failed_actions = array();

        $attempt = 1;
        while( !empty($this->unresolved_actions) && $attempt < 5 ){
            $LOG->info("Pass #$attempt through hazard-change actions. Unresolved Actions: " . count($this->unresolved_actions));
            $this->_process();
            $attempt++;
        }

        $LOG->info("Action-Processing completed. "
            . count($this->resolved_actions) . " were resolved "
            . count($this->unresolved_actions) . " left unresolved "
            . count($this->failed_actions) . " failed to resolve");

        if( !empty( $this->unresolved_actions )){
            $LOG->warn( count($this->unresolved_actions) . " Actions were unable to be resolved after $attempt attempts:");
            foreach($this->unresolved_actions as $a){
                $reason = '';
                if( isset($this->repeatable_unresolved_actions["$a"]) ){
                    $reason = $this->repeatable_unresolved_actions["$a"];
                }

                $LOG->warn("        [$a]: $reason");
            }
        }

        if( !empty( $this->failed_actions )){
            $LOG->warn( count($this->failed_actions) . " Actions cannot be resolved by HazardChange Actions:");
            foreach($this->failed_actions as $failed_action => $message){
                $LOG->error("        [$failed_action]: $message");
            }
        }

        if( empty($this->failed_actions) && empty($this->unresolved_actions) ){
            // All actions completed successfully!
            $LOG->info("All actions have been successfully performed");
            return true;
        }

        return false;
    }

    private function _process(){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        foreach($this->unresolved_actions as $idx => $action){
            GenericDAO::$_ENTITY_CACHE->flush();

            $result = $this->do_action($action);

            if( $result->success == false ){
                if( $result->repeatable == true ){
                    $LOG->debug("Retain action for re-attempt: $action");
                    $this->repeatable_unresolved_actions["$action"] = $result->message;
                }
                else {
                    // Action should not be attempted again, as it is impossible to resolve with additional Actions
                    unset( $this->unresolved_actions[$idx] );

                    // Use string version of action as key to reason it failed
                    $this->failed_actions["$action"] = $result->message;
                }
            }
            else {
                // Success!
                unset( $this->unresolved_actions[$idx] );
                $this->resolved_actions[] = $action;
            }
        }
    }

    public function do_action( Action &$action ){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        // Get processor
        $proc = $this->processors[ $action->type ] ?? null;

        $validation_result = $proc->validate( $action );
        if( $validation_result->success == true ){
            // Valid action - perform
            $action_result = $proc->perform( $action );

            $status = '  DONE  ';
            try {
                $proc->verify($action);
                $status = 'VERIFIED';
            }
            catch (Exception $e){
                $LOG->warn("Action Verification failed: {" . $e->getMessage() . "}");
            }

            $LOG->info("[$status] [$action] {$action_result->message}");
            return $action_result;
        }
        else {
            // Invalid action - skip
            $LOG->warn("[ INVALID] [$action] {$validation_result->message}");
            return $validation_result;
        }
    }
}

?>
