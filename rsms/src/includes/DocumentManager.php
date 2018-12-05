<?php

?><?php

class FailedUploadException extends Exception {}
class UnsupportedFileTypeException extends Exception {}
class IOException extends Exception {}

class DocumentManager {

    public static $VALID_FILE_TYPES = array(
        'pdf'  => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'doc'  => 'application/msword',
    );

    public static function processFileUpload(){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // TODO: Pass upload directory as param?
        $uploadbase = BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR;

		// Make sure the file upload didn't throw a PHP error
        $LOG->debug("Validate file upload");
		if ( isset($_FILES[0]) && $_FILES[0]['error'] != 0) {
            throw new FailedUploadException("File upload error.");
		}

		//check the extension
        $LOG->debug("Validate file extension");
        $file_extension = strtolower( substr( $_FILES['file']["name"], strpos($_FILES['file']["name"], "." ) + 1) );

        if (!array_key_exists($file_extension, self::$VALID_FILE_TYPES)) {
            $LOG->fatal("Not a valid file extension: $file_extension");
            throw new UnsupportedFileTypeException("Not a valid file extension: $file_extension", 415);
		}
		else{
			//make sure the file actually matches the extension, as best we can
            $LOG->debug("Validate file mimetype");
			$finfo = new finfo(FILEINFO_MIME);
			$type = $finfo->file($_FILES['file']["tmp_name"]);
            $match = false;
            $LOG->debug("Checking file type '$type' against our valid extensions");
			foreach(self::$VALID_FILE_TYPES as $ext => $mime){
				if(strstr($type, $mime)){
					$match = true;
                }

                $LOG->trace("$type = $mime" . ($match ? ' : MATCHED' : ''));

                if($match){
                    break;
                }
            }

			if($match == false){
                throw new UnsupportedFileTypeException("Not a valid file", 415);
            }
        }

        $LOG->info("Process file upload: " . $_FILES['file']['name']);
        if( $LOG->isTraceEnabled() ){
            $LOG->trace(str_replace("#","",$_FILES['file']));
        }

        // Start by creating a unique filename using timestamp.  If it's
		// already in use, keep incrementing the timstamp until we find an unused filename.
        // 99.999% of the time, this should work the first time, but better safe than sorry.
        $LOG->debug("Generate unique name...");
		$now = time();
		while(file_exists($filename = $uploadbase . $now.'-'.$_FILES['file']['name']))
		{
			$now++;
        }

        // Write the file
        $LOG->debug("Write file...");
		if (move_uploaded_file($_FILES['file']['tmp_name'], $filename) != true) {
            $LOG->error("Unable to write upload file $filename");
            throw new UnsupportedFileTypeException("Unable to write file", 500);
		}

        $LOG->info("Saved document: $filename");
        return $filename;
    }
}
?>
