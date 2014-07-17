<?php

require_once 'Application.php';

/**
 * Autoloader class. Registers local directories to check for .php files
 *		within when a class is not found
 * 
 * Directory path(s) are scanned recursively relative to Autoloader.php
 * 
 * @author Mitch Martin, GraySail LLC
 */
class Autoloader {
	
	/** Static reference to a logger */
	private static $LOG;

	private static $loader;
	
	/** Array of directory paths to check when Autoloading */
	public static $autoload_dirs;
	
	/**
	 * Initializes the Autoloader singleton.
	 */
	public static function init(  ){
		if( self::$loader == NULL )
			self::$loader = new self();
		
		return self::$loader;
	}
	
	/**
	 * Constructs the Autoloader. This builds a (local) directory list
	 * to use for scanning, and then registers the Autoloader
	 */
	function __construct( ){
		//Define logger
		self::$LOG = Logger::getLogger(__CLASS__);
		self::$LOG->trace("Initializing Autoloader");
		
		Autoloader::scan_directories();
		$this->register();
		
		self::$LOG->trace("Autoloader initialized");
	}
	
	/**
	 * Recursively scans the local directory for directory paths 
	 */
	static function scan_directories(){
		self::$LOG->trace("Scanning for directories");
		$dir = dirname(__FILE__);
		
		Autoloader::$autoload_dirs = array();
		Autoloader::scanDirectoriesToArray($dir, Autoloader::$autoload_dirs);
		
		$dircount = sizeof(Autoloader::$autoload_dirs);
		self::$LOG->trace("Loaded $dircount directories");
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
			
			//Define new logger as we can't access self
			$LOG = Logger::getLogger(__CLASS__);
			
			$LOG->debug( "Attempting to autoload $class" );
			$loaded = FALSE;
			
			foreach( Autoloader::$autoload_dirs as $directory ){
				$LOG->trace( "Checking $directory for $class" );
				$classfile = "$directory/$class.php";
		
				if( file_exists( $classfile ) ){
					$LOG->debug("Autoloading class file: $classfile");
					
					include_once( $classfile );
					$loaded = TRUE;
					break;
				}
			}
			
			if( !$loaded ){
				//This is fatal because class-not-found results in a fatal error
				$LOG->fatal("Unable to autoload class '$class' - no such file '$class.php' found.");
			}
		});
	}
	
}

// auto-initialize autoloader when included (init() will not duplicate loaders)
Autoloader::init();

?>