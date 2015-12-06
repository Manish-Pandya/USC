
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
    
    public function getAllBioSafetyCabinets(){
    	$bioSafetyCabinetDao = $this->getDao(new BioSafetyCabinet());
    	return $bioSafetyCabinetDao->getAll();
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
            $dao->save($decodedObject);
            return $decodedObject;
        }
    }
    
    public function getBioSafetyCabinetById( $id = NULL ){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );
    
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
}

?>