<?php

include_once('logger.php');

////////////////////////////////////////////////////////////////////////////////
//
//	Autoloader class. Registers the listed directories to check for .php files
//		within when a class is not found
//
//	Add relative directory path(s) to self::$autoload_dirs to register directory
//		for Autoload
//
////////////////////////////////////////////////////////////////////////////////
class Autoloader {

	private static $loader;
	
	public static $source_dir = "src";
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
	
	private function register(){
		spl_autoload_register( function ($class) {
			Logger::debug( "Attempting to autoload $class" );
				
			foreach( Autoloader::$autoload_dirs as $directory ){
				Logger::debug( "Checking $directory" );
				$classfile = "$directory$class.php";
				Logger::debug( "Checking file $classfile" );
		
				if( file_exists( $classfile ) ){
					Logger::debug( "File Exists: $classfile" );
					include_once( $classfile );
				}
				else{
					Logger::debug( "Does Not Exist: $classfile" );
				}
			}
		});
	}
	
}

?>