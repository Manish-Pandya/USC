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
	
	public function getHazardRoomDtosByPIId($piId = null, $roomId = null) {
		 
		if($piId == NULL){
			$piId = $this->getValueFromRequest('id', $piId);
		}
		
		if($roomId == NULL){
			$roomId = $this->getValueFromRequest('roomId', $roomId);
		}
		 
		if( $piId !== NULL ){
			$dao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());
			if($roomId == null){
				return $dao->getHazardRoomDtosByPIId($piId);
			}else{
				return $dao->getHazardRoomDtosByPIId($piId, $roomId);
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
		$LOG = Logger::getLogger("asdfasdfasdf");
				
		if($decodedObject == null){
			$decodedObject = $this->convertInputJson();
		}
		
		if($decodedObject == null){
			return new ActionError("No DTO");
		}
		
		foreach($decodedObject->getInspectionRooms() as $roomDto){
			$this->savePrincipalInvestigatorHazardRoomRelation($roomDto);
		}
		
		return $decodedObject;
	}
	
	/*
	* Creates, updates or deletes a PIHazardRoomDto object, depending on its ContainsHazard property and whether or not it already exists or needs to be created anew
	* @ param PIHazardRoomDto decodedObject
	* @ return PIHazardRoomDto dto
	*/
public function savePrincipalInvestigatorHazardRoomRelation( PIHazardRoomDto $decodedObject = null ){
		$LOG = Logger::getLogger("asdfaf");
	
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
			
			// If this is a master category, remove the appropriate master category flag from the Room
			// Case 1, Biohazards
			if($decodedObject->getHazardId() == 1){
				$room = $roomDao->getById($decodedObject->getRoom_id());
				$room->setBio_hazards_present(false);
				$roomDao->save($room);
			}
			// Case 2, Checm hazards
			if($decodedObject->getHazardId() == 9999){
				$room = $roomDao->getById($decodedObject->getRoom_id());
				$room->setChem_hazards_present(false);
				$roomDao->save($room);
			}
			// Case 3, Rad hazards
			if($decodedObject->getHazardId() == 10009){
				$room = $roomDao->getById($decodedObject->getRoom_id());
				$room->setRad_hazards_present(false);
				$roomDao->save($room);
			}
				
			//since we've removed the hazard from the room for this PI, we should also remove any child hazards
			$hazard = $this->getHazardById($decodedObject->getHazard_id());
			$childHazards = $hazard->getActiveSubHazards();
			foreach($childHazards as $child){
				$childDto = new PIHazardRoomDto();
				$childDto->setContainsHazard(0);
				$childDto->setHazard_id($child->getKey_id());
				$childDto->setRoom_id($decodedObject->getRoom_id());
				$childDto->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
				$LOG->fatal($childDto);				
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
				$LOG->fatal($relation);
			}
			//if we have set the status to "Stored Only", we must also set the status to for each child hazard in this room
			if($decodedObject->getStatus() == "Stored Only"){
				$hazard = $this->getHazardById($decodedObject->getHazard_id());
				$childHazards = $hazard->getActiveSubHazards();
				foreach($childHazards as $child){
					//only do this for hazards the PI already has in this room.
					if($this->getHasHazardInLab($decodedObject->getPrincipal_investigator_id(), $child->getKey_id(), $decodedObject->getRoom_id())){
						$childDto = new PIHazardRoomDto();
						$childDto->setStatus("Stored Only");
						$childDto->setContainsHazard(true);
						$childDto->setHazard_id($child->getKey_id());
						$childDto->setRoom_id($decodedObject->getRoom_id());
						$childDto->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
						$LOG->fatal($childDto);
						$this->savePrincipalInvestigatorHazardRoomRelation($childDto);
					}
				}
			}

			// Flag the master category on the room object
			
				$room = $roomDao->getById($decodedObject->getRoom_id());
					if ($decodedObject->getMasterHazardId() == 1) {
					$room->setBio_hazards_present(true);
				}
					if ($decodedObject->getMasterHazardId() == 9999) {
					$room->setChem_hazards_present(true);
				}
					if ($decodedObject->getMasterHazardId() == 10009) {
					$room->setRad_hazards_present(true);
				}

				$roomDao->save($room);

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
	
	public function getBuildingsByPIID(){
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
		$roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		$roomMaps[] = new EntityMap("lazy","getHazards");
		$roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
		$roomMaps[] = new EntityMap("lazy","getHas_hazards");
		$roomMaps[] = new EntityMap("eager","getBuilding");
		$roomMaps[] = new EntityMap("lazy","getSolidsContainers");
		
		foreach($rooms as $room){
			$room->setEntityMaps($roomMaps);
		}
		
		return $rooms;
	
	}
	
	public function getPisByHazardAndRoomIDs( $roomIds = null, $hazardId = null){

		$LOG = Logger::getLogger(__Class__);
		
		if($roomIds == NULL){
			$roomIds = $this->getValueFromRequest('roomIds', $roomIds);
		}
		
		if($hazardId == NULL){
			$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
		}
		
		$LOG->fatal($roomIds);
		$LOG->fatal($hazardId);
		
		if($roomIds == NULL && $hazardId == NULL){
			return new ActionError("roomId and hazardId params both required");
		}
		
		$piDao = $this->getDao(new PrincipalInvestigator());
		
		if($hazardId != null){
			$pis = $piDao->getPisByHazardAndRoomIDs($roomIds, $hazardId);
		}else{
			$pis = $piDao->getPisByHazardAndRoomIDs($roomIds);
		}
		
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getLabPersonnel");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("lazy","getDepartments");
		$entityMaps[] = new EntityMap("lazy","getUser");
		$entityMaps[] = new EntityMap("lazy","getInspections");
		$entityMaps[] = new EntityMap("lazy", "getActiveParcels");
		$entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
		$entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
		$entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
		$entityMaps[] = new EntityMap("lazy", "getPickups");
		$entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
		$entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
		$entityMaps[] = new EntityMap("lazy","getOpenInspections");
		$entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
		$entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
		$entityMaps[] = new EntityMap("lazy","getVerifications");
		$entityMaps[] = new EntityMap("lazy","getPi_authorization");
		
		foreach($pis as $pi){
			$pi->setEntityMaps($entityMaps);
		}
		
		return $pis;
			
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
}