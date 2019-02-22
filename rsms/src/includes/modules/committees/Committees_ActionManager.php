
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

	public function getAllPIs($rooms = NULL){
        return $this->getAllPIDetails();
    }

	public function getAllProtocols(){
		$dao = $this->getDao(new BioSafetyProtocol());
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
			$dao = $this->getDao(new BioSafetyProtocol());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided");
		}

	}

	public function saveProtocol( BioSafetyProtocol $decodedObject = NULL){
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
			
			$LOG->info("Saved protocol: $decodedObject");
			if( $LOG->isTraceEnabled()) {
				$LOG->trace($decodedObject);
			}

			return $decodedObject;
		}
	}


	//upload the document for a BiosafteyProtocol
	public function uploadProtocolDocument( $id = NULL){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        try{
            $filename = DocumentManager::processFileUpload();

            //get just the name of the file
            $name = basename($filename);

			//is this for a protocol that already exists?
			if($id == NULL){
				$LOG->trace("Protocol ID was not passed as function parameter; attempt to read from request");
				$id = $this->getValueFromRequest('id', $id);
			}

			//if so, update the path of that protocol now and save it.
			if($id != NULL){
				$LOG->info("Attach document to protocol $id");
				$protocolDao = $this->getDao( new BioSafetyProtocol() );
				$protocol = $this->getProtocolById( $id );
				$protocol->setReport_path( $name );
				$protocolDao->save($protocol);
				$LOG->info("Saved $protocol");
				if( $LOG->isTraceEnabled() ){
					$LOG->trace($protocol);
				}
			}

            //either way, return the name of the saved document so that it can be added to the client
            return $name;
        }
        catch( FailedUploadException $e ){
			return new ActionError($e->getMessage());
        }
        catch( UnsupportedFileTypeException $e ){
			return new ActionError($e->getMessage(), $e->getCode());
        }
        catch( IOException $e ){
			return new ActionError($e->getMessage(), $e->getCode());
		}
	}
}

?>