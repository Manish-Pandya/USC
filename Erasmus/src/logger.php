<?php

class Logger {
	
	private static $logger;
	
	public static $enable_debug = FALSE;
	
	private static function getLogger(){
		if ( self::$logger == NULL )
			self::$logger = new self();
		
		return self::$logger;
	}
	
	public static function debug($msg){
		//$back = debug_backtrace();
		//echo 'Called From:' . $back[0]["file"];
		
		if( self::$enable_debug )
			self::getLogger()->log($msg);
	}
	
	private function log($msg){
		echo $msg . "\n";
	}
	
}

?>