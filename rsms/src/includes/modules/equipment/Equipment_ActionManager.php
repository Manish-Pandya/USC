
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
    	$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
            $type = $decodedObject->getEquipment_class();
            $equipment = new $type();
            $equipmentDao = $this->getDao($equipment);
            $equipment = $equipmentDao->getById($decodedObject->getEquipment_id());
            $equipment->setCertification_date($decodedObject->getCertification_date());
            $equipment->setFrequency($decodedObject->getFrequency());
            //certify and create subsequent inspection as well
            $n = $equipment->conditionallyCreateEquipmentInspection($decodedObject);
            //force reload of all inspections for relevant equipment by client
			$is = $equipment->getEquipmentInspections();

            $entityMaps = array();
		    $entityMaps[] = new EntityMap("eager","getRoom");
            $entityMaps[] = new EntityMap("eager","getPrincipal_investigator");
            $entityMaps[] = new EntityMap("eager","getPrincipalInvestigators");
            foreach($is as $i){
                //$i->setCertification_date("2017-10-01 15:32:56");
                $i->setEntityMaps($entityMaps);
            }

            return $is;
        }
    }

    public function getAllBioSafetyCabinets(){
    	$bioSafetyCabinetDao = $this->getDao(new BioSafetyCabinet());
        $cabs = $bioSafetyCabinetDao->getAll();
        foreach($cabs as $cab){
            $entityMaps = array();
		    $entityMaps[] = new EntityMap("lazy","getRoom");
            $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator");
            $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
            $entityMaps[] = new EntityMap("lazy","getEquipmentInspections");
            $cab->setEntityMaps($entityMaps);
        }

    	return $cabs;
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

            if($decodedObject->getSelectedInspection() != null){
                $insp = $decodedObject->getSelectedInspection();

                if($decodedObject->getFrequency() != null && $insp != null)
                    $insp->setFrequency($decodedObject->getFrequency());
                if($insp->getCertification_date() != null)
                    $decodedObject->setCertification_date($insp->getCertification_date());

                $pisToAdd = $insp->getPrincipalInvestigators();
                $decodedObject->setKey_id($cabinet->getKey_id());

                $inspection = $decodedObject->conditionallyCreateEquipmentInspection($insp);
                $LOG->debug($inspection);
                $pis = $inspection->getPrincipalInvestigators();
                //if the inspection already exists, remove its PIs first, then add the relevant ones
                if($decodedObject->getSelectedInspection() != null && $inspection != null){
                    $LOG->debug("we found an inspection");

                    $piHazRoomDao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());

                    foreach ($pis as $pi){
                        if (is_array($pi)) $pi = JsonManager::assembleObjectFromDecodedArray($pi);
                        $dao->removeRelatedItems($pi->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));

                        //remove relavent PrincipalInvestigatorHazardRoomRelation so Hazard Inventory stays in sync
                        $group = new WhereClauseGroup(array(
                            new WhereClause("principal_investigator_id","=",$pi->getKey_id()),
                            new WhereClause("equipment_id", "=",$cabinet->getKey_id())
                        ));
                        $relations = $piHazRoomDao->getAllWhere($group);
                        foreach($relations as $r){
                            $piHazRoomDao->deleteById($r->getKey_id());
                        }

                    }

                    foreach($pisToAdd as $pi){
                        if (is_array($pi)) $pi = JsonManager::assembleObjectFromDecodedArray($pi);

                        $dao->addRelatedItems($pi->getKey_id(),$insp->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));

                        //add PrincipalInvestigatorHazardRoomRelation so Hazard Inventory stays in sync
                        $r = new PrincipalInvestigatorHazardRoomRelation();
                        //Magic number is the key_id for BioSafety Cabinet hazard
                        $r->setHazard_id(10324);
                        $r->setPrincipal_investigator_id($pi->getKey_id());
                        $r->setEquipment_id($cabinet->getKey_id());
                        $r->setRoom_id($insp->getRoom_id());
                        $r->setStatus("In Use");
                        $r->setIs_active(true);
                        $r = $piHazRoomDao->save($r);

                    }
                }

                $cabinet->setEquipmentInspections(null);
            }
            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager","getEquipmentInspections");
            $entityMaps[] = new EntityMap("lazy","getFirstInspection");
            $cabinet->setEntityMaps($entityMaps);

            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager","getRoom");
            $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator");
            $entityMaps[] = new EntityMap("eager","getPrincipalInvestigators");
            foreach($cabinet->getEquipmentInspections() as $i){
                $i->setEntityMaps($entityMaps);
            }

            //manage PiHazardRoomRelationships for all PIs who could be associated, or could HAVE been associated with the cabinet

            return $cabinet;
        }
    }

    public function getBioSafetyCabinetById( $id = NULL ){
    	$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

	public function getAllChemFumeHoods(){
        $hoodsDao = $this->getDao(new ChemFumeHood());
    	return $hoodsDao->getAll();
    }

	public function getAllLasers(){
        $lasersDao = $this->getDao(new Laser());
    	return $lasersDao->getAll();
    }

	public function getAllXRays(){
        $xraysDao = $this->getDao(new XRay());
    	return $xraysDao->getAll();
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
		$LOG = Logger::getLogger('Action:' . __function__);

		// Process file upload
		$documentName = $this->uploadDocument();

		if( $documentName instanceof ActionError){
			$LOG->error("Error in document upload");
			return $documentName;
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

		//update the path of that report cert now and save it.
		$LOG->info("Update EquipmentInspection #$id Report_path: $documentName");
        $protocolDao = $this->getDao( new EquipmentInspection() );
        $protocol = $this->getEquipmentInspectionById( $id );
        $protocol->setReport_path( $documentName );
        $LOG->debug($protocol);
        $protocolDao->save($protocol);

		//return the name of the saved document so that it can be added to the client
		return $documentName;
	}

    public function uploadDeconDocument( $id = NULL){
		$LOG = Logger::getLogger('Action:' . __function__);

		// Process file upload
		$documentName = $this->uploadDocument();

		if( $documentName instanceof ActionError){
			$LOG->error("Error in document upload");
			return $documentName;
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

		//update the path of that report cert now and save it.
		$LOG->info("Update EquipmentInspection #$id Decon_path: $documentName");
        $protocolDao = $this->getDao( new EquipmentInspection() );
        $protocol = $this->getEquipmentInspectionById( $id );
        $protocol->setDecon_path( $documentName );
        $LOG->debug($protocol);
        $protocolDao->save($protocol);

		//return the name of the saved document so that it can be added to the client
		return $documentName;
	}

    //upload the document for a BiosafteyProtocol
	public function uploadReportQuoteDocument( $id = NULL){
		$LOG = Logger::getLogger('Action:' . __function__);

		// Process file upload
		$documentName = $this->uploadDocument();

		if( $documentName instanceof ActionError){
			$LOG->error("Error in document upload");
			return $documentName;
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

		//update the path of that report cert now and save it.
		$LOG->info("Update EquipmentInspection #$id Quote_path: $documentName");
        $protocolDao = $this->getDao( new EquipmentInspection() );
        $protocol = $this->getEquipmentInspectionById( $id );
        $protocol->setQuote_path( $documentName );
        $LOG->debug($protocol);
        $protocolDao->save($protocol);

		//return the name of the saved document so that it can be added to the client
		return $documentName;
	}

    public function getAllEquipmentPis(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );


        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAll();
        /** TODO: Instead of $dao->getAll, we gather PIs which are either active or have rooms associated with them. **/
        /* $whereClauseGroup = new WhereClauseGroup( array( new WhereClause("is_active","=","1"), new WhereClause("key_id","IN","(SELECT principal_investigator_id FROM principal_investigator_room)") ) );
        $pis = $dao->getAllWhere($whereClauseGroup, "OR");*/

        $entityMaps = array();
        $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
        $entityMaps[] = new EntityMap("lazy","getRooms");
        $entityMaps[] = new EntityMap("eager","getDepartments");
        $entityMaps[] = new EntityMap("eager","getUser");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPi_authorization");
        $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
        $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
        $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
        $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
        $entityMaps[] = new EntityMap("lazy", "getPickups");
        $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
        $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("lazy","getVerifications");
        $entityMaps[] = new EntityMap("lazy","getBuidling");
        $entityMaps[] = new EntityMap("lazy","getWipeTests");
        $entityMaps[] = new EntityMap("lazy","getCurrentPi_authorization");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $entityMaps[] = new EntityMap("lazy", "getCurrentIsotopeInventories");
		$entityMaps[] = new EntityMap("lazy", "getWasteBags");
		$entityMaps[] = new EntityMap("lazy", "getCurrentWasteBag");

        foreach($pis as $pi){
            $pi->setEntityMaps($entityMaps);
        }

        return $pis;
    }

    public function getAllEquipmentRooms(){
        $dao = $this->getDao(new Room());

        $rooms = $dao->getAll();

        // initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
        $roomMaps = array();
	    $roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
	    $roomMaps[] = new EntityMap("lazy","getHazards");
	    $roomMaps[] = new EntityMap("eager","getBuilding");
	    $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
	    $roomMaps[] = new EntityMap("lazy","getHas_hazards");
	    $roomMaps[] = new EntityMap("lazy","getSolidsContainers");
        $roomMaps[] = new EntityMap("lazy","getHasMultiplePIs");
        $roomMaps[] = new EntityMap("lazy","getHazardTypesArePresent");
        $roomMaps[] = new EntityMap("lazy","getChem_hazards_present");
        $roomMaps[] = new EntityMap("lazy","getRad_hazards_present");
        $roomMaps[] = new EntityMap("lazy","getBio_hazards_present");

        foreach($rooms as &$room){
            $room->setEntityMaps($roomMaps);
        }

        return $rooms;
    }

	function uploadDocument(){
        $LOG = Logger::getLogger(__CLASS__);

        try{
            $filename = DocumentManager::processFileUpload();

            //get just the name of the file
            $name = basename($filename);

            //return the name of the saved document
            return $name;
        }
        catch( FailedUploadException $e ){
			return new ActionError($e->getMessage());
        }
        catch( UnsupportedFileTypeException $e ){
			return new ActionError($e->getMessage(), $e->getCode());
        }
        catch( IOException $e ){
			return new ActionError($e->getMessage(), $e->getCode());
        }
	}
}

?>