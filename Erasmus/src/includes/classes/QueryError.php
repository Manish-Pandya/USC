<?php

// Holds error relating to SELECTs while querying database
class QueryError extends ActionError {
	public $errorDetails;

	// $error is an array containing error details generated by PDOStatement::errorInfo()

	public function __construct( $error ) {
		$this->errorDetails = $error;

		// $error[2] is the human readable message, other details are error codes
		parent::__construct($error[2]);
	}

	public function getErrorDetails() {
		return $this->errorDetails;
	}

	public function setErrorDetails($details) {
		$this->errorDetails = $details;
	}
}
?>