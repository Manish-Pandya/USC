<?php
/**
 * Contains action functions specific to the verification module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt Breeden
 */
?><?php

class Verification_ActionManager extends ActionManager  {

    /***
     * 
     *    ANNUAL VERIFICATION
     * 
     */
    
    //Labs can't create verification
    public function saveVerification($verification = NULL){
    	$LOG = Logger::getLogger('Action:' . __function__);
    	if($verification !== NULL) {
    		$decodedObject = $verification;
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
    		$dao = $this->getDao(new Verification());
    		$dao->save($decodedObject);
    		return $decodedObject;
    	}
    }
    
    //since labs can't create verifications, they call this function when they are finished with one
    public function closeVerification($id = NULL, $timestamp = NULL){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );
    	 
    	if($id == NULL)$id = $this->getValueFromRequest('id', $id);
    	if($timestamp == NULL)$timestamp = $this->getValueFromRequest('date', $id);
    	 
    	if( $id !== NULL &&  $timestamp !== NULL ){
    		$dao = $this->getDao(new Verification());
    		$verification = $dao->getById($id);
    		$verification->setCompleted_date($timestamp);
    		return $dao->save($verification);
    	}
    	else{
    		//error
    		return new ActionError("No request parameter 'id' was provided");
    	}
    }
    
    public function getVerificationById($id = NULL){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );
    	
    	if($id == NULL)$id = $this->getValueFromRequest('id', $id);
    	
    	if( $id !== NULL ){
    		$dao = $this->getDao(new Verification());
    		return $dao->getById($id);
    	}
    	else{
    		//error
    		return new ActionError("No request parameter 'id' was provided");
    	}
    }
    
    public function getPIForVerification(){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );
    	//$LOG->fatal('called it');
    	if($id == NULL)$id = $this->getValueFromRequest('id', $id);
    	 
    	if( $id !== NULL ){
    		$dao = $this->getDao(new PrincipalInvestigator());
            $pi = $dao->getById($id);

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

            $rooms = $pi->getRooms();
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
    		
    		$entityMaps = array();
    		$entityMaps[] = new EntityMap("eager","getLabPersonnel");
    		$entityMaps[] = new EntityMap("eager","getUser");
    		$entityMaps[] = new EntityMap("eager","getCurrentVerifications");
    		$entityMaps[] = new EntityMap("eager","getBuildings");
    		
    		$entityMaps[] = new EntityMap("lazy","getDepartments");
    		$entityMaps[] = new EntityMap("lazy","getInspections");
    		$entityMaps[] = new EntityMap("lazy","getPi_authorization");
    		$entityMaps[] = new EntityMap("lazy","getActiveParcels");
    		$entityMaps[] = new EntityMap("lazy","getCarboyUseCycles");
    		$entityMaps[] = new EntityMap("lazy","getPurchaseOrders");
    		$entityMaps[] = new EntityMap("lazy","getSolidsContainers");
    		$entityMaps[] = new EntityMap("lazy","getPickups");
    		$entityMaps[] = new EntityMap("lazy","getScintVialCollections");
    		$entityMaps[] = new EntityMap("lazy","getCurrentScintVialCollections");
    		$entityMaps[] = new EntityMap("lazy","getOpenInspections");
    		$entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
    		$entityMaps[] = new EntityMap("lazy","getVerifications");
    		$entityMaps[] = new EntityMap("lazy","getRooms");
    		
    		
    		$pi->setEntityMaps($entityMaps);
    		return $pi;
    	}
    	else{
    		//error
    		return new ActionError("No request parameter 'id' was provided");
    	}
    }
    
    public function savePendingUserChange(PendingUserChange $pendingUserChange = NULL){
    	$LOG = Logger::getLogger('Action:' . __function__);
    	if($pendingUserChange !== NULL) {
    		$decodedObject = $pendingUserChange;
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
    		//$LOG->fatal($decodedObject);
    		$dao = $this->getDao(new PendingUserChange());
    		$change = $dao->save($decodedObject);
    		return $change;
    	}
    }
    public function savePendingRoomChange(PendingRoomChange $pendingRoomChange = NULL){
    	$LOG = Logger::getLogger('Action:' . __function__);
    	if($pendingRoomChange !== NULL) {
    		$decodedObject = $pendingRoomChange;
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
    		$dao = $this->getDao(new PendingRoomChange());
    		$dao->save($decodedObject);
    		return $decodedObject;
    	}
    }
    
    public function savePendingHazardChange(PendingHazardChange $pendingHazardChange = NULL){
    	$LOG = Logger::getLogger('Action:' . __function__);
    	if($pendingHazardChange !== NULL) {
    		$decodedObject = $pendingHazardChange;
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
    		$dao = $this->getDao(new PendingHazardChange());
    		$dao->save($decodedObject);
    		return $decodedObject;
    	}
    }
    
    public function confirmPendingUserChange(PendingUserChange $pendingUserChange = Null){
    	$LOG = Logger::getLogger('Action:' . __function__);
    	if($pendingUserChange !== NULL) {
    		$decodedObject = $pendingUserChange;
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
    		if($decodedObject->getParent_id() == NULL){
    			return new ActionError('This user doesn\'t exist.  Please create a user account in the User Hub');
    		}
    		$userDao = $this->getDao(new User());    		
    		$user = $userDao->getById($decodedObject->getParent_id());
    		$phone = $this->getValueFromRequest('phone', $phone);
	    	if($phone == NULL){
	    		$status = strtolower( $decodedObject->getNew_status() );
	    		
	    		
	    		if($status == "still in this lab, but no longer a contact"){
	    			
	    			//get the lab contact role by name
	    			$whereClauseGroup = new WhereClauseGroup(
	    				array(new WhereClause("name","=","Lab Contact"))
	    			);
	    			$roleDao = $this->getDao(new Role());
	    			$roles = $roleDao->getAllWhere($whereClauseGroup);
	    			
	    			//remove lab contact role
	    			$relation = new RelationshipDto();
	    			$relation->setMaster_id($user->getKey_id());
	    			$relation->setRelation_id($roles[0]->getKey_id());
	    			$relation->setAdd(false);
	    			$this->saveUserRoleRelation($relation);
	    			
	    			
	    		}elseif($status == "still in this lab, but now a lab contact"){
	    			
	    			//get the lab contact role by name
	    			$whereClauseGroup = new WhereClauseGroup(
	    					array(new WhereClause("name","=","Lab Contact"))
	    			);
	    			$roleDao = $this->getDao(new Role());
	    			$roles = $roleDao->getAllWhere($whereClauseGroup);
	    			
	    			//add lab contact role
	    			$relation = new RelationshipDto();
	    			$relation->setMaster_id($user->getKey_id());
	    			$relation->setRelation_id($roles[0]->getKey_id());
	    			$relation->setAdd(true);
	    			$this->saveUserRoleRelation($relation);
	    		}elseif($status == "in another pi's lab"){    			 
	    			//remove supervisor id
	    			$user->setSupervisor_id(null);
	    			$userDao->save($user);
	    			 
	    		}elseif($status == "no longer at the univserity"){
	    			//deactivate user
	    			$user->setIs_active(false);
	    			$userDao->save($user);
	    		}else{
	    			return $decodedObject;
	    		}
	    		$decodedObject->setApproval_date(date("Y-m-d H:i:s"));
    		}else{
    			$user->setEmergency_phone($decodedObject->getEmergency_phone());
    			$userDao->save($user);
    			$decodedObject->setPhone_approved(true);
    		}
    		
    		$dao = $this->getDao(new PendingUserChange());
    		$change = $dao->save($decodedObject);
    		
    		return $change;
    	}
    }
    
    public function confirmPendingRoomChange(PendingRoomChange $pendingRoomChange = NULL){
    	$LOG = Logger::getLogger('Action:' . __function__);
    	if($pendingRoomChange !== NULL) {
    		$decodedObject = $pendingRoomChange;
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
    		if($decodedObject->getParent_id() == NULL){
    			return new ActionError('This user doesn\'t exist.  Please create a user account in the User Hub');
    		}
    		$userDao = $this->getDao(new Room());
    		$room = $userDao->getById($decodedObject->getParent_id());
    		$verDao = $this->getDao(new Verification());
    		$verification = $verDao->getById($decodedObject->getVerification_id());
    		$this->savePIRoomRelation($verification->getPrincipal_investigator_id(), $room->getKey_id(), ($decodedObject->getNew_status() == 'Adding') );
    		
    		$decodedObject->setApproval_date(date("Y-m-d H:i:s"));
    		
    		$dao = $this->getDao(new PendingRoomChange());
    		$dao->save($decodedObject);
    		return $decodedObject;
    	}
    }
    
    public function confirmPendingHazardChange(PendingHazardChange $pendingHazardChange = NULL){
    	 
    }
}
?>
