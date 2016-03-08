
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
    
    public function getEquipmentInspectionById( $id = NULL ){
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
        	} else {
                // make new inspection
                $newInspection = new EquipmentInspection($decodedObject->getEquipment_class(), $decodedObject->getFrequency(), $decodedObject->getEquipment_id());
                if($decodedObject->getPrincipal_investigator_id() != null) $newInspection->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
                if($decodedObject->getRoom_id() != null)                  $newInspection->setRoom_id($decodedObject->getRoom_id());
                
                $newInspectionDao = new GenericDao($newInspection);
                $newInspection = $newInspectionDao->save($newInspection);
            }
        	$dao = $this->getDao(new EquipmentInspection());
            $decodedObject = $dao->save($decodedObject);
            $decodedObjects = array($decodedObject);
            if ($newInspection) array_push($decodedObjects, $newInspection);
            return $decodedObjects;
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
    
    //upload the document for a BiosafteyProtocol
	public function uploadReportCertDocument( $id = NULL){
        define(UPLOAD_DATA_DIR, "http://erasmus.graysail.com/rsms/src/equipment/documents");
		$LOG = Logger::getLogger('Action:' . __function__);		
		//verify that this file is of a type we consider safe		
		
		// Make sure the file upload didn't throw a PHP error
		if ($_FILES[0]['error'] != 0) {
			return new ActionError("File upload error.");
		}
		/*
		// Make sure it was an HTTP upload
		if (!is_uploaded_file($_FILES[$fieldname]['tmp_name'])) {
			return new ActionError("Not a valid upload method.");
		}
		*/
		//validate the file, make sure it's a .doc or .pdf
		//check the extension
		$valid_file_extensions = array("doc","pdf");
		$file_extension = strtolower( substr( $_FILES['file']["name"], strpos($_FILES['file']["name"], "." ) + 1) ) ;
		
		if (!in_array($file_extension, $valid_file_extensions)) {
			return new ActionError("Not a valid file extension");
		}else{
			//make sure the file actually matches the extension, as best we can
			$finfo = new finfo(FILEINFO_MIME);
			$type = $finfo->file($_FILES['file']["tmp_name"]);
			$match = false;
			foreach($valid_file_extensions as $ext){
				if(strstr($type, $ext)){
					$match = true;
				}
			}
			if($match == false){
				return new ActionError("Not a valid file");
			}
		}
		
		// Start by creating a unique filename using timestamp.  If it's
		// already in use, keep incrementing the timstamp until we find an unused filename.
		// 99.999% of the time, this should work the first time, but better safe than sorry.
		$now = time();
		while(file_exists($filename = UPLOAD_DATA_DIR . $now.'-'.$_FILES['file']['name']))
		{
			$now++;
		}

		// Write the file
		if (move_uploaded_file($_FILES['file']['tmp_name'], $filename) != true) {
			return new ActionError("Directory permissions error for " . UPLOAD_DATA_DIR);
		}
		
		
		/////////////////////////////////////
		//
		// return the name of the file, as it was saved on the server, saving the relevant protocol if one exists already
		//
		////////////////////////////////////
		
		//is this for a protocol that already exists?
		if($id == NULL){
			$id = $this->getValueFromRequest('id', $id);
		}
		$LOG->fatal($filename);
		//get just the name of the file
		$name = basename($filename);

		//if so, update the path of that report cert now and save it.
        $protocolDao = $this->getDao( new EquipmentInspection() );
        $protocol = $this->getEquipmentInspectionById( $id );
        $protocol->setReport_path( $name );
        $LOG->fatal($protocol);
        $protocolDao->save($protocol);
		
		//either way, return the name of the saved document so that it can be added to the client
		return $name;				
	}
}

?>