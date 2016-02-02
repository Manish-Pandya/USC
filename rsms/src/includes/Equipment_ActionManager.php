
<?php
/**
 * Contains action functions specific to the Equipment module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt Breeden
 */
class Equipment_ActionManager extends ActionManager {


    /*****************************************************************************\
     *                            Get Functions                                  *
    \*****************************************************************************/
    
    public function getAllEquipmentInspections(){
        $equipmentInspectionDao = $this->getDao(new EquipmentInspection());
    	return $equipmentInspectionDao->getAll();
    }
    
    public function getEquipmentInspectionsById( $id = NULL ){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );
    
    	$id = $this->getValueFromRequest('id', $id);
    
    	if( $id !== NULL ){
    		$dao = $this->getDao(new EquipmentInspection());
    		return $dao->getById($id);
    	}
    	else{
    		//error
    		return new ActionError("No request parameter 'id' was provided");
    	}
    }
    
    public function saveEquipmentInspection( EquipmentInspection $inspection = NULL ){
        $LOG = Logger::getLogger('Action:' . __function__);
        if($inspection !== NULL) {
            $decodedObject = $inspection;
        }
        else {
            $decodedObject = $this->convertInputJson();
        }
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Question');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
        	if($decodedObject->getCertification_date() == NULL){
        		$decodedObject->setCertification_date(date('Y-m-d H:i:s'));
        	}
        	$dao = $this->getDao(new EquipmentInspection());
            $dao->save($decodedObject);
            return $decodedObject;
        }
    }
    
    public function getAllBioSafetyCabinets(){
    	$bioSafetyCabinetDao = $this->getDao(new BioSafetyCabinet());
    	return $bioSafetyCabinetDao->getAll();
    }
    
  	public function saveBioSafetyCabinet( BioSafetyCabinet $cabinet = NULL ){
        $LOG = Logger::getLogger('Action:' . __function__);
        if($cabinet !== NULL) {
            $decodedObject = $cabinet;
        }
        else {
            $decodedObject = $this->convertInputJson();
        }
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Question');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
        	$dao = $this->getDao(new BioSafetyCabinet());
            $cabinet = $dao->save($decodedObject);
            $decodedObject->setKey_id($cabinet->getKey_id());
            $decodedObject->conditionallyCreateEquipmentInspection();
            return $cabinet;
        }
    }
    
    public function getBioSafetyCabinetById( $id = NULL ){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );
    
    	$id = $this->getValueFromRequest('id', $id);
    
    	if( $id !== NULL ){
    		$dao = $this->getDao(new BioSafetyCabinet());
    		return $dao->getById($id);
    	}
    	else{
    		//error
    		return new ActionError("No request parameter 'id' was provided");
    	}
    }
    
    public function getBuidlingsWithoutRooms(){
    	return $this->getAllBuildings(null, true, true);	
    }
    
    public function getRoomsWithoutComposing(){
    	$rooms = $this->getAllRooms();
    	
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
    	$entityMaps[] = new EntityMap("lazy","getHazards");
    	$entityMaps[] = new EntityMap("lazy","getHazard_room_relations");
    	$entityMaps[] = new EntityMap("lazy","getHas_hazards");
    	$entityMaps[] = new EntityMap("eager","getBuilding");
    	$entityMaps[] = new EntityMap("lazy","getSolidsContainers");
    	
    	foreach($rooms as $room){
    		$room->setEntityMaps($entityMaps);
    	}
    	
    	return $rooms;
    	
    }
}

?>