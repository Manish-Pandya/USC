<?php

class RenameActionProcessor extends A_ActionProcessor {
    const STAT_RENAME = "Renamed Hazards";

    function validate( A_HazardChangeAction &$action ): ActionProcessorResult {
        // Get the hazard to rename
        $hazard = $this->_get_hazard($action);

        if( !$hazard ){
            return new ActionProcessorResult(false, "Cannot rename [$action]: Hazard with ID #$action->hazard_id does not exist", false);
        }

        if( $hazard->getName() == $action->desc ){
            return new ActionProcessorResult(false, "Hazard with ID #$action->hazard_id already has target name '" . $hazard->getName() . "'", false, true);
        }

        return new ActionProcessorResult(true);
    }

    function perform( A_HazardChangeAction &$action ): ActionProcessorResult {
        // Get the hazard to rename
        $hazard = $this->_get_hazard($action);

        $old_name = $hazard->getName();
        $hazard->setName( $action->desc );

        // Save the hazard
        $savedHazard = $this->appActionManager->saveHazard( $hazard );
        $this->stat( self::STAT_RENAME, 1 );

        return new ActionProcessorResult(true, "Renamed Hazard: $savedHazard: '$old_name' => '" . $savedHazard->getName() . "'");
    }

    function verify( A_HazardChangeAction &$action ): bool {
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
