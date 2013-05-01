<?php

include_once('logger.php');

/**
 * Autoloader class. Registers the listed directories to check for .php files
 *		within when a class is not found
 * 
 * Add relative directory path(s) to self::$autoload_dirs to register directory
 * 		for Autoload
 * @author Mitch Martin, GraySail LLC
 */
class Autoloader {

	private static $loader;
	
	/** Array of directory paths to check when Autoloading */
	public static $autoload_dirs = array(
		'',
		'com/graysail/usc/erasmus/domain/'
	);
	
	public static function init(  ){
		if( self::$loader == NULL )
			$loader = new self();
		
		return self::$loader;
	}
	
	function __construct( ){
		$this->register();
	}
	
	/**
	 * Function to register custom autoloading
	 */
	private function register(){
		spl_autoload_register( function ($class) {
			//Instantiate logger
			$LOG = Logger::getLoger();
			
			$LOG->debug( "Attempting to autoload $class" );
				
			foreach( Autoloader::$autoload_dirs as $directory ){
				$LOG->debug( "Checking $directory" );
				$classfile = "$directory$class.php";
				$LOG->debug( "Checking file $classfile" );
		
				if( file_exists( $classfile ) ){
					$LOG->debug( "File Exists: $classfile" );
					$LOG->info("Autoloading $classfile");
					include_once( $classfile );
					break;
				}
				else{
					$LOG->debug( "Does Not Exist: $classfile" );
				}
			}
		});
	}
	
}

?>