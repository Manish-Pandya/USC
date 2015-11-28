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
	
	public function getHazardRoomDtosByPIId($piId = null) {
		 
		if($piId == NULL){
			$piId = $this->getValueFromRequest('id', $piId);
		}
		 
		if( $piId !== NULL ){
			$dao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());
			return $dao->getHazardRoomDtosByPIId($piId);
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
	
		//get the relevant PrincipalInvestigatorHazardRoomRelation
		$relations = $this->getRelevantRelations($decodedObject);
		$LOG->fatal($decodedObject);
		//room doesn't contain the relevant hazard, so delete any relations
		if($decodedObject->getContainsHazard() == false){
			foreach($relations as $relation){
				$LOG->fatal('here?');
				$piHazardRoomDao->deleteById($relation->getKey_id());
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
				$LOG->fatal('relations null');
				$decodedObject->setContainsHazard(true);
	
				$relation = new PrincipalInvestigatorHazardRoomRelation();
				$relation->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
				$relation->setHazard_id($decodedObject->getHazard_id());
				$relation->setRoom_id($decodedObject->getRoom_id());
				$relation->setStatus($decodedObject->getStatus());
				$relation = $piHazardRoomDao->save($relation);
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
	
	public function getBuildingsByPIID(){
		$LOG = Logger::getLogger(__CLASS__);
		$id = $this->getValueFromRequest("id", $id);
		$pi = $this->getPIById($id);
		$rooms = $pi->getRooms();
		$buildings = array();
		
		$roomMaps = array();
		$roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		$roomMaps[] = new EntityMap("lazy","getHazards");
		$roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
		$roomMaps[] = new EntityMap("lazy","getHas_hazards");
		$roomMaps[] = new EntityMap("lazy","getBuilding");
		$roomMaps[] = new EntityMap("lazy","getSolidsContainers");
		
		$buildingMaps = array();
		$buildingMaps[] = new EntityMap("eager","getRooms");
		$buildingMaps[] = new EntityMap("lazy","getCampus");
		$buildingMaps[] = new EntityMap("lazy","getCampus_id");
		$buildingMaps[] = new EntityMap("lazy","getPhysical_address");
            foreach($rooms as $room){
                if(!in_array($room->getBuilding(), $buildings)){
                    $buildings[] = $room->getBuilding();
                }
            }

            foreach($buildings as $building){
                $rooms = array();
                foreach($pi->getRooms() as $room){
                    if($room->getBuilding_id() == $building->getKey_id()){
                        $room->setEntityMaps($roomMaps);
                        $rooms[] = $room;
                    }
                }
                $building->setEntityMaps($buildingMaps);
                $building->setRooms($rooms);
            }

            $pi->setBuildings($buildings);
		return $pi->getBuildings();
	}
}