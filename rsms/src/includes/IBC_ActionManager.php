
<?php
/**
 * Contains action functions specific to the Committees module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt Breeden
 */
class IBC_ActionManager extends ActionManager {


    /*****************************************************************************\
     *                            Get Functions                                  *
    \*****************************************************************************/

    /*
     * Filters protocols and relevant data based on whether currently signed in user has Admin permissions or persmissions to retrieve and save protcols from a single lab
     */
    private function filterByPermission(){
        
    }

	public function getAllProtocols(){
		$dao = $this->getDao(new IBCProtocol());
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
			$dao = $this->getDao(new IBCProtocol());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided");
		}

	}

	public function saveProtocol( IBCProtocol $decodedObject = NULL){
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
			$dao = $this->getDao(new BioSafetyProtocol());
			$decodedObject = $dao->save($decodedObject);
			$LOG->fatal($decodedObject);
			return $decodedObject;
		}
	}

}

?>