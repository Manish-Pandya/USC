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
		Logger::getLogger( __CLASS__ )->debug("created ErrorHandler class");
	}
	
	/**
	 * 
	 * @param unknown $exception
	 */
	function handleException(Throwable $exception){
		//Logger::getLogger(__CLASS__)->debug('Handling Exception');
		$severity = ($exception instanceof ErrorException) ? $exception->getSeverity() : E_NOTICE;
		$message = "Exception ($severity) '" . get_class($exception) . "' occurred at " . $exception->getFile() . ":" . $exception->getLine() . ". Message: " . $exception->getMessage();
		
		$log = Logger::getLogger( basename($exception->getFile(), ".php") );
		if( $severity == E_NOTICE ){
			$log->warn("$message:\n    " . str_replace("\n", "\n    ", $exception->getTraceAsString()));
		}
		else{
			$log->error("$message:\n    " . str_replace("\n", "\n    ", $exception->getTraceAsString()));
		}
	}
	
	/**
	 * 
	 * @param int $num
	 * @param string $str
	 * @param string $file
	 * @param int $line
	 * @param string $context
	 */
	function handleError($num, $str, $file, $line, $context = null){
		//Logger::getLogger(__CLASS__)->debug('Handling Error');

		// Omit handling of error if it was suppressed with @
		//   see https://php.net/manual/en/language.operators.errorcontrol.php
		if (error_reporting() == 0) {
			return;
		}
		
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
			Logger::getLogger(__CLASS__)->info($error);
			$this->handleError( $error["type"], $error["message"], $error["file"], $error["line"] );
		}
	}
}
?>