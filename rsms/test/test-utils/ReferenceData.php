<?php
/**
 * Utility Class with Functions useful for setting up test data
 */
class ReferenceData {

    public static function create_hazard( ActionManager &$manager, $hazard_name, $parent_id = 1, $active = true){
        $hazard = new Hazard();
        $hazard->setIs_active( $active );
        $hazard->setName( $hazard_name );
        $hazard->setParent_hazard_id( $parent_id );
        return $manager->saveHazard($hazard);
    }

    public static function create_room( ActionManager &$manager, $room_name, $active = true){
        $room = new Room();
        $room->setIs_active( $active );
        $room->setName( $room_name );
        return $manager->saveRoom($room);
    }

    public static function create_user( ActionManager &$manager, $first_name, $last_name, $email, $active = true){
        $user = new User();
        $user->setIs_active( $active );
        $user->setFirst_name( $first_name );
        $user->setLast_name( $last_name );
        $user->setEmail( $email );
        return $manager->saveUser($user);
    }

    public static function create_pi( PrincipalInvestigatorDAO &$piDao, $user_id, $active = true){
        $pi = new PrincipalInvestigator();
        $pi->setIs_active($active);
        $pi->setUser_id($user_id);
        return $piDao->save($pi);
    }

    ////
    public static function assign_room( ActionManager &$manager, PrincipalInvestigator &$pi, Room &$room ){
        return $manager->savePIRoomRelation($pi->getKey_id(), $room->getKey_id(), true);
    }

    public static function assign_hazard( PrincipalInvestigatorHazardRoomRelationDAO &$pihr_dao, PrincipalInvestigator &$pi, Hazard &$hazard, Room &$room ){
        // Assign a hazard to the pi/room
        $pihr = new PrincipalInvestigatorHazardRoomRelation();
        $pihr->setHazard_id($hazard->getKey_id());
        $pihr->setPrincipal_investigator_id( $pi->getKey_id() );
        $pihr->setRoom_id( $room->getKey_id() );
        return $pihr_dao->save($pihr);
    }
}
?>
