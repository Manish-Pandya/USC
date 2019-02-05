<?php

/**
 *
 *
 *
 * @author Matt Breede, GraySail LLC
 */
class PrincipalInvestigatorHazardRoomRelation extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "principal_investigator_hazard_room";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

		//GenericCrud
		"key_id"	=> "integer",
		"hazard_id" => "integer",
		"room_id"	=> "integer",
		"principal_investigator_id"	=> "integer",
		"equipment_id"	=> "integer",
		"status"	=> "text"
	);

	private $room_id;
	private $principal_investigator_id;
	private $hazard_id;
    private $hazard;
	private $status;
    private $equipment_inspection_id;
	private $hasMultiplePis;
    private $piName;
    private $buildingName;
	private $roomName;
	private $equipment_id;

	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::eager("getRoom_id");
		$entityMaps[] = EntityMap::eager("getPrincipal_investigator_id");
		return $entityMaps;
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getRoom_id(){ return $this->room_id; }
	public function setRoom_id($room_id){ $this->room_id = $room_id; }

	public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($principal_investigator_id){ $this->principal_investigator_id = $principal_investigator_id; }

	public function getHazard_id(){return $this->hazard_id;}
	public function setHazard_id($hazard_id){$this->hazard_id = $hazard_id;}

	public function getStatus(){
        if($this->status == "Stored Only"){
            $this->status = "STORED_ONLY";
        }elseif($this->status == "In Use" || $this->status == null){
            $this->status = "IN_USE";
        }elseif($this->status == "Other Lab's Hazard" || $this->status == null){
            $this->status = "OTHER_PI";
        }
        return $this->status;
    }
	public function setStatus($status){$this->status = $status;}

	public function getHasMultiplePis(){
		return $this->hasMultiplePis;
	}

	public function setHasMultiplePis($hasMultiplePis){
		$this->hasMultiplePis = $hasMultiplePis;
	}

    public function getHazard(){
        if($this->hazard == null) {
			$userDAO = new GenericDAO(new Hazard());
			$this->hazard = $userDAO->getById($this->hazard_id);
		}
		return $this->hazard;
    }

    public function getPiName(){
        if($this->piName == null && $this->principal_investigator_id != null){
            $db = DBConnection::get();
            $queryString = "SELECT concat(c.first_name, ' ', c.last_name) as piName
                            FROM principal_investigator_hazard_room a
                            JOIN principal_investigator b
                            ON b.key_id = a.principal_investigator_id
                            JOIN erasmus_user c
                            ON c.key_id = b.user_id
                            AND b.key_id = :piId";
            $stmt = DBConnection::prepareStatement($queryString);
            $stmt->bindParam(':piId', $this->principal_investigator_id, PDO::PARAM_INT);
            $stmt->execute();
            while($name = $stmt->fetchColumn()){
                $this->piName = $name;
            }
        }
        return $this->piName;
    }
    public function setPiName($name){$this->piName = $name;}

    public function getEquipment_id(){
		return $this->equipment_id;
	}
	public function setEquipment_id($equipment_id){
		$this->equipment_id = $equipment_id;
	}

    public function getBuildingName(){
		return $this->buildingName;
	}
	public function setBuildingName($buildingName){
		$this->buildingName = $buildingName;
	}

	public function getRoomName(){
		return $this->roomName;
	}
	public function setRoomName($roomName){
		$this->roomName = $roomName;
	}
}
?>