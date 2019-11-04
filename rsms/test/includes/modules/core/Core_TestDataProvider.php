<?php
class Core_TestDataProvider {

    /**
     * Creates roles if they do not exist
     */
    public static function create_named_roles( Array $roleNames ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        $roleDao = new GenericDAO(new Role());
        $roles = [];
        foreach($roleNames as $name){
            $role = QueryUtil::selectFrom(new Role())
                ->where(Field::create('name', 'role'), '=', $name)
                ->getOne();

            if( !$role ){
                $LOG->info("Creating role '$name'");
                $role = new Role();
                $role->setName( $name );
                $role = $roleDao->save($role);
            }

            $LOG->info("Role: $role");
            $roles[$name] = $role;
        }

        return $roles;
    }
}
?>
