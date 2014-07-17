<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/ErrorHandler.php');
require_once(dirname(__FILE__) . '/../../src/logging/Logger.php');

Mock::generate('Logger');
class TestErrorHandler extends UnitTestCase {
	
	/**
	 * reads the last line from the given file
	 */
	static function readLastLine($filename){
		$line = '';
		
		//echo "Opening file $filename in " . dirname($filename) . "\n";
		$f = fopen($filename, 'r');
		$cursor = -1;
		
		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);
		
		// Trim trailing newline chars of the file
		while ($char === "\n" || $char === "\r") {
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		
		// Read until the start of file or first newline char
		while ($char !== false && $char !== "\n" && $char !== "\r") {
			// Prepend the new char
			$line = $char . $line;
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		
		return $line;
	}
	
	public function test_handleException(){
		$errorMessage = 'Testing Error Handler: Handle Exception';
		
		//build exception to handle
		$exception = new ErrorException( $errorMessage );
		
		//build handler
		$handler = new ErrorHandler();
		
		//test exception handling
		$handler->handleException($exception);
		
		//Test that exception was logged
		//read last line from log file
		//TODO: Get log file path from configuration
		$lastLog = self::readLastLine(dirname(__FILE__) . '/../../logs/erasmus.log');
		
		//echo 'last logged line: ' . $lastLog . "\n";
		self::assertTrue( strpos($lastLog, $errorMessage) );
	}
	
	public function test_handleError(){
		$errorMessage = 'Testing Error Handler: Handle Error';
		
		//build exception to handle
		$exception = new ErrorException( $errorMessage );
		
		//build handler
		$handler = new ErrorHandler();
		
		//test exception handling
		$handler->handleException($exception);
		
		//Test that exception was logged
		//read last line from log file
		//TODO: Get log file path from configuration
		$lastLog = self::readLastLine(dirname(__FILE__) . '/../../logs/erasmus.log');
		
		//echo 'last logged line: ' . $lastLog . "\n";
		self::assertTrue( strpos($lastLog, $errorMessage) );
	}
	
	public function test_handleFatalError(){
		$errorMessage = 'Testing Error Handler: Handle Fatal Error';
		
		//build exception to handle
		$exception = new ErrorException( $errorMessage );
		
		//build handler
		$handler = new ErrorHandler();
		
		//test exception handling
		$handler->handleException($exception);
		
		//Test that exception was logged
		//read last line from log file
		//TODO: Get log file path from configuration
		$lastLog = self::readLastLine(dirname(__FILE__) . '/../../logs/erasmus.log');
		
		//echo 'last logged line: ' . $lastLog . "\n";
		self::assertTrue( strpos($lastLog, $errorMessage) );
	}
}

?>