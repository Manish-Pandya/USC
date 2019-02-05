<?php
/**
 * Contains action functions specific to the radiation module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt
 */
class HazardInventoryActionManager extends ActionManager {

	public function getHazardRoomDtosByPIId($piId = null, $roomIds = null) {

		if($piId == NULL){
			$piId = $this->getValueFromRequest('id', $piId);
		}

		if($roomIds == NULL){
			$roomIds = $this->getValueFromRequest('roomIds', $roomIds);
		}

		if( $piId !== NULL ){
			$dao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());
			if($roomIds == null){
				return $dao->getHazardRoomDtosByPIId($piId);
			}else{
				return $dao->getHazardRoomDtosByPIId($piId, $roomIds);
			}
		}
		else{
			//error
			return new ActionError("No request parameter 'piId' was provided");
		}

	}

	public function PrincipalInvestigatorHazardRoomRelationById(){}

	/**
	 * Given a PIHazardRoomDto, creates or deletes relevant relationships between PI, Hazard, and Room
	 *
	 * @param HazardDto $decodedObject
	 * @return HazardDto $decodedObject
	 */
	public function savePIHazardRoomMappings($decodedObject = NULL){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

		if($decodedObject == null){
			$decodedObject = $this->convertInputJson();
		}

		if($decodedObject == null){
			return new ActionError("No DTO");
		}

        $rels = array();
		foreach($decodedObject->getInspectionRooms() as $roomDto){
			$rels[] = $this->savePrincipalInvestigatorHazardRoomRelation($roomDto);
		}

		return $rels;
	}

	/**
	* Creates, updates or deletes a PIHazardRoomDto object, depending on its ContainsHazard property and whether or not it already exists or needs to be created anew
	* @param PIHazardRoomDto|array decodedObject
	* @return PIHazardRoomDto dto
	*/
    public function savePrincipalInvestigatorHazardRoomRelation( $decodedObject = null ){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

		if($decodedObject == null){
			$decodedObject = $this->convertInputJson();
		}

		//this method is frequently called by $this->savePIHazardRoomMappings()
		//because the param of that method is assembled by JsonManager, its children will be arrays instead of objects
		//If we have passed an array, assemble it into a PIHazardRoomDto object
		if(is_array($decodedObject)){
			$decodedObject = JsonManager::assembleObjectFromDecodedArray($decodedObject);
		}

		if($decodedObject == null){
			return new ActionError("No DTO");
		}

		$piHazardRoomDao = $this->getDao(new PrincipalInvestigatorHazardRoomRelation());
		$roomDao = $this->getDao(new Room());

		//get the relevant PrincipalInvestigatorHazardRoomRelation
		$relations = $this->getRelevantRelations($decodedObject);
		//room doesn't contain the relevant hazard, so delete any relations
		if($decodedObject->getContainsHazard() == false){
			foreach($relations as $relation){
				$piHazardRoomDao->deleteById($relation->getKey_id());
			}

			// If this is a master category, remove the appropriate master category flag from the Room, but only if no other PIs have the master category in the room
            $room = $roomDao->getById($decodedObject->getRoom_id());
            $roomHazards = $room->getHazards();
            $dontRemove = false;

			//since we've removed the hazard from the room for this PI, we should also remove any child hazards
			$hazard = $this->getHazardById($decodedObject->getHazard_id());
			$childHazards = $hazard->getActiveSubHazards();
			foreach($childHazards as $child){
				$childDto = new PIHazardRoomDto();
				$childDto->setContainsHazard(0);
				$childDto->setHazard_id($child->getKey_id());
				$childDto->setRoom_id($decodedObject->getRoom_id());
				$childDto->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
				$this->savePrincipalInvestigatorHazardRoomRelation($childDto);
			}
		}
		//this room contains the relevant hazard
		else{
			//hazard, room and PI were already related so we get the relevant relations and save them
			//since they are already matched by pi, hazard and room, the only thing that could have changed is status
			//there should only be one relation, but our query returns an array, hence the loop
			if($relations != null){
				foreach($relations as $relation){
					$relation->setStatus($decodedObject->getStatus());
					$piHazardRoomDao->save($relation);
				}
			}
			//no previous relations.  make a new one and save it
			else{
				$decodedObject->setContainsHazard(true);
				$relation = new PrincipalInvestigatorHazardRoomRelation();
				$relation->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
				$relation->setHazard_id($decodedObject->getHazard_id());
				$relation->setRoom_id($decodedObject->getRoom_id());
				$relation->setStatus($decodedObject->getStatus());
				$relation = $piHazardRoomDao->save($relation);
                $LOG->debug($relation);

			}
			//if we have set the status to "STORED_ONLY", we must also set the status to for each child hazard in this room
			if($decodedObject->getStatus() == "STORED_ONLY"){
				$hazard = $this->getHazardById($decodedObject->getHazard_id());
				$childHazards = $hazard->getActiveSubHazards();
				foreach($childHazards as $child){
					//only do this for hazards the PI already has in this room.
					if($this->getHasHazardInLab($decodedObject->getPrincipal_investigator_id(), $child->getKey_id(), $decodedObject->getRoom_id())){
						$childDto = new PIHazardRoomDto();
						$childDto->setStatus("STORED_ONLY");
						$childDto->setContainsHazard(true);
						$childDto->setHazard_id($child->getKey_id());
						$childDto->setRoom_id($decodedObject->getRoom_id());
						$childDto->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
						$this->savePrincipalInvestigatorHazardRoomRelation($childDto);
					}
				}
			}

		}


		return $decodedObject;
	}
	/*
	 * Given a PIHazardRoomDTO, finds matching PrincipalInvestigatorHazardRoomRelation objects
	 * @param PIHazardRoomDTO roomDto
	 * @return Array <PrincipalInvestigatorHazardRoomRelation> relations
	 */

	private function getRelevantRelations(PIHazardRoomDTO $roomDto){

		$LOG = Logger::getLogger("asdfaf");

		$whereClauseGroup = new WhereClauseGroup(
				array(
					new WhereClause("hazard_id","=",$roomDto->getHazard_id()),
					new WhereClause("principal_investigator_id","=",$roomDto->getPrincipal_investigator_id()),
					new WhereClause("room_id","=",$roomDto->getRoom_id())
				)
		);
		$piHazardRoomDao =  $this->getDao(new PrincipalInvestigatorHazardRoomRelation());
		$relations = $piHazardRoomDao->getAllWhere($whereClauseGroup);
		return $relations;
	}

	public function getBuildingsByPIID($id, $roomId){
		$LOG = Logger::getLogger(__CLASS__);
		$id = $this->getValueFromRequest("id", $id);
		$roomId = $this->getValueFromRequest("roomId", $roomId);

		$pi = $this->getPIById($id);
		if($roomId == null){
			$rooms = $pi->getRooms();
		}else{
			$rooms = array($this->getRoomById($roomId));
		}

		$roomMaps = array();
		$roomMaps[] = EntityMap::lazy("getPrincipalInvestigators");
		$roomMaps[] = EntityMap::lazy("getHazards");
		$roomMaps[] = EntityMap::lazy("getHazard_room_relations");
		$roomMaps[] = EntityMap::lazy("getHas_hazards");
		$roomMaps[] = EntityMap::eager("getBuilding");
		$roomMaps[] = EntityMap::lazy("getSolidsContainers");

		foreach($rooms as $room){
			$room->setEntityMaps($roomMaps);
		}

		return $rooms;

	}

	public function getPisByRoomIDs( $roomIds = null, $hazardId = null){

		$LOG = Logger::getLogger(__Class__);

		if($roomIds == NULL){
			$roomIds = $this->getValueFromRequest('roomIds', $roomIds);
		}

		if($hazardId == NULL){
			$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
		}


		if($roomIds == NULL && $hazardId == NULL){
			return new ActionError("roomId and hazardId params both required");
		}
        $LOG->fatal("asdfasdfasdfasdfasdfasdf");

		$piDao = $this->getDao(new PrincipalInvestigator());
        $LOG->fatal($roomIds);
		$pis = $piDao->getPisByHazardAndRoomIDs($roomIds);


		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getLabPersonnel");
		$entityMaps[] = EntityMap::lazy("getRooms");
		$entityMaps[] = EntityMap::lazy("getDepartments");
		$entityMaps[] = EntityMap::lazy("getUser");
		$entityMaps[] = EntityMap::lazy("getInspections");
		$entityMaps[] = EntityMap::lazy("getActiveParcels");
		$entityMaps[] = EntityMap::lazy("getCarboyUseCycles");
		$entityMaps[] = EntityMap::lazy("getPurchaseOrders");
		$entityMaps[] = EntityMap::lazy("getSolidsContainers");
		$entityMaps[] = EntityMap::lazy("getPickups");
		$entityMaps[] = EntityMap::lazy("getScintVialCollections");
		$entityMaps[] = EntityMap::lazy("getCurrentScintVialCollections");
		$entityMaps[] = EntityMap::lazy("getOpenInspections");
		$entityMaps[] = EntityMap::lazy("getQuarterly_inventories");
		$entityMaps[] = EntityMap::lazy("getCurrentVerifications");
		$entityMaps[] = EntityMap::lazy("getVerifications");
		$entityMaps[] = EntityMap::lazy("getPi_authorization");
        $entityMaps[] = EntityMap::lazy("getWipeTests");


		foreach($pis as $pi){
			$pi->setEntityMaps($entityMaps);
		}

		return $pis;

	}


	public function getPisByHazardAndRoomIDs( $roomIds = null, $hazardId = null , $principalInvestigatorId = null){

		$LOG = Logger::getLogger(__Class__);

		if($roomIds == NULL){
			$roomIds = $this->getValueFromRequest('roomIds', $roomIds);
		}

		if($hazardId == NULL){
			$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
		}

		if($roomIds == NULL && $hazardId == NULL){
			return new ActionError("roomId and hazardId params both required");
		}

        if($principalInvestigatorId == NULL){
			$principalInvestigatorId = $this->getValueFromRequest('piId', $principalInvestigatorId);
		}

        $db = DBConnection::get();
        $newRoomIds = implode(',', array_fill(0, count($roomIds), '?'));

		$queryString = "SELECT principal_investigator_id
                        FROM principal_investigator_room a
                        LEFT JOIN principal_investigator b
                        ON a.principal_investigator_id = b.key_id
                        WHERE b.is_active = 1 AND a.room_id IN ( $newRoomIds ) group by a.principal_investigator_id";
		$stmt = DBConnection::prepareStatement($queryString);
        foreach ($roomIds as $k => $id){
		    $stmt->bindValue(($k+1), $id);
		}
		$stmt->execute();
		$piIds = array();
		while($id = $stmt->fetchColumn()){
			array_push($piIds,$id);
		}

        $newPiIds = implode(',', array_fill(0, count($piIds), '?'));
        $queryString = "SELECT a.*,
                        concat(c.first_name, ' ', c.last_name) as piName
                        FROM principal_investigator_hazard_room a
                        JOIN principal_investigator b
                        ON b.key_id = a.principal_investigator_id
                        JOIN principal_investigator_room d
                        ON d.principal_investigator_id = a.principal_investigator_id
                        AND d.room_id = a.room_id
                        JOIN erasmus_user c
                        ON c.key_id = b.user_id
                        WHERE a.room_id IN ( $newRoomIds )
                        AND a.principal_investigator_id IN ( $newPiIds )
                        AND a.hazard_id = ?";

		$stmt = DBConnection::prepareStatement($queryString);
        // bindvalue is 1-indexed, so $k+1

		foreach ($roomIds as $k => $id){
		    $stmt->bindValue(($k+1), $id);
		}
        $skips = $k+2;
        // bindvalue is 1-indexed, so $k+1
		foreach ($piIds as $k => $id){
		    $stmt->bindValue(($k+$skips), $id);
		}

        $stmt->bindValue(($k+$skips+1), $hazardId);
        $stmt->execute();
        $piHazRooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigatorHazardRoomRelation");


        // Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getHazard");
        foreach($piHazRooms as $pi){
            $pi->setEntityMaps($entityMaps);
        }

		return $piHazRooms;

	}

	private function getHasHazardInLab($piId, $hazardId, $roomId){
		$piHazardRoomDao = $this->getDao(new PrincipalInvestigatorHazardRoomRelation());

		$whereClauseGroup = new WhereClauseGroup(array(
			new WhereClause('principal_investigator_id', "=", $piId),
			new WhereClause('hazard_id', "=", $hazardId),
			new WhereClause('room_id', "=", $roomId)
		));

		return count($piHazardRoomDao->getAllWhere($whereClauseGroup)) > 0;
	}

    private function  getExists($objects, $piId, $roomId){
        foreach($objects as $object){
            if($object->getPrincipal_investigator_id() == $piId && $object->getRoom_id())return true;
        }
        return false;
    }

    public function getCabinetsByPi(){
        $LOG = Logger::getLogger(__CLASS__);
		$id = $this->getValueFromRequest("id", $id);
        if($id == null)return new ActionError("No Id provided");
        $db = DBConnection::get();

		$queryString = "SELECT a.* from biosafety_cabinet a
                        left join  equipment_inspection b
                        on a.key_id = b.equipment_id
                        left join principal_investigator_equipment_inspection c
                        on c.inspection_id = b.key_id
                        where b.equipment_class = 'BioSafetyCabinet'
                        AND c.principal_investigator_id = ?
                        AND a.Is_active = 1
                        GROUP BY a.key_id";
        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->bindValue(1, $id);
        $stmt->execute();
        $cabs = $stmt->fetchAll(PDO::FETCH_CLASS, "BioSafetyCabinet");
        $entityMaps[] = EntityMap::eager("getRoom");
        $entityMaps[] = EntityMap::lazy("getPrincipal_investigator");
        $entityMaps[] = EntityMap::eager("getPrincipalInvestigators");
        foreach($cabs as $cab){
            $cab->setEquipmentInspections(array($cab->grabMostRecentInspection()));
            foreach($cab->getEquipmentInspections() as $insp){
                $insp->setEntityMaps($entityMaps);
            }
        }
        return $cabs;

    }
}