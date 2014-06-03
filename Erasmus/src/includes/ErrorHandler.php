<?php
// Define error/exception handling functions

class ErrorHandler {
	
	public static $handler;
	
	public static function init(){
		if( self::$handler === NULL ){
			// Create handler
			self::$handler = new ErrorHandler();
			
			// Register functions
			register_shutdown_function( array(&self::$handler, "handleFatalError") );
			set_error_handler( array(&self::$handler, "handleError") );
			set_exception_handler( array(&self::$handler, "handleException") );
			
			//This forces errors to not be displayed to the user
			//ini_set( "display_errors", "off" );
			
			//Make sure we report all errors
			error_reporting( E_ALL );
		}
	}
	
	function __construct(){
		Logger::getLogger( __CLASS__ )->info("created ErrorHandler class");
	}
	
	/**
	 * 
	 * @param unknown $exception
	 */
	function handleException(Exception $exception){
		Logger::getLogger(__CLASS__)->debug('Handling Exception');
		$message = "Exception '" . get_class($exception) . "' occurred at " . $exception->getFile() . ":" . $exception->getLine() . ". Message: " . $exception->getMessage();
		
		$log = Logger::getLogger( basename($exception->getFile(), ".php") );
		$log->error($message);
	}
	
	/**
	 * 
	 * @param unknown $num
	 * @param unknown $str
	 * @param unknown $file
	 * @param unknown $line
	 * @param string $context
	 */
	function handleError($num, $str, $file, $line, $context = null){
		Logger::getLogger(__CLASS__)->debug('Handling Error');
		
		// transform data into an ErrorException to pass to the exception handler
		$exception = new ErrorException($str, 0, $num, $file, $line);
		
		// Pass to handleException to log/etc
		$this->handleException($exception);
	}
	
	/**
	 * Checks if the last error was a FATAL error. If so, acts as handleError.
	 * 
	 * This is called as a shutdown function because FATAL errors do not trigger
	 * handleError.
	 */
	function handleFatalError(){
		$error = error_get_last();
		if ( $error["type"] == E_ERROR ){
			Logger::getLogger(__CLASS__)->info('Handling Fatal Error');
			$this->handleError( $error["type"], $error["message"], $error["file"], $error["line"] );
		}
	}
}
?>