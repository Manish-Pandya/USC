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

        // User is a Rad User
        if( CoreSecurity::userHasAnyRole($user, array('Radiation User'))){
            // Get the PI being requested
            $dao = new PrincipalInvestigatorDAO();
            $pi = $dao->getById($id);

            if( isset($pi) ){
                if($user->getKey_id() == $pi->getUser()->getKey_id()){
                    // User is the PI
                    $LOG->debug("User is PI #$id");
                    return true;
                }

                // Check pi's authorization user list
                $piauthDao = new PIAuthorizationDAO();
                $authorizedUsers = $piauthDao->getAllAuthorizedUsersForPi( $pi->getKey_id() );
                foreach ( $authorizedUsers as $u ) {
                    if( $u->getKey_id() == $user->getKey_id() ){
                        $LOG->debug("User is authorized to view Rad Lab for PI #$id");
                        return true;
                    }
                }

                $LOG->warn("User is not authorized to view Rad Lab for PI #$id");
            }
            else {
                $LOG->warn("Requested PI #$id does not exist");
            }
        }
        else {
            $LOG->warn("User is not a Radiation User");
        }

        return false;
    }

    public static function userCanViewPickup($id){
        return true;
    }
}
?>
