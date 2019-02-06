
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

            EntityManager::with_entity_maps(EquipmentInspection::class, array(
		        EntityMap::eager("getRoom"),
                EntityMap::eager("getPrincipal_investigator"),
                EntityMap::eager("getPrincipalInvestigators")
            ));

            return $is;
        }
    }

    public function getAllBioSafetyCabinets(){
    	$bioSafetyCabinetDao = $this->getDao(new BioSafetyCabinet());
        $cabs = $bioSafetyCabinetDao->getAll();
        EntityManager::with_entity_maps(BioSafetyCabinet::class, array(
            EntityMap::lazy("getRoom"),
            EntityMap::lazy("getPrincipal_investigator"),
            EntityMap::lazy("getPrincipalInvestigators"),
            EntityMap::lazy("getEquipmentInspections")
        ));

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

            EntityManager::with_entity_maps(BioSafetyCabinet::class, array(
                EntityMap::eager("getEquipmentInspections"),
                EntityMap::lazy("getFirstInspection")
            ));

            EntityManager::with_entity_maps(EquipmentInspection::class, array(
                EntityMap::eager("getRoom"),
                EntityMap::lazy("getPrincipal_investigator"),
                EntityMap::eager("getPrincipalInvestigators")
            ));

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

    	EntityManager::with_entity_maps(Room::class, array(
    	    EntityMap::lazy("getPrincipalInvestigators"),
    	    EntityMap::lazy("getHazards"),
    	    EntityMap::lazy("getHazard_room_relations"),
    	    EntityMap::lazy("getHas_hazards"),
    	    EntityMap::eager("getBuilding"),
            EntityMap::lazy("getSolidsContainers")
        ));

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

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::lazy("getRooms"),
            EntityMap::eager("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::lazy("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::lazy("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getWipeTests"),
            EntityMap::lazy("getCurrentPi_authorization"),
            EntityMap::lazy("getCurrentVerifications"),
            EntityMap::lazy("getCurrentIsotopeInventories"),
		    EntityMap::lazy("getWasteBags"),
            EntityMap::lazy("getCurrentWasteBag")
        ));

        return $pis;
    }

    public function getAllEquipmentRooms(){
        $dao = $this->getDao(new Room());

        $rooms = $dao->getAll();

        // instructin rooms to lazy-load children
        EntityManager::with_entity_maps(Room::class, array(
	        EntityMap::lazy("getPrincipalInvestigators"),
	        EntityMap::lazy("getHazards"),
	        EntityMap::eager("getBuilding"),
	        EntityMap::lazy("getHazard_room_relations"),
	        EntityMap::lazy("getHas_hazards"),
	        EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getHasMultiplePIs"),
            EntityMap::lazy("getHazardTypesArePresent"),
            EntityMap::lazy("getChem_hazards_present"),
            EntityMap::lazy("getRad_hazards_present"),
            EntityMap::lazy("getBio_hazards_present")
        ));

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