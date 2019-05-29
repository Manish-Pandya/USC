<?php

class RenameActionProcessor extends A_ActionProcessor {

    function validate( Action &$action ): ActionProcessorResult {
        // Get the hazard to rename
        $hazard = $this->_get_hazard($action);

        if( !$hazard ){
            return new ActionProcessorResult(false, "Cannot rename [$action]: Hazard with ID #$action->hazard_id does not exist", false);
        }

        if( $hazard->getName() == $action->desc ){
            return new ActionProcessorResult(false, "Hazard with ID #$action->hazard_id already has target name '" . $hazard->getName() . "'", false);
        }

        return new ActionProcessorResult(true);
    }

    function perform( Action &$action ): ActionProcessorResult {
        // Get the hazard to rename
        $hazard = $this->_get_hazard($action);

        $old_name = $hazard->getName();
        $hazard->setName( $action->desc );

        // Save the hazard
        $savedHazard = $this->appActionManager->saveHazard( $hazard );
        return new ActionProcessorResult(true, "Renamed Hazard: $savedHazard: '$old_name' => '" . $savedHazard->getName() . "'");
    }

    function verify( Action &$action ): bool {
        $hazard = $this->_get_hazard($action);
        if( $hazard->getName() != $action->desc ){
            throw new Exception("Action did not result in $hazard renamed to '$action->desc'");
        }

        return true;
    }

    private function _get_hazard( RenameAction &$action ){
        $hazard = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $action->hazard_id)
            ->getOne();

        return $hazard;
    }
}
?>
