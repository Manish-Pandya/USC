<?php
class AuthSecurity {

    public static function userIsCandidate(){
        if( isset($_SESSION) && isset($_SESSION['CANDIDATE']) ){
            return $_SESSION['CANDIDATE'] != null;
        }

        return false;
    }
}
?>
