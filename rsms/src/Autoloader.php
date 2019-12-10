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
	
	/** Static reference to a logger */
	private static $LOG;

	private static $loader;
	
	/** Array of directory paths to check when Autoloading */
	private static $autoload_dirs;
	private static $autoloaded_roots;
	
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

		Autoloader::register_class_dir(dirname(__FILE__));
		$this->register();
		
		self::$LOG->trace("Autoloader initialized");
	}

	public static function register_class_dir( $class_dir ){
		Autoloader::scan_directories($class_dir);
	}
	
	/**
	 * Recursively scans the local directory for directory paths 
	 */
	static function scan_directories( $root = NULL ){
		$dir = $root;

		if( !isset($dir) ){
			throw new Exception("Cannot register empty path");
		}

		if( !isset( Autoloader::$autoloaded_roots ) ){
			Autoloader::$autoloaded_roots = array();
		}

		if( in_array($dir, Autoloader::$autoloaded_roots) ){
			self::$LOG->warn("$dir is already scanned for autoloading");
		}

		if( !isset( Autoloader::$autoload_dirs ) ){
			Autoloader::$autoload_dirs = array();
		}

		self::$LOG->trace("Scanning for directories; root=$dir");
		Autoloader::scanDirectoriesToArray($dir, Autoloader::$autoload_dirs);
		
		$dircount = sizeof(Autoloader::$autoload_dirs);
		self::$LOG->trace("Loaded $dircount directories");
	}

	/**
	 * Recursive function that scans the named directory and adds directory names
	 * to the given array
	 * 
	 * @param string $dir
	 * @param Array $list
	 */
	static function scanDirectoriesToArray( string $dir, Array &$list ){
		// Scan directory
		$results = scandir($dir);
		if( !empty($results) ){
			// Directory contains children; register it to our list
			array_push($list, $dir);

			// Scan child directories
			foreach ($results as $result){
				//ignore these
				if ($result === '.' or $result === '..') continue;
		
				$path = $dir . '/' . $result;
		
				if( is_dir($path) ){
					Autoloader::scanDirectoriesToArray($path, $list);
				}
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
			
			if( $class == 'BiosafetyProtocol' ){
				// This is a widespread misnomer in the application...
				$class = 'BioSafetyProtocol';
			}

			$LOG->debug( "Attempting to autoload $class" );
			$loaded = FALSE;
			
			foreach( Autoloader::$autoload_dirs as $directory ){
				$LOG->trace( "Checking $directory for $class" );
				$classfile = "$directory/$class.php";
		
				if( is_file( $classfile ) ){
					$LOG->debug("Autoloading class file: $classfile");
					
					include_once( $classfile );
					$loaded = TRUE;

					Autoloader::onLoadSuccess($directory, $class);

					break;
				}
			}
			
			if( !$loaded ){
				//This is fatal because class-not-found results in a fatal error
				$LOG->error("Unable to autoload class '$class' - no such file '$class.php' found.");
			}
		});
	}

	static function onLoadSuccess( &$directory, &$class ){
		// If this path falls within a 'classes' or 'domain' folder, consider it an Entity type
		if( stristr($directory, 'classes') || stristr($directory, 'domain') ){
			// Register entity type
			Logger::getLogger(__CLASS__)->debug("Loaded entity type $directory/$class");
			EntityManager::register_entity_class($class);
		}
	}
	
}

?>