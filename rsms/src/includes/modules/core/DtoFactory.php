<?php

class DtoFactory {

    public static function buildDto(GenericCrud &$object, Array $fields = array()){
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

    public static function departmentToDto($d){
        return DtoFactory::buildDto($d, array(
            'Name' => $d->getName())
        );
    }

    public static function buildingToDto($building){
        return DtoFactory::buildDto($building, array(
            'Name' => $building->getName(),
            'Campus_id' => $building->getCampus_id()
        ));
    }

    public static function roomToDto($room){
        return DtoFactory::buildDto($room, array(
            'Name' => $room->getName(),
            'Building_id' => $room->getBuilding_id()
        ));
    }

    public static function piToDto($pi){
        return DtoFactory::buildDto($pi, array(
            'Name' => $pi->getName()
        ));
    }

}

?>
