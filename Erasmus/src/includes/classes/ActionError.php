<?php

class ActionError {
	public $message;
	protected $statusCode;
	private $LOG;

	public function __construct( $message, $statusCode ) {
		$this->message = $message;
		$this->statusCode = $statusCode;
		$this->LOG = Logger::getLogger( __CLASS__ );
		// TODO: possibly log what created this instance of ActionError, if possible?

	}
	
	public function __toString() {
		return "[ActionError: $this->message]";
	}
	
	public function getMessage() { return $this->message; }
	public function setMessage($m) { $this->message = $m; }
	
	public function getStatusCode() { return $this->statusCode; }
	public function setStatusCode($newCode) { $this->statusCode = $newCode; }

	// tells JSON encoder to give ActionError a property IsError with value true.
	// This will give the frontend something to check to see if the value it gets
	// back is an error.
	public function getIsError() { return true; }

	// prevent any undefined method exceptions if ActionError is passed to a
	// function that expects another type of object.
	public function __call($method, $args) {
		if(!isset($this->$method)) {
			$this->LOG->error('Method ' . $method . ' does not exist on object of type ' . get_class());
			// Thought for future self: Would it make more sense to return an error instead?
			// or would that be redundant?
			return NULL;
		}
	}
}

?>