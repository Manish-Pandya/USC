<?php

/**
 * Autoloader class. Registers local directories to check for .php files
 *		within when a class is not found
 * 
 * Directory path(s) are scanned recursively relative to Autoloader.php
 * 
 * @author Mitch Martin, GraySail LLC
 */
class Autoloader {

	private static $loader;
	
	/** Array of directory paths to check when Autoloading */
	public static $autoload_dirs;
	
	/**
	 * Initializes the Autoloader singleton.
	 */
	public static function init(  ){
		if( self::$loader == NULL )
			$loader = new self();
		
		return self::$loader;
	}
	
	/**
	 * Constructs the Autoloader. This builds a (local) directory list
	 * to use for scanning, and then registers the Autoloader
	 */
	function __construct( ){
		Autoloader::scan_directories();
		$this->register();
	}
	
	/**
	 * Recursively scans the local directory for directory paths 
	 */
	static function scan_directories(){
		$dir = dirname(__FILE__);
		
		Autoloader::$autoload_dirs = array();
		Autoloader::scanDirectoriesToArray($dir, Autoloader::$autoload_dirs);
	}
	
	/**
	 * Recursive function that scans the named directory and adds directory names
	 * to the given array
	 * 
	 * @param unknown $dir
	 * @param Array $list
	 */
	static function scanDirectoriesToArray( $dir, Array &$list ){
		$results = scandir($dir);
	
		foreach ($results as $result){
			//ignore these
			if ($result === '.' or $result === '..') continue;
	
			$path = $dir . '/' . $result;
	
			if( is_dir($path) ){
				array_push($list, $path);
				Autoloader::scanDirectoriesToArray($path, $list);
			}
		}
	}
	
	/**
	 * Function to register custom autoloading
	 */
	private function register(){
		spl_autoload_register( function ($class) {
			
			//$LOG->debug( "Attempting to autoload $class" );
			
			foreach( Autoloader::$autoload_dirs as $directory ){
				//$LOG->debug( "Checking $directory" );
				$classfile = "$directory/$class.php";
				//$LOG->debug( "Checking file $classfile" );
		
				if( file_exists( $classfile ) ){
					//$LOG->debug( "File Exists: $classfile" );
					//$LOG->info("Autoloading $classfile");
					
					include_once( $classfile );
					break;
				}
				else{
					//$LOG->debug( "Does Not Exist: $classfile" );
				}
			}
		});
	}
	
}

// auto-initialize autoloader when included (init() will not duplicate loaders)
Autoloader::init();

?>