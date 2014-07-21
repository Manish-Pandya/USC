<?php

class ActionError {
	public $message;
	
	private $LOG;

	public function __construct( $message ){
		$this->message = $message;
		$this->LOG = Logger::getLogger( __CLASS__ );

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
			return NULL;
		}
	}
}

?>