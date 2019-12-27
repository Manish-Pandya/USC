<?php

class RoomDAO extends GenericDAO {
    private static $HAZARDS_PRESENT_CACHE;

    public function __construct(){
        parent::__construct(new Room());
        if( !isset(self::$HAZARDS_PRESENT_CACHE)){
            self::$HAZARDS_PRESENT_CACHE = CacheFactory::create('Room/Hazards');
        }
    }

    function getAll($sortColumn = NULL, $sortDescending = false, $activeOnly = false){
        $this->cacheAllHazardTypesPresentInRooms();
        return parent::getAll($sortColumn, $sortDescending, $activeOnly);
    }

    public function cacheAllHazardTypesPresentInRooms(){
        $stmt = DBConnection::prepareStatement("SELECT * FROM room_hazards GROUP BY room_id");

        if( !$stmt->execute()){
            $error = $stmt->errorInfo();
            $stmt = null;
            return new QueryError($error);
        }

        $hazards = $stmt->fetchAll(PDO::FETCH_CLASS, PresentHazardsDto::class);
        $stmt = null;

        foreach($hazards as $hazard){
            self::$HAZARDS_PRESENT_CACHE->cacheEntity(
                $hazard,
                AppCache::key_class_id(Room::class, $hazard->room_id)
            );
        }

        return $hazards;
    }

    public function getHazardTypesPresentInRoom($roomId){
        $key = AppCache::key_class_id(Room::class, $roomId);
        $cached = self::$HAZARDS_PRESENT_CACHE->getCachedEntity($key);
        if( !$cached ){

            $stmt = DBConnection::prepareStatement("SELECT * FROM room_hazards WHERE room_id = :id");
            $stmt->bindParam(':id', $roomId);
            if( !$stmt->execute()){
                $error = $stmt->errorInfo();
                $stmt = null;
                return new QueryError($error);
            }

            $hazards = $stmt->fetchObject(PresentHazardsDto::class);
            $stmt = null;

            if( !$hazards ){
                // None returned; return empty mapping object
                $hazards = new PresentHazardsDto();
            }

            self::$HAZARDS_PRESENT_CACHE->cacheEntity($hazards, $key);

            return $hazards;
        }

        return $cached;
    }

    public function getRoomHasHazards($roomId){
		$db = DBConnection::get();

        $stmt = DBConnection::prepareStatement('SELECT COUNT(*) FROM hazard_room WHERE room_id = :id');
        $stmt->bindParam(':id', $roomId);
		$stmt->execute();
		$number_of_rows = $stmt->fetchColumn();

		return ($number_of_rows > 0);
    }

    /**
     * Returns the PI IDs of principal investigators with Hazard assignments in the given room
     *
     * @return Array
     */
    public function getPrincipalInvestigatorsWithHazardInRoom(int $roomId, Array $piIds = null){
        $db = DBConnection::get();

        $limit_pis = isset($piIds) && !empty($piIds);

        $pi_predicate = '';

        if( $limit_pis ){
            $inQuery = implode(',', array_fill(0, count($piIds), '?'));
            $pi_predicate = "AND principal_investigator_id IN ($inQuery)";
        }

		$sql = "SELECT principal_investigator_id, COUNT(*) as hazard_count FROM principal_investigator_hazard_room
                WHERE room_id = ?
                $pi_predicate
                GROUP BY principal_investigator_id";

		// Query the db and return an array of $this type of object
        $stmt = DBConnection::prepareStatement($sql);

        // Bind room ID
        $stmt->bindValue( 1, $roomId );

        // Bind PI IDs
        if( $limit_pis ){
            $i = 2;
            foreach($piIds as $val){
                $stmt->bindValue( $i, $val );
                $i++;
            }
        }

        if ($stmt->execute() ) {
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $result;
		} else{
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
            $l->error('Returning QueryError with message: ' . $result->getMessage());
            return $result;
		}
    }

    public function getRoomPIs( $roomId, $activeOnly = false ){
        return $this->getRelatedItemsById($roomId,
            DataRelationship::fromArray(Room::$PIS_RELATIONSHIP),
            NULL, $activeOnly, $activeOnly
        );
    }

    public function getRoomAssignedUsers( int $roomId, string $roleName = null ){
        // TODO: Join to principal_investigator_room to get a glimpse of ALL assignments?

        $q = QueryUtil::selectFrom(new UserRoomAssignment())
            ->where(
                Field::create('room_id', UserRoomAssignment::TABLE_NAME),
                '=',
                $roomId
            );

        if( isset($roleName) ){
            $q->where(
                Field::create('role_name', UserRoomAssignment::TABLE_NAME),
                '=',
                $roleName
            );
        }

        return $q->getAll();
    }
}
?>