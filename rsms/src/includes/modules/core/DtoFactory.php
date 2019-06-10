<?php

class DtoFactory {

    public static function buildDto(GenericCrud $object = null, Array $fields = array()){
        if( $object == null ){
            return $object;
        }

        $dto = new GenericDto($object->_jsonSerializeCrudFields($fields));

        return $dto;
    }

    public static function buildDtos(Array $objects, Callable $transformer){
        return array_map($transformer, $objects);
    }

    // Specific entity Factory methods
    // TODO: Refactor these into the entity classes
    //   It is more appropriate for each entity to define its own
    //   serializability

    public static function roleToDto($r){
        if( !isset($r) )
            return $r;

        return DtoFactory::buildDto($r, array(
            'Name' => $r->getName()
        ));
    }

    public static function departmentToDto($d){
        if( !isset($d) )
            return $d;

        return DtoFactory::buildDto($d, array(
            'Name' => $d->getName())
        );
    }

    public static function buildingToDto($building){
        if( !isset($building) )
            return $building;

        return DtoFactory::buildDto($building, array(
            'Name' => $building->getName(),
            'Campus_id' => $building->getCampus_id()
        ));
    }

    public static function roomToDto($room){
        if( !isset($room) )
            return $room;

        return DtoFactory::buildDto($room, array(
            'Name' => $room->getName(),
            'Building_id' => $room->getBuilding_id()
        ));
    }

    public static function piToDto($pi){
        if( !isset($pi) )
            return $pi;

        $data = array();
        $u = self::userToDto($pi->getUser());
        if( isset($u) ){
            $data['Name'] = $u->Name;
            $data['First_name'] = $u->First_name;
            $data['Last_name'] = $u->Last_name;
        }

        return DtoFactory::buildDto($pi, $data);
    }

    public static function userToDto($u){
        if( !isset($u) )
            return $u;

        return DtoFactory::buildDto($u, array(
            'Name' => $u->getName(),
            'First_name' => $u->getFirst_name(),
            'Last_name' => $u->getLast_name(),
            'Position' => $u->getPosition(),
            'Is_active' => $u->getIs_active()
        ));
    }
}

?>
