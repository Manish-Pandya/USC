<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingHazardDtoChange extends PendingChange {
	
	private $hazard;
	private $room_id;
	private $hazard_id;
    private $hazard_name;
    private $room_name;
	
	public function __construct(){

		$this->COLUMN_NAMES_AND_TYPES["room_id"] = "integer";
		$this->COLUMN_NAMES_AND_TYPES["hazard_id"] = "integer";
        $this->COLUMN_NAMES_AND_TYPES["hazard_name"] = "text";

		// Define which subentities to load
		$entityMaps = array();
		//$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$entityMaps[] = new EntityMap("eager","getParent_id");
		$entityMaps[] = new EntityMap("lazy","getHazard");
	
		$this->setEntityMaps($entityMaps);
	
	}
	public function getHazard() {
		if($this->hazard === NULL && $this->hasPrimaryKeyValue()) {
			$hazardDao = new GenericDAO(new hazard());
			$this->hazard = $hazardDao->getById($this->parent_id);
		}
		return $this->hazard;
	}

	public function getRoom_id(){
		return $this->room_id;
	}
	public function setRoom_id($room_id){
		$this->room_id = $room_id;
	}

	public function getHazard_id(){
		return $this->hazard_id;
	}
	public function setHazard_id($hazard_id){
		$this->hazard_id = $hazard_id;
	}

    public function getHazard_name(){
        if($this->hazard_name == null && $this->hazard_id != null && $this->hasPrimaryKeyValue()){
            $hazardDao = new GenericDAO(new Hazard());
            $hazard = $hazardDao->getById($this->hazard_id);
            $this->hazard_name = $hazard->getName();
        }
        return $this->hazard_name;
    }
    public function setHazard_name($name){$this->hazard_name = $name;}

    public function getRoom_name(){
        if($this->room_name == null && $this->room_id != null && $this->hasPrimaryKeyValue()){
            $roomDao = new GenericDAO(new Room());
            $room = $roomDao->getById($this->room_id);
            $this->room_name = $room->getName();
        }
        return $this->room_name;
    }
    public function setRoom_name($name){$this->room_name = $name;}
}
?>