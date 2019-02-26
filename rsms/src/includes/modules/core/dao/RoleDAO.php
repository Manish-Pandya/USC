<?php

class RoleDAO extends GenericDAO {
    
    public function __construct(){
        parent::__construct(new PrincipalInvestigator());
    }

    public function getByName( $name ){
        $q = QueryUtil::selectFrom(new Role())
            ->where(Field::create('name', 'role'), '=', $name);

        return $q->getOne();
    }
}
?>
