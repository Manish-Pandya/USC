<?php

class ActionError {
	public $message;
	protected $statusCode;

	public function __construct( $message, $statusCode = 000) {
		$this->message = $message;
		$this->statusCode = $statusCode;

		// log what created this instance of ActionError
		$this->logStack("$this created at");
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
			$LOG = Logger::getLogger(__CLASS__);
			$LOG->error($this . ' Method ' . $method . ' does not exist on object of type ' . get_class());
			$this->logStack("Call Stack");

			// Thought for future self: Would it make more sense to return an error instead?
			// or would that be redundant?
			return NULL;
		}
	}

	private function logStack($msg){
		$LOG = Logger::getLogger(__CLASS__);
		LogUtil::log_stack($LOG, $msg, 'trace');
	}
}

?>