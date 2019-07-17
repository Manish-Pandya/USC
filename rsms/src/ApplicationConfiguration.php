<?php
class ApplicationConfiguration {

    private static $CONFIG;

    public static function get( $key = NULL, $defaultValue = NULL ){
        // If key provided, attempt to retrieve configured value
        if( $key != NULL ){
            $val = $defaultValue;
            if( array_key_exists($key, @self::$CONFIG) ){
                $val = @self::$CONFIG[$key];
            }

            if( $val === NULL ){
                return $defaultValue;
            }

            return $val;
        }

        // Return all configuration if no Key is provided
        return self::$CONFIG;
    }

    public static function configure( $overrideConfigPath = NULL, $mergeOverrides = NULL ){
        $path = self::resolveConfigurationFile( $overrideConfigPath );
        self::$CONFIG = self::readConfiguration($path);

        if( isset($mergeOverrides) && is_array($mergeOverrides) ){
            // Merge parameter into our configuration, giving precedence to overrides
            self::$CONFIG = array_merge(
                self::$CONFIG,
                $mergeOverrides
            );
        }
    }

    public static function resolveConfigurationFile( $overrideConfigPath ){
        // Prepare array of possible config-file paths
        $possiblePaths = array(
            $overrideConfigPath,
            dirname(__FILE__) . "/config/rsms-config.php",
            dirname(__FILE__) . "/config/rsms-config.default.php"
        );

        // Resolve configuration file by finding first existing possible path
        $configFile = null;
        foreach ($possiblePaths as $path) {
            if( file_exists($path) ){
                $configFile = $path;
                break;
            }
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