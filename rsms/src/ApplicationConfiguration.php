<?php
class ApplicationConfiguration {

    private static $CONFIG;

    public static function get( $key = NULL, $defaultValue = NULL ){
        if( $key != NULL ){
            $val = @self::$CONFIG[$key];
            if( $val === NULL ){
                return $defaultValue;
            }

            return $val;
        }

        return self::$CONFIG;
    }

    public static function configure(){
        $path = self::resolveConfigurationFile();
        self::$CONFIG = self::readConfiguration($path);
    }

    public static function resolveConfigurationFile(){
        // Resolve configuration file
        $configFile = dirname(__FILE__) . "/config/rsms-config.php";
        if( !file_exists($configFile) ){
            // Use default configuration file
            $configFile = dirname(__FILE__) . "/config/rsms-config.default.php";
        }

        return $configFile;
    }

    static function readConfiguration($url){

        if(!file_exists($url)){
            throw new Exception("File [$url] does not exist.");
        }

        $data = @file_get_contents($url);
        if($data === false){
            $error = self::getLastErrorMessage();
            throw new Exception("Error loading application config file [$url]: $error");
        }

        $config = @eval('?>' . $data);

		if ($config === false || $config == NULL) {
			$error = self::getLastErrorMessage();
			throw new Exception("Error parsing configuration: $error");
		}

		if (empty($config)) {
			throw new Exception("Invalid configuration: empty configuration array.");
		}

		if (!is_array($config)) {
			throw new Exception("Invalid configuration: not an array.");
        }

        if( count($config) === 0 ){
            throw new Exception("EMPTY CONFIG ARRAY");
        }

        return $config;
    }

    /**
     * Retrieves the message of the last error, if any
     * @return string
     */
    static function getLastErrorMessage(){
        $error = error_get_last();
        return $error == NULL ? '' : $error['message'];
    }
}
?>