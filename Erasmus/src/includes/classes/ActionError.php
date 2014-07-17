<?php

class ActionError {
	public $message;
	
	public function __construct( $message ){
		$this->message = $message;
	}
	
	public function __toString(){
		return "[ActionError: $this->message]";
	}
	
	public function getMessage(){ return $this->message; }
	public function setMessage($m){ $this->message = $m; }
}

?>