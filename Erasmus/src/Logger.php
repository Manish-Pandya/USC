<?php

/**
 * 
 * Logger class used for logging messages. A log is accessed by the static getLogger( $log_name ) function.
 * $log_name is optional; if none is supplied then the calling file's name will be obtained from a backtrace.
 * 
 * This version merely echos messages
 * TODO: Write to log file
 * 
 * @author Mitch Martin, GraySail LLC
 */
class Logger {
	
	const DEBUG	= 4;
	const INFO	= 3;
	const WARN	= 2;
	const ERROR	= 1;
	const NONE	= 0;
	
	/** Date/Time format to use while logging */
	public static $time_format = 'Y-m-d G:i:s';
	
	/** Array of configuration options */
	private static $config;
	
	/////
	
	private $log_level = Logger::DEBUG;
	private $log_name = 'newlog';
	
	private static $logger_debug = FALSE;
	private static function loggerDebug($msg){
		if( self::$logger_debug )
			echo "$msg\n";
	}
	
	public static function getLogger( $log_name ){
		self::loggerDebug("Getting logger for $log_name");
		if( $log_name == null ){
			self::loggerDebug("checking backtrace for name:");
			
			$backtrace = debug_backtrace();
			$backtrace_name = $backtrace[0]["file"];
			
			self::loggerDebug("$backtrace_name");
			
			$log_name = basename( $backtrace_name, '.php' );
		}
		
		self::loggerDebug("using name '$log_name'");
		
		$logger = new self($log_name);
		return $logger;
	}
	
	public static function getLogLevel( $levelName ){
		self::loggerDebug("Getting level for $levelName");
		switch( $levelName ){
			case "NONE": return Logger::NONE;
			case "ERROR": return Logger::ERROR;
			case "WARN": return Logger::WARN;
			case "INFO": return Logger::INFO;
			default:
			case "DEBUG": return Logger::DEBUG;
		}
	}
	
	private static function configure(){
		if( self::$config == NULL ){
			self::loggerDebug("configuring Logger");
			self::$config = parse_ini_file("/etc/logger.ini", false, INI_SCANNER_RAW);
			
			self::loggerDebug("Logger configured with:");
		}
	}
	
	private function __construct( $name ){
		//Make sure configuration has been read
		self::configure();
		
		$this->log_name = $name;
		
		self::loggerDebug("Log name: $name");
		
		self::loggerDebug("Checking level");
		if( array_key_exists($name, Logger::$config) ){
			
			self::loggerDebug("Level configuration found");
			$configuredLogLevel = Logger::$config[ $name ];
			$this->log_level = Logger::getLogLevel( $configuredLogLevel );
		}
		else{
			//Leave leve as default
			self::loggerDebug("No configuration found; using default level");
		}
		
		self::loggerDebug("Log level: $this->log_level");
		
	}
	
	private function log($msg, $level_name){
		$time = date(self::$time_format);
		
		echo "[$time] [$level_name] [$this->log_name] $msg\n";
	}
	
	public function debug( $msg ){
		if( $this->log_level >= Logger::DEBUG){
			$this->log( $msg, 'DEBUG');
		}
	}
	
	public function info( $msg ){
		if( $this->log_level >= Logger::INFO){
			$this->log( $msg, 'INFO');
		}
	}
	
	public function warn( $msg ){
		if( $this->log_level >= Logger::WARN){
			$this->log( $msg, 'WARN');
		}
	}
	
	public function error( $msg ){
		if( $this->log_level >= Logger::ERROR){
			$this->log( $msg, 'ERROR');
		}
	}
	
}

?>