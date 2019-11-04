<?php
class InactivateActionProcessor extends A_ActionProcessor {
    const STAT_INACTIVATED = 'Inactivated Hazards';

    private function _get_hazard( InactivateAction &$action ){
        $hazard = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $action->hazard_id)
            ->getOne();

        return $hazard;
    }

    public function validate( Action &$action ): ActionProcessorResult {
        // Get hazard to inactivate
        $hazard = $this->_get_hazard($action);

        if( !$hazard ){
            return new ActionProcessorResult(false, "Hazard with ID #$action->hazard_id does not exist", false);
        }

        if( !$hazard->getIs_active() ){
            return new ActionProcessorResult(false, "Hazard $hazard is already inactive", false, true);
        }

        return new ActionProcessorResult(true);
    }

    public function perform( Action &$action ): ActionProcessorResult {
        // Get hazard to inactivate
        $hazard = $this->_get_hazard($action);

        // Inactivate
        $hazard->setIs_active( false );

        // TODO: Also Inactivate all ancestors
        // Note that ActionManager does not recursively inactivate hazards, so we shouldn't here.

        $savedHazard = $this->appActionManager->saveHazard( $hazard );
        $this->stat( self::STAT_INACTIVATED, 1);

        return new ActionProcessorResult(true, "$savedHazard");
    }

    public function verify( Action &$action ): bool {
        // Get hazard to inactivate
        $hazard = $this->_get_hazard($action);
        if( $hazard->getIs_active() ){
            throw new Exception("Action did not result in inactivation of $hazard");
        }

        return true;
    }
}
?>
