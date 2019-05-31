<?php

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

            REORDER => new ReorderActionProcessor( $this->appActionManager, $this->meta)
        );
    }

    public function process_actions( Array $actions ) {
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        // Extract post-actions to deal with later
        $pre_actions  = array_filter( $actions, function($a){ return !$a->isPostAction(); } );
        $post_actions = array_filter( $actions, function($a){ return $a->isPostAction(); } );

        $completed_pre_actions = false;
        $completed_post_actions = false;

        // Set up result arrays
        $this->omitted_actions = array();
        $this->repeatable_unresolved_actions = array();
        $this->resolved_actions = array();
        $this->failed_actions = array();

        // Process pre-actions, allowing 5 attempts
        $LOG->info("Processing actions");
        $completed_pre_actions = $this->_process( $pre_actions, 5 );
        
        // Are all pre-actions done?
        if( $completed_pre_actions->success ){
            // Process Post actions
            $LOG->info("Processing post-actions");
            $completed_post_actions = $this->_process( $post_actions );
        }

        $completed_all_actions = $completed_pre_actions->success && $completed_post_actions->success;

        $LOG->info("Action-Processing completed. "
            . count($this->resolved_actions) . " were resolved | "
            . count($this->omitted_actions) . " were omitted | "
            . count($this->unresolved_actions) . " left unresolved | "
            . count($this->failed_actions) . " failed to resolve");

        $LOG->info("+--- Action Stats ----");
        foreach($this->processors as $proc){
            $LOG->info( '| ' . get_class($proc) . ': ' .  $proc->get_stats() );
        }
        $LOG->info("+---------------------");

        if( !empty( $this->omitted_actions )){
            $LOG->info( count($this->omitted_actions) . " Actions were not necessary to complete, and therefore omitted:");
            foreach($this->omitted_actions as $omitted_action => $message){
                $LOG->info("        [$omitted_action]: $message");
            }
        }

        if( !empty( $this->unresolved_actions )){
            $LOG->warn( count($this->unresolved_actions) . " Actions were unable to be resolved after $completed_pre_actions->attempts attempts:");
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

        if( $completed_all_actions ){
            // All actions completed successfully!
            $LOG->info("All actions have been successfully performed");
            return true;
        }
        else {
            $LOG->warn("Not all actions could be successfully performed. Review the log for additional information.");
            return false;
        }
    }

    private function _process( &$actions, $max_attempts = 1 ){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);
        $this->unresolved_actions = $actions;

        $attempt = 1;
        while( !empty($this->unresolved_actions) && $attempt <= $max_attempts ){
            $LOG->info("Pass #$attempt through hazard-change actions. Unresolved Actions: " . count($this->unresolved_actions));
            $this->_process_unresolved_actions();
            $attempt++;
        }

        $result = new stdClass();
        $result->success = empty($this->failed_actions) && empty($this->unresolved_actions);
        $result->attempts = $attempt;

        return $result;
    }

    private function _process_unresolved_actions(){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        foreach($this->unresolved_actions as $idx => $action){
            GenericDAO::$_ENTITY_CACHE->flush();

            $result = $this->do_action($action);

            if( $result->success == false ){
                if( $result->redundant == true ){
                    // Action is redundant, and is not necessary
                    $this->omit_action($idx, $action, $result->message);
                }
                else if( $result->repeatable == true ){
                    $LOG->debug("Retain action for re-attempt: $action");
                    $this->repeat_action($idx, $action, $result->message);
                }
                else {
                    // Action should not be attempted again, as it is impossible to resolve with additional Actions
                    $this->fail_action($idx, $action, $result->message);
                }
            }
            else {
                // Success!
                $this->resolve_action($idx, $action);
            }
        }
    }

    private function resolve_action( &$idx, &$action ){
        unset( $this->unresolved_actions[$idx] );
        $this->resolved_actions[] = $action;
    }

    private function omit_action( &$idx, &$action, $message = '' ){
        unset( $this->unresolved_actions[$idx] );
        $this->omitted_actions["$action"] = $message;
    }

    private function repeat_action( &$idx, &$action, &$message ){
        // To not remove from unresolved_actions
        $this->repeatable_unresolved_actions["$action"] = $result->message;
    }

    private function fail_action( &$idx, &$action, &$reason ){
        unset( $this->unresolved_actions[$idx] );

        // Use string version of action as key to reason it failed
        $this->failed_actions["$action"] = $reason;
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
                $action_result->success = false;
                $action_result->message = $e->getMessage();
                $status = ' FAILURE';
            }

            $LOG->info("[$status] [$action] {$action_result->message}");
            return $action_result;
        }
        else {
            // Invalid action - skip

            if( $validation_result->redundant == true ){
                // This should be omitted
                $LOG->info("[  OMIT  ] [$action] {$validation_result->message}");
            }
            else{
                $LOG->warn("[ INVALID] [$action] {$validation_result->message}");
            }

            return $validation_result;
        }
    }
}

?>
