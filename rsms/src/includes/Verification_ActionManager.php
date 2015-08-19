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
    		$dao = $this->getDao(new PendingUserChange());
    		$dao->save($decodedObject);
    		return $decodedObject;
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
    		$status = strtolower( $decodedObject->getNew_status() );
    		
    		if($status == "no longer a lab contact"){
    			
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
    			
    			
    		}elseif($status == "now a lab contact"){
    			
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
    		}elseif($status == "no longer works in this lab"){
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
    		$dao->save($decodedObject);
    		return $decodedObject;
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
    		$this->savePIRoomRelation($this->getPIIDFromObject($room), $room->getKey_id(), $decodedObject->getAdding());
    		
    		$decodedObject->setApproval_date(date("Y-m-d H:i:s"));
    		$dao->save($decodedObject);
    		return $decodedObject;
    	}
    }
    
    public function confirmPendingHazardChange(PendingHazardChange $pendingHazardChange = NULL){
    	 
    }
}
?>
