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
			$dto->setAndFilterInspectionRooms($roomDtos);
		}
		return $hazardDtos;
	}
}

?>
