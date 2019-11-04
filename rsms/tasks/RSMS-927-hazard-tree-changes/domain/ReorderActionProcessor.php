<?php
class ReorderActionProcessor extends A_ActionProcessor {
    const STAT_REORDERED = 'Reordered Hazards';

    private function _get_root_hazard( ReorderAction &$action ){
        $hazard = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $action->hazard_id)
            ->getOne();

        return $hazard;
    }

    public function validate( Action &$action ): ActionProcessorResult {
        // Get root hazard to reorder
        $hazard = $this->_get_root_hazard($action);

        if( !$hazard ){
            return new ActionProcessorResult(false, "Hazard with ID #$action->hazard_id does not exist", false);
        }

        $leafParents = array();
        $this->_walk( $hazard, $leafParents );

        if( empty($leafParents) ){
            return new ActionProcessorResult(false, "All leaves are alphabetized", false, true);
        }

        return new ActionProcessorResult(true);
    }

    public function perform( Action &$action ): ActionProcessorResult {
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        // Get root hazard
        $hazard = $this->_get_root_hazard($action);

        // Find all sub-hazards which have only leaves as children
        $leafParents = array();
        $this->_walk( $hazard, $leafParents );
        $LOG->debug("Found " . count($leafParents) . " hazards parent only to Leaf nodes");

        // Reorder children in all leafParents hazards
        $updated_hazards = array();
        foreach( $leafParents as $parent ){
            $LOG->trace("Reordering children of $parent");
            $updated_hazards = array_merge($updated_hazards, $this->_reorderChildren( $parent ));
        }

        // Save all updated hazards
        $hazardDao = new GenericDAO( new Hazard() );
        foreach ($updated_hazards as $h ){
            $hazardDao->save( $h );
            $LOG->trace("Saved $h");
        }

        $updated_cnt = count($updated_hazards);
        $this->stat( self::STAT_REORDERED, $updated_cnt);

        return new ActionProcessorResult(true, "Reordered $updated_cnt leaf hazards below $hazard");
    }

    private function _walk( &$parent, &$leafParents ){
        // Get the children of parent
        $children = $parent->getSubHazards();

        // Determine if all children are leaves
        $all_leaves = true;
        foreach( $children as $child ){
            if( !empty( $child->getSubHazards() ) ){
                // child is not a leaf
                $all_leaves = false;

                // Check the child
                $this->_walk( $child, $leafParents );
            }
        }

        if( $all_leaves == true ){
            // Check if children are already alphabetized
            if( !$this->appActionManager->getIsAlphabetized( $children ) ){
                // Leaves are out-of-order
                $leafParents[] = $parent;
                return true;
            }
        }

        return false;
    }

    public function fn_reorder_comparator( Hazard $h1, Hazard $h2){
        return strcasecmp( $h1->getName(), $h2->getName() );
    }

    private function _reorderChildren( Hazard &$hazard ){
        $children = $hazard->getSubHazards();

        // Order children alphabetically by name
        usort( $children, array($this, 'fn_reorder_comparator') );

        // Reassign order value of all children
        foreach( $children as $idx => $child ){
            $child->setOrder_index( $idx );
        }

        return $children;
    }

    public function verify( Action &$action ): bool {
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        // Find all leaf-parents and ensure their children are alphabetically ordered
        // Get hazard to inactivate
        $hazard = $this->_get_root_hazard($action);

        $leafParents = array();
        $this->_walk( $hazard, $leafParents );

        foreach( $leafParents as $parent ){
            $children = $parent->getSubHazards();

            if( !$this->appActionManager->getIsAlphabetized( $children ) ){
                $msg = "Action did not result in alphabetization of children of $parent";
                foreach( $children as $child){
                    $msg .= "\n\t" . $child->getName();
                }

                throw new Exception($msg);
            }
        }

        return true;
    }
}
?>
