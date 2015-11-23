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
	 * @param PIHazardRoomDto $decodedObject
	 * @return PIHazardRoomDto $dto
	 */
	public function savePIHazardRoomMappings($decodedObject = NULL){
		$LOG = Logger::getLogger(__CLASS__);
		
		if($decodedObject == null){
			$decodedObject = $this->convertInputJson();
		}
		
		if($decodedObject == null){
			return new ActionError("No DTO");
		}
		
		if($decodedObject->getIsPresent() == false){
			//delete all the PrincipalInvestigatorHazardRoomRelations
			foreach($decodedObject->getInspectionRooms() as $room){
				$relations = $this->getRelevantRelations($decodedObject, $room);				
				foreach($relations as $relation){
					$piHazardRoomDao->deleteById($relation->getKey_id());
				}
			}
			
	
		}else{
			foreach($decodedObject->getInspectionRooms() as $room){
				//get the relevant PrincipalInvestigatorHazardRoomRelation
				$relations = $this->getRelevantRelations($decodedObject, $room);
				
				//room doesn't contain the relevant hazard, so delete any relations
				if($room->getContainsHazard() == false){
					foreach($relations as $relation){
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
							$relation->setStatus($room["Status"]);
							$piHazardRoomDao->save($relation);
						}
					}
					//no previous relation.  make a new one and save it
					else{
						
						$room["ContainsHazard"] = true;
						
						$relation = new PrincipalInvestigatorHazardRoomRelation();
						$relation->setPrincipal_investigator_id($decodedObject->getPrincipal_investigator_id());
						$relation->setHazard_id($decodedObject->getHazard_id());
						$relation->setRoom_id($room["Key_id"]);
						$relation->setStatus($decodedObject->getStatus());
						$relation->setStatus($room["Status"]);
						$piHazardRoomDao->save($relation);
					}
				}
			}
		}
		return $decodedObject;
	}
	
	private function getRelevantRelations($hazardDto, $roomDto){
		$whereClauseGroup = new WhereClauseGroup(
				new WhereClause("hazard_id","=",$decodedObject->getHazard_id()),
				new WhereClause("principal_investigator_id","=",$decodedObject->getPrincipal_investigator_id()),
				new WhereClause("room_id","=",$roomDto["Key_id"])
		);
			
		$piHazardRoomDao =  $this->getDao(new PrincipalInvestigatorHazardRoomRelation());
		$relations = $piHazardRoomDao->getAllWhere($whereClauseGroup);
		return $relations;
	}
}