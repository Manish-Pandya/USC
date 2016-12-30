
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
            $LOG->fatal($decodedObject);
            $type = $decodedObject->getEquipment_class();
            $equipment = new $type();
            $equipmentDao = $this->getDao($equipment);
            $equipment = $equipmentDao->getById($decodedObject->getEquipment_id());
            $equipment->setCertification_date($decodedObject->getCertification_date());
            $equipment->setFrequency($decodedObject->getFrequency());

            //certify and create subsequent inspection as well
            $equipment->conditionallyCreateEquipmentInspection($decodedObject);
            //force reload of all inspections for relevant equipment by client
            return $equipment->getEquipmentInspections();
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
            $insp = $decodedObject->getSelectedInspection();
            $pisToAdd = $insp->getPrincipalInvestigators();

            $cabinet = $dao->save($decodedObject);
            $decodedObject->setKey_id($cabinet->getKey_id());

            $inspection = $decodedObject->conditionallyCreateEquipmentInspection($insp);

            //if the inspection already exists, remove its PIs first, then add the relevant ones
            if($decodedObject->getSelectedInspection() != null){
                foreach ($inspection->getPrincipalInvestigators() as $pi){
                    $dao->removeRelatedItems($pi->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));
                }

                foreach($pisToAdd as $pi){
                    $dao->addRelatedItems($pi["Key_id"],$insp->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));
                }
            }


            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager","getEquipmentInspections");
            $entityMaps[] = new EntityMap("lazy","getFirstInspection");
            $cabinet->setEntityMaps($entityMaps);
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

	public function getAllAutoclaves(){
        $autoClavesDao = $this->getDao(new Autoclave());
    	return $autoClavesDao->getAll();
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
        $l = Logger::getLogger("upload cert doc");
       // define(UPLOAD_DATA_DIR, "http://erasmus.graysail.com/rsms/src/biosafety-protocols/protocol-documents");
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
            $l->fatal($file_extension);
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
		while(file_exists($filename = BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR . $now.'-'.$_FILES['file']['name']))
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

		//update the path of that report cert now and save it.
        $protocolDao = $this->getDao( new EquipmentInspection() );
        $protocol = $this->getEquipmentInspectionById( $id );
        $protocol->setReport_path( $name );
        $LOG->fatal($protocol);
        $protocolDao->save($protocol);

		//return the name of the saved document so that it can be added to the client
		return $name;
	}

    //upload the document for a BiosafteyProtocol
	public function uploadReportQuoteDocument( $id = NULL){
        $l = Logger::getLogger("upload quote doc");
        // define(UPLOAD_DATA_DIR, "http://erasmus.graysail.com/rsms/src/biosafety-protocols/protocol-documents");
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
            $l->fatal($file_extension);
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
		while(file_exists($filename = BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR . $now.'-'.$_FILES['file']['name']))
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

		//update the path of that report cert now and save it.
        $protocolDao = $this->getDao( new EquipmentInspection() );
        $protocol = $this->getEquipmentInspectionById( $id );
        $protocol->setQuote_path( $name );
        $LOG->fatal($protocol);
        $protocolDao->save($protocol);

		//return the name of the saved document so that it can be added to the client
		return $name;
	}
}

?>