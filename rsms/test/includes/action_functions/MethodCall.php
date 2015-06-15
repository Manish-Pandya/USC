<?php
/**
 * Used by GenericDaoSpy for keeping track of past method calls
 * 
 * @author Perry
 */

class MethodCall {
	
	private $method;
	private $args;
	private $timeCalled;
	
	public function __construct($methodName, $arrayOfArguments) {
		$this->method = $methodName;
		$this->timeCalled = new DateTime();
		$this->args = $arrayOfArguments;
	}
	
	public function getMethod() { return $this->method; }
	public function getTimeCalled() { return $this->timeCalled;	}
	public function getAllArgs() { return $this->args; }
	public function getArg($argNumber) {
		$args = $this->getAllArgs();
		return $args[$argNumber];
	}
}