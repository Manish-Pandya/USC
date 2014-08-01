<?php

class ActionError {
	// This constant is what the frontend will check for to see if it got an error back.
	define("isError", true);

	public $message;
	private $LOG;

	public function __construct( $message ){
		$this->message = $message;
		$this->LOG = Logger::getLogger( __CLASS__ );
		// TODO: possibly log what created this instance of ActionError, if possible?

	}
	
	public function __toString(){
		return "[ActionError: $this->message]";
	}
	
	public function getMessage(){ return $this->message; }
	public function setMessage($m){ $this->message = $m; }

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