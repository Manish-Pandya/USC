<?php
class RequestLog {

    private static $username;
    private static $desc;
    private static $logger;

    public static function init(&$actionName, &$dataSource){
        self::$logger = Logger::getLogger('request.ajax');

        // attempt to get module...
        $module = '';
        $action = ActionMappingManager::getAction($actionName);
        if( isset($action) ){
            $module = $action['module'];
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $username = $_SESSION['USER'] ? $_SESSION['USER']->getUsername() : '';
        if( isset($_SESSION['IMPERSONATOR']) ){
            $username = $_SESSION['IMPERSONATOR']['USER']->getUsername() . " (as $username)";
        }

        $params = "";
        foreach( $dataSource as $key=>$value){
            if( $key == 'action' || $key == 'callback')
                continue;

            // Implode array if necessary
            $pval = is_array($value) ? implode(", ", $value) : $value;

            $params .= "[$key : $pval] ";
        }

        self::$desc = "$requestMethod $module/$actionName $params";
        self::$username = $username;
    }

    public static function describe(){
        return self::$desc;
    }

    public static function log_start(){
        self::$logger->info(self::$username . " >>> " . self::$desc);
    }

    public static function log_stop( &$actionResult, $contentSize ){
        self::$logger->info(self::$username . " <<< " . self::$desc . " content-length: $contentSize");
    }
}
?>
