<?php
class CoreSecurity {

    public static function userHasAnyRole(User &$user, Array $roleNames){
        return CoreSecurity::userHasRoles($user, $roleNames, false);
    }

    public static function userHasRoles(User &$user, Array $roleNames, $require_all=true){
        $userRoles = array_map(
            function($r){ return $r->getName(); }, $user->getRoles());
        $matches = array_intersect($roleNames, $userRoles);

        if( $require_all ){
            // All roles included
            return count($matches) == count($roleNames);
        }
        else {
            // Any role matched
            return count($matches) > 0;
        }
    }

    public static function userCanViewInspection($id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        // Get current user

        if( !isset($_SESSION['USER']) ){
            // No user
            return false;
        }

        $user = $_SESSION['USER'];

        // Is user admin or an Inspector?
        // FIXME: Centralize role names
        if( CoreSecurity::userHasAnyRole($user, array('Admin', 'Safety Inspector'))){
            // User is Admin or Inspector
            $LOG->debug("User is administrator or inspector");
            return true;
        }

        // Look up Inspection
        $dao = new GenericDAO(new Inspection());
        $inspection = $dao->getById($id);

        // Is user the assigned PI?
        if( $inspection->getPrincipalInvestigator()->getUser()->getKey_id() == $user->getKey_id()){
            $LOG->debug("User is assigned PI");
            return true;
        }

        // Is user assigned personnel?
        $personnel_ids = array_map( function($u){ return $u->getKey_id(); }, $inspection->getLabPersonnel());
        if( in_array( $user->getKey_id(), $personnel_ids) ){
            $LOG->debug("User is assigned Personnel");
            return true;
        }

        return false;
    }

    public static function userCanViewPI( $piId ){
        // TODO: Is user this PI, or a subordinate?
        return true;
    }

    public static function userCanSaveInspection(){
        // TODO: Is request body an inspection which this user can save?
        // CoreSecurity::userCanViewInspection( input->Key_id )
        return true;
    }

    public static function userCanSaveCorrectiveAction(){
        // TODO: Is request body a corrective action which this user can save?
        return true;
    }
}
?>
