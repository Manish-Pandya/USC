<?php

class MoveActionProcessor extends A_ActionProcessor {
    const STAT_MOVED = 'Moved Hazards';
    const STAT_HAZARD_ASSIGNMENTS = 'Added Assignments';

    public function __construct( ActionManager &$actionManager, &$meta ){
        parent::__construct( $actionManager, $meta );

        $this->meta->pihrDao = new GenericDAO( new PrincipalInvestigatorHazardRoomRelation() );
    }

    public function validate( Action &$action ): ActionProcessorResult {
        // Validate that hazard and target-parent exist
        $hazard = $this->_get_move_action_hazard($action);
        if( !$hazard ){
            // Target hazard doesn't exist
            return new ActionProcessorResult(false, "Hazard with id #$action->hazard_id does not exist", false);
        }

        // Find the target-parent
        $target = $this->_get_move_action_target_hazard($action);
        if( !$target ){
            // Target parent doesn't exist. Allow reattempt in case a later process adds it
            return new ActionProcessorResult(false, "Target parent hazard does not exist", true);
        }

        // Validate that item has not yet been moved
        if( $hazard->getParent_hazard_id() == $target->getKey_id() ){
            return new ActionProcessorResult(false, "Hazard $hazard is already child of target $target", false, true);
        }

        // OK to move
        return new ActionProcessorResult(true);
    }

    public function perform( Action &$action ): ActionProcessorResult {
        $hazard = $this->_get_move_action_hazard($action);
        $target = $this->_get_move_action_target_hazard($action);
        $old_parent = $this->_get_hazard_by_id( $hazard->getParent_hazard_id() );

        $hazard->setParentIds(array());
        $old_tree = $hazard->getParentIds();

        // Reassign parent ID
        $hazard->setParent_hazard_id( $target->getKey_id() );

        // Save
        $savedHazard = $this->appActionManager->saveHazard( $hazard );
        $this->stat( self::STAT_MOVED, 1);

        $savedHazard->setParentIds(array());
        $new_tree = $savedHazard->getParentIds();

        // TODO: DIFF OLD vs NEW TREES
        $diff = '[' . implode('/', $old_tree) . '] => [' . implode('/', $new_tree) . ']';

        // Process assignment tree for this hazard
        // TODO: Remove assignments of old parent(s)?

        // Add assignments to new parent(s)
        $total_added = $this->addAssignments( $savedHazard );
        $this->stat( self::STAT_HAZARD_ASSIGNMENTS, $total_added );

        return new ActionProcessorResult(true, "Moved Hazard $savedHazard to $target | Added $total_added PI/Hazard/Room assignments | $diff" );
    }

    function verify( Action &$action ): bool {
        $hazard = $this->_get_move_action_hazard($action);
        $target = $this->_get_move_action_target_hazard($action);

        if( $hazard->getParent_hazard_id() != $target->getKey_id() ){
            throw new Exception("Action did not result in moving $hazard below $target");
        }

        return true;
    }

    private function addAssignments( Hazard &$node ){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        $parent_node = $this->_get_hazard_by_id( $node->getParent_hazard_id() );

        if( $parent_node->getParent_hazard_id() == 10000 ){
            // Don't assign top-level hazards
            return;
        }

        $LOG->debug("Add missing assignments for $node");

        // Get existing PI/Hazard/Room relations for this node
        $relations = $this->_get_pi_hazard_room_assignments($node->getKey_id());

        // Get existing PI/Hazard/Room relations for this node's Parent
        $parent_relations = $this->_get_pi_hazard_room_assignments($parent_node->getKey_id());

        // Find PI/Rooms who have the node relation but do not not have the parent relation
        $missing = array_udiff($relations, $parent_relations, function($a, $b){
            if( $a->getPrincipal_investigator_id() == $b->getPrincipal_investigator_id() ){
                if( $a->getRoom_id() == $b->getRoom_id() ){
                    return 0;
                }
        
                return $a->getRoom_id() - $b->getRoom_id();
            }
            else{
                return $a->getPrincipal_investigator_id() - $b->getPrincipal_investigator_id();
            }
        });

        // For each missing pi/room, add a relation to the parent
        foreach( $missing as $rel ){
            // Save a new relation for this PI/Room and the Parent Hazard
            $parent_rel = new PrincipalInvestigatorHazardRoomRelation();
            $parent_rel->setIs_active(true);
            $parent_rel->setPrincipal_investigator_id( $rel->getPrincipal_investigator_id() );
            $parent_rel->setRoom_id( $rel->getRoom_id() );
            $parent_rel->setHazard_id( $parent_node->getKey_id() );
            $parent_rel->setStatus( $rel->getStatus() );

            // SAVE
            $saved = $this->meta->pihrDao->save($parent_rel);
            $LOG->info("Assign new PI/Hazard/Room: $saved");
        }

        $count = count($missing);

        // Ascend up the tree until we reach the root
        $count += $this->addAssignments( $parent_node );
        return $count;
    }

    private function removeHazardAssignments( Hazard $node ){

    }


    private function _get_move_action_hazard( MoveAction &$action ){
        $hazard = $this->_get_hazard_by_id($action->hazard_id);
        return $hazard;
    }

    private function _get_move_action_target_hazard( MoveAction &$action ){
        if( $action->targetId ){
            // Pre-existing hazard specified; get-by-id
            $target = $this->_get_hazard_by_id($action->targetId);
            return $target;
        }
        else {
            // No target ID is specified, so target was new. get-by-name
            $target = QueryUtil::selectFrom($this->meta->hazard)
                ->where($this->meta->f_name, '=', $action->targetName)
                ->getOne();

            return $target;
        }
    }

    private function _get_hazard_by_id( $id ){
        return QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $id)
            ->getOne();
    }

    private function _get_pi_hazard_room_assignments( $hazard_id ){
        return QueryUtil::selectFrom( new PrincipalInvestigatorHazardRoomRelation() )
            ->where(Field::create('hazard_id', 'principal_investigator_hazard_room'), '=', $hazard_id)
            ->getAll();
    }
}

?>
