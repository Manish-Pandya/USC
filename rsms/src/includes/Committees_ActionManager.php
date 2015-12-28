
<?php
/**
 * Contains action functions specific to the Committees module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt Breeden
 */
class Committees_ActionManager extends ActionManager {


    /*****************************************************************************\
     *                            Get Functions                                  *
    \*****************************************************************************/
 
	
	public function getAllProtocols(){
		$dao = $this->getDao(new BiosafetyProtocol());
		return $dao->getAll();
	}
	
	/*
	 * @param int $id
	 * 
	 */	
	public function getProtocolById( $id = NULL ){
		
		if($id == NULL){
			$id = $this->getValueFromRequest('id', $id);
		}
		
		if( $id !== NULL ){
			$dao = $this->getDao(new BiosafetyProtocol());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided");
		}
		
	}
	
	public function saveProtocol( BiosafetyProtocol $decodedObject = NULL){
		$LOG = Logger::getLogger('Action:' . __function__);
		if( $decodedObject === NULL ){
			$decodedObject = $this->convertInputJson();
		}
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Deficiency');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new BiosafetyProtocol());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}
	
	//upload the document for a BiosafteyProtocol
	public function uploadProtocolDocument(){
		
		//verify that this file is of a type we consider safe
		
		//is this for a protocol that already exists?
		//if so, update the path of that protocol now and save it.
		//find and delete the old protocol, if there is one
				
		//either way, return the path to the saved document so that it can be added to the client
	}
}

?>