<?php

class RoomDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new Room());
    }

    public function getHazardTypesPresentInRoom($roomId){
        $stmt = DBConnection::prepareStatement("SELECT * FROM room_hazards WHERE room_id = :id");
        $stmt->bindParam(':id', $roomId);
        if( !$stmt->execute()){
            $error = $stmt->errorInfo();
            $stmt = null;
            return new QueryError($error);
        }

        $hazards = $stmt->fetchObject(PresentHazardsDto::class);
        $stmt = null;

        if( $hazards ){
            return $hazards;
        }

        // None returned; return empty mapping object
        return new PresentHazardsDto();
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