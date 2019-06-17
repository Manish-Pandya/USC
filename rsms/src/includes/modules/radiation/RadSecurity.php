<?php
class RadSecurity {
    public static function userCanViewRadPI($id, $rooms = null) {
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // TODO: Can this user view PI?
        if( !isset($_SESSION['USER']) ){
            // No user
            return false;
        }

        $user = $_SESSION['USER'];

        // User is Admin or Inspector
        if( CoreSecurity::userHasAnyRole($user, array('Admin', 'Radiation Admin'))){
            $LOG->debug("User is administrator");
            return true;
        }

        // Get the PI being requested
        $dao = new PrincipalInvestigatorDAO();
        $pi = $dao->getById($id);

        if( isset($pi) ){

            if($user->getKey_id() == $pi->getUser()->getKey_id()){
                // User is the PI
                $LOG->debug("User is PI #$id");
                return true;
            }

            // TODO: Check pi's authorization user list?

            // Is user one of the PI's lab personnel?
            foreach( $pi->getLabPersonnel() as $personnel ){
                if( $personnel->getKey_id() == $user->getKey_id() ){
                    $LOG->debug("User is a Lab Personnel of PI #$id");
                    return true;
                }
            }
        }

        return false;
    }

    public static function userCanViewPickup($id){
        return true;
    }
}
?>
