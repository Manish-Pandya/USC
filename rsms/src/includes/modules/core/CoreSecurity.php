<?php
class CoreSecurity {

    public static function userIsAdmin( User &$user ){
        return CoreSecurity::userHasRoles($user, array('Admin'));
    }

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
}
?>
