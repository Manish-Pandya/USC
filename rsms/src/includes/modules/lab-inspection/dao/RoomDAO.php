<?php

class RoomDAO extends GenericDAO {
    private static $HAZARDS_PRESENT_CACHE;

    public function __construct(){
        parent::__construct(new Room());
        if( !isset(self::$HAZARDS_PRESENT_CACHE)){
            self::$HAZARDS_PRESENT_CACHE = new AppCache('Room/Hazards');
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
}
?>