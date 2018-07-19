<?php
class ApplicationConfiguration {

    private static $CONFIG;

    public static function get(){
        return self::$CONFIG;
    }

    public static function configure($path){
        self::$CONFIG = self::readConfiguration($path);
    }

    static function readConfiguration($url){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Read application configuration");

        if(!file_exists($url)){
            throw new Exception("File [$url] does not exist.");
        }

        $data = @file_get_contents($url);
        if($data === false){
            $error = error_get_last();
            throw new Exception("Error loading application config file [$url]: " . $error['message']);
        }

        $config = @eval('?>' . $data);

		if ($config === false) {
			$error = error_get_last();
			throw new Exception("Error parsing configuration: " . $error['message']);
		}

		if (empty($config)) {
			throw new Exception("Invalid configuration: empty configuration array.");
		}

		if (!is_array($config)) {
			throw new Exception("Invalid configuration: not an array.");
		}

        if( $LOG->isDebugEnabled() ){
            $LOG->debug(ApplicationConfiguration::get());
        }

        return $config;
    }
}
?>