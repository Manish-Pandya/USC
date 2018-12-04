
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
		$LOG = Logger::getLogger('Action:' . __function__);
		//verify that this file is of a type we consider safe

		// Make sure the file upload didn't throw a PHP error
		if ( isset($_FILES[0]) && $_FILES[0]['error'] != 0) {
			return new ActionError("File upload error.");
		}
		/*
		// Make sure it was an HTTP upload
		if (!is_uploaded_file($_FILES[$fieldname]['tmp_name'])) {
			return new ActionError("Not a valid upload method.");
		}
		*/
		//validate the file, make sure it's a .doc or .pdf
		//check the extension
		$valid_file_extensions = array("doc","docx","pdf");
		$file_extension = strtolower( substr( $_FILES['file']["name"], strpos($_FILES['file']["name"], "." ) + 1) ) ;

		if (!in_array($file_extension, $valid_file_extensions)) {
			return new ActionError("Not a valid file extension");
		}else{
			//make sure the file actually matches the extension, as best we can
			$finfo = new finfo(FILEINFO_MIME);
			$type = $finfo->file($_FILES['file']["tmp_name"]);
			$match = false;
			foreach($valid_file_extensions as $ext){
				if(strstr($type, $ext)){
					$match = true;
				}
			}
			if($match == false){
				return new ActionError("Not a valid file");
			}
		}

		// Start by creating a unique filename using timestamp.  If it's
		// already in use, keep incrementing the timstamp until we find an unused filename.
		// 99.999% of the time, this should work the first time, but better safe than sorry.
		$LOG->info("Process file upload: " . $_FILES['file']['name']);
        $LOG->debug(str_replace("#","",$_FILES['file']));
		$now = time();
		while(file_exists($filename = BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR . $now.'-'.str_replace("#","",$_FILES['file']['name'])))
		{
			$now++;
		}

		// Write the file
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filename) != true) {
			return new ActionError("Directory permissions error for " . BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR);
		}


		/////////////////////////////////////
		//
		// return the name of the file, as it was saved on the server, saving the relevant protocol if one exists already
		//
		////////////////////////////////////

		//is this for a protocol that already exists?
		if($id == NULL){
			$id = $this->getValueFromRequest('id', $id);
		}
		$LOG->info("Save file to $filename");
		//get just the name of the file
		$name = basename($filename);

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
}

?>