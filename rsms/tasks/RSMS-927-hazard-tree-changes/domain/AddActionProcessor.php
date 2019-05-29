<?php

class AddActionProcessor extends A_ActionProcessor {

    public function validate( Action &$action ): ActionProcessorResult {
        // Validate that parent hazard exists
        $parent = $this->_get_add_action_parent_hazard($action);

        if( !$parent ){
            // Invalid; parent doesn't exist, but has an expected ID. Disallow reattempt
            return new ActionProcessorResult(false, "Parent Hazard with id #$action->hazard_id does not exist", false);
        }

        // If action defines a sub-parent by name, validate that this parent already exists
        $subParent = $this->_get_add_action_subparent_hazard($action);
        if( isset($action->subParentName) && !$subParent ){
            // Invalid; parent doesn't exist. Allow reattempt in case parent gets added by later instruction
            return new ActionProcessorResult(false, "Child Hazard of #$action->hazard_id with name '$action->subParentName' does not exist", true);
        }

        $parent_id = $subParent ? $subParent->getKey_id() : $parent->getKey_id();

        // Validate that item has not yet been added
        $existing_added = $this->_get_target_new_hazard($action);

        if( $existing_added ){
            // Hazard has already been added
            return new ActionProcessorResult(false, "Item to add has already been added: $existing_added", false);
        }

        // Action is valid and can be performed
        return new ActionProcessorResult(true);
    }

    public function perform( Action &$action ): ActionProcessorResult {
        // Get dependencies
        $parent = $this->_get_add_action_parent_hazard($action);
        $subParent = $this->_get_add_action_subparent_hazard($action);

        $newHazard = new Hazard();
        $newHazard->setIs_active(true);
        $newHazard->setName($action->newHazardName);

        if( $subParent ){
            // Add to newly-created sub-hazard
            $newHazard->setParent_hazard_id( $subParent->getKey_id() );
        }
        else{
            // Add to pre-existing hazard
            $newHazard->setParent_hazard_id( $parent->getKey_id() );
        }

        // Save the new hazard
        $savedHazard = $this->appActionManager->saveHazard( $newHazard );
        return new ActionProcessorResult(true, "$savedHazard");
    }

    function verify( Action &$action ): bool {
        $hazard = $this->_get_target_new_hazard($action);
        if( $hazard == null ){
            throw new Exception("Action did not result in creation of hazard");
        }

        return true;
    }

    private function _get_target_new_hazard( AddAction &$action ){
        return QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_name, '=', $action->newHazardName)
            ->getOne();
    }

    private function _get_add_action_parent_hazard( AddAction &$action ){
        $parent = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $action->hazard_id)
            ->getOne();

        return $parent;
    }

    private function _get_add_action_subparent_hazard( AddAction &$action ){
        if( isset($action->subParentName) ){
            $subParent = QueryUtil::selectFrom($this->meta->hazard)
                ->where($this->meta->f_name, '=', $action->subParentName)
                ->where($this->meta->f_parent_hazard, '=', $action->hazard_id)
                ->getOne();

            return $subParent;
        }

        return null;
    }
}

?>
