<?php

// Holds errors related to INSERTs, UPDATEs, etc. to the database
class ModifyError extends ActionError {
	public $attemptedObject;

	public function __construct($message, $attemptedObject) {
		parent::__construct($message);
		$this->attemptedObject = $attemptedObject;
	}

	public function getAttemptedObject(){ return $this->attemptedObject; }
	public function setAttemptedObject($a){ $this->attemptedObject = $a; }
}

?>