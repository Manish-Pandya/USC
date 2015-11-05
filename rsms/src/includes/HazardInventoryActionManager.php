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
	
	/**
	 * Given a PIHazardRoomDto, creates or deletes relevant relationships between PI, Hazard, and Room
	 *
	 * @param PIHazardRoomDto $decodedObject
	 * @return PIHazardRoomDto $dto
	 */
	public function savePIHazardRoomMappings($decodedObject = NULL){
		//$decodedObject = $this->convertInputJson();
		 
		if($decodedObject->getIsPresent() == false){
			//delete all the PrincipalInvestigatorHazardRoomRelations
			foreach($decodedObject->getInspectionRooms() as $room){
				//get the relevant PrincipalInvestigatorHazardRoomRelation
				$whereClauseGroup = new WhereClauseGroup(
						new WhereClause("hazard_id","=",$decodedObject->getHazard_id()),
						new WhereClause("principal_investigator_id","=",$decodedObject->getHazard_id()),
						new WhereClause("room_id","=",$room->getKey_id())
				);
				 
				$piHazardRoomDao =  $this->getDao(new PrincipalInvestigatorHazardRoomRelation());
				$relations = $piHazardRoomDao->getAllWhere($whereClauseGroup);
				foreach($relations as $relation){
					$piHazardRoomDao->deleteById($relation->getKey_id());
				}
			}
	
		}else{
			//delete all the PrincipalInvestigatorHazardRoomRelations
			foreach($decodedObject->getInspectionRooms() as $room){
				//get the relevant PrincipalInvestigatorHazardRoomRelation
				$whereClauseGroup = new WhereClauseGroup(
						new WhereClause("hazard_id","=",$decodedObject->getHazard_id()),
						new WhereClause("principal_investigator_id","=",$decodedObject->getHazard_id()),
						new WhereClause("room_id","=",$room->getKey_id())
				);
				 
				$piHazardRoomDao =  $this->getDao(new PrincipalInvestigatorHazardRoomRelation());
				$relations = $piHazardRoomDao->getAllWhere($whereClauseGroup);
				foreach($relations as $relation){
					$relation = new PrincipalInvestigatorHazardRoomRelation();
					$room = new Room();
					if($room->getContainsHazard() == false){
						$piHazardRoomDao->deleteById($relation->getKey_id());
					}else{
						$relation->setRoom_id($room->getKey_id());
						//$relation->set7
					}
				}
			}
		}
	}
}