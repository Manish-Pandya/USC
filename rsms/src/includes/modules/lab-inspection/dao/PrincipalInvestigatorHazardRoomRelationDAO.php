<?php
class PrincipalInvestigatorHazardRoomRelationDAO extends GenericDAO {
    public function __construct(){
        parent::__construct( new PrincipalInvestigatorHazardRoomRelation() );
    }

    function getHazardRoomDtosByPIId( $pIId, $roomIds = null ){
		$LOG = Logger::getLogger(__CLASS__);
        $LOG->info( "Get Hazard Rooms (" . ($roomIds == null ? '' : implode(', ', $roomIds)) . ") for PI #$pIId");

        //get this pi's rooms
        $sql = "SELECT
                room.key_id as room_id,
                room.building_id,
                room.name as room_name,
                COALESCE(NULLIF(b.alias, ''),b.name) as building_name
            from room room

            LEFT JOIN building b on room.building_id = b.key_id";

        // Prepare room-id predicate
        $roomIdPredicate = null;
        $bind_room_ids = null;
        if( $roomIds === null ){
            $roomIdPredicate = "WHERE room.key_id IN (
                select room_id from principal_investigator_room where principal_investigator_id = :id
            )";
            $bind_room_ids = false;
        }
        else {
            $inQuery = implode(',', array_fill(0, count($roomIds), '?'));
            $roomIdPredicate = "WHERE room.key_id IN ($inQuery)";
            $bind_room_ids = true;
        }

        // Add predicate to query
        $sql .= ' ' . $roomIdPredicate;

        // Prepare statement
        $stmt = DBConnection::prepareStatement($sql);

        // Bind room IDs, if specified
        if( $bind_room_ids ){
            foreach($roomIds as $key=>$id){
                $stmt->bindValue($key+1, $id, PDO::PARAM_INT);
            }
        }
        else {
            $stmt->bindParam(':id', $pIId, PDO::PARAM_INT);
        }

		if($stmt->execute()){
            $rooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PIHazardRoomDto");
        }else{
			// 'close' the statement
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
            $this->LOG->fatal('Returning QueryError with message: ' . $result->getMessage());

			$stmt = null;
            return $result;
		}

		// 'close' the statement
		$stmt = null;

        return $rooms;
    }

    public function getAllHazardDtos(){

		//get a dto for every hazard
        $queryString = "SELECT
                key_id as hazard_id,
                order_index,
                key_id,
                name as hazard_name,
                is_equipment,
                parent_hazard_id as parent_hazard_id,
                (
                    SELECT EXISTS(SELECT 1 from hazard where parent_hazard_id = hazard_id)
                ) as hasChildren
            from hazard WHERE is_active = 1;";

		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->execute();
		$dtos = $stmt->fetchAll(PDO::FETCH_CLASS, "HazardDto");

		// 'close' the statement
		$stmt = null;

        return $dtos;
    }

    /**
     * Applies the PI and Room IDs to each incoming DTO and collects Inspection/Room
     * details for each Hazard as it relates to the PI/Rooms
     *
     * @param int $piId Principal Investigator ID
     * @param Array $roomIds Array of Room key_id values
     * @param Array $hazardDtos Array of HazardDto
     * @param Array $pihrDtos Array of PIHazardRoomDto
     *
     * @return Array Modified $hazardDtos parameter
     */
    public function mergeHazardRoomDtos( int $piId, Array $roomIds, Array &$hazardDtos, Array &$pihrDtos){
        // TODO: Refactor this function out of the DAO

        // Post-process the queried DTOs
        // Take the incoming Hazard DTOs and apply the PI and Room IDs
		foreach($hazardDtos as $dto){
			$dto->setRoomIds($roomIds);
            $dto->setPrincipal_investigator_id($piId);

            // Clone each PIHR and apply the PI and Hazard IDs
			// (make a new collection of rooms so we won't pass a reference)
			$roomDtos = array();
			foreach($pihrDtos as $key=>$room){
				$roomDtos[] = clone $room;
				$roomDtos[$key]->setPrincipal_investigator_id($piId);
				$roomDtos[$key]->setHazard_id($dto->getHazard_id());
            }

            // Instruct the Hazard DTO to construct a collection of InspectionRoom DTOs
            $this->determineHazardStatus( $dto, $roomDtos );
		}
		return $hazardDtos;
    }

    public function getPIsAssignedInRooms( Array $roomIds ){
        ////////////////////////////////////////
        // Get PI assignments to the hazadDto's set of Room IDs
        $roomIds = implode (',', $roomIds);

        $queryString = "SELECT
                pi_room.principal_investigator_id,
                pi_room.room_id
            FROM principal_investigator_room pi_room
            LEFT JOIN principal_investigator pi
                ON pi_room.principal_investigator_id = pi.key_id
            WHERE pi.is_active = 1
                AND pi_room.room_id IN ( $roomIds )
            group by pi_room.principal_investigator_id";

		$stmt = DBConnection::prepareStatement($queryString);
        $stmt->execute();

        // Collect PI Ids per-room
        $pi_room_assignments = $stmt->fetchAll();

        /**
         * Array keyed by Room ID mapping to arrays of PI IDs
         *
         * [
         *   123  => [             // ROOM
         *     1, 2, 3, ...N       // PIs
         *   ],
         *   ...
         * ]
         **/
        $piIds = array_reduce(
            $pi_room_assignments,
            function( $pis_per_room, $assignment ){
                $_room_id = $assignment['room_id'];
                $_pi_id = $assignment['principal_investigator_id'];

                if( !array_key_exists($_room_id, $pis_per_room) ){
                    // Init list for this room
                    $pis_per_room[$_room_id] = array();
                }

                if( !in_array($_pi_id, $pis_per_room[$_room_id]) ){
                    // Push PI into room
                    $pis_per_room[$_room_id][] = $_pi_id;
                }

                return $pis_per_room;
            },
            array()
        );

        return $piIds;
    }

    public function getPIHazardRoomRelations( &$piId, &$hazard_id, Array &$roomIds){
        // FIXME: Bind these instead of concat
        $_ids = implode(', ', $roomIds);

        $queryString = "SELECT
            a.key_id, a.hazard_id, a.room_id, a.principal_investigator_id, a.status
            FROM principal_investigator_hazard_room a
            LEFT JOIN principal_investigator_room b
            ON a.principal_investigator_id = b.principal_investigator_id
            LEFT JOIN principal_investigator c
            ON c.key_id = a.principal_investigator_id
            WHERE (c.is_active = 1 OR c.key_id = $piId)
                AND a.hazard_id = $hazard_id
                AND a.room_id IN ($_ids)
                AND b.room_id IN($_ids)";

        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigatorHazardRoomRelation");
    }

    public function pihrReferencesOtherPI( PrincipalInvestigatorHazardRoomRelation &$relation, Array &$piIdsPerRoom, $subjectPI ){
        /** Relation references PI which is not the subject */
        $_rel_is_other_pi = $relation->getPrincipal_investigator_id() != $subjectPI;

        /** List of PI IDs assigned to the relation's Room (IFF the room was included in our earlier query) */
        $_rel_room_pi_list = $piIdsPerRoom[$relation->getRoom_id()] ?? [];

        /** Relation's PI is listed in its room's assignments */
        $_rel_pi_is_listed = in_array($relation->getPrincipal_investigator_id(), $_rel_room_pi_list);

        return $_rel_is_other_pi && $_rel_pi_is_listed;
    }

    public function determineHazardStatus( HazardDto &$hazardDto, Array &$pihrDtos ){
        $LOG = Logger::getLogger(__CLASS__ );

        ////////////////////////////////////////
        $piId = $hazardDto->getPrincipal_investigator_id();
        $hazard_id = $hazardDto->getHazard_id();
        $roomIds = $hazardDto->getRoomIds();
        ////////////////////////////////////////

        ////////////////////////////////////////
        $piIds = $this->getPIsAssignedInRooms( $hazardDto->getRoomIds() );
        ////////////////////////////////////////

        ////////////////////////////////////////
        //get all the Relationships between this hazard and rooms that this PI has,
        //  so we can determine if this PI or ANY PI has the hazard in any of these rooms
        $piHazardRooms = $this->getPIHazardRoomRelations($piId, $hazard_id, $roomIds);
        ////////////////////////////////////////

        ////////////////////////////////////////
        $relationHashMap = array();
        //build key_id arrays for fast comparison in the next step
        foreach($piHazardRooms as $relation){
            //while we're at it, determine if any of these relations belong to other PIs
            $referencesOtherPI = $this->pihrReferencesOtherPI($relation, $piIds, $piId);

            if( $referencesOtherPI === TRUE ){
                // Relation (and therefore this hazard DTO) is assigned to a PI other than the HazardDto's subject
                $hazardDto->setHasMultiplePis( true );
                $relation->setHasMultiplePis( true );
            }else{
                // Relation is assigned to at most One PI
                $relation->setHasMultiplePis(false);
            }

            if(!isset($relationHashMap[$relation->getRoom_id()])){
                $relationHashMap[$relation->getRoom_id()] = array();
            }

            array_push($relationHashMap[$relation->getRoom_id()], $relation);
        }
        ////////////////////////////////////////

        // Assume the hazard is not present or stored-only
        $hazardDto->setIsPresent( false );
        $hazardDto->setStored_only( false );

        ////////////////////////////////////////
        $storedOnly = true;
        foreach ($pihrDtos as &$room){
            $room->setContainsHazard(false);
            $room->setHasMultiplePis(false);

            if( isset($relationHashMap[$room->getRoom_id()])) {
                //see if there's a relation for this room and hazard with this pi id
                foreach ($relationHashMap[$room->getRoom_id()] as $relation){
                    if($relation->getPrincipal_investigator_id() == $hazardDto->getPrincipal_investigator_id() ){
                        $room->setContainsHazard(true);
                        $hazardDto->setIsPresent( true );
                        $room->setStatus($relation->getStatus());
                        if($relation->getStatus() != "STORED_ONLY"){
                            $storedOnly = false;
                        }else{
                            $room->setStored(true);
                        }

                    }
                    if($relation->getHasMultiplePis() == true){
                        $room->setHasMultiplePis(true);
                        if($relation->getPrincipal_investigator_id() != $hazardDto->getPrincipal_investigator_id() && in_array($relation->getPrincipal_investigator_id(), $piIds)){
                            $room->setOtherLab(true);
                        }
                        if($relation->getStatus() != "STORED_ONLY"){
                            $storedOnly = false;
                        }
                    }
                }
            }
        }
        ////////////////////////////////////////

        ////////////////////////////////////////
        //if Another PI has this hazard in one of these rooms, but the relevant PI does not
        if($hazardDto->getHasMultiplePis() == true && $hazardDto->getIsPresent() == false){
            $hazardDto->setBelongsToOtherPI( true );
        }

        //if the hazard is stored only in every room, and is present, set its stored_only property to true.
        $hazardDto->setStored_only( (bool) ($storedOnly && ($hazardDto->getIsPresent() || $hazardDto->getBelongsToOtherPI())) );
        ////////////////////////////////////////

        // Set our inspection-rooms
        $hazardDto->setInspectionRooms($pihrDtos);
        return $pihrDtos;
    }
}

?>
