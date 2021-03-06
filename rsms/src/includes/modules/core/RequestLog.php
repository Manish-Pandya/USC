<?php
class RequestLog {

    private static $username;
    private static $desc;
    private static $logger;

    public static function init(&$actionName, &$dataSource, $logger_name = 'request.ajax'){
        self::$logger = Logger::getLogger($logger_name);

        // attempt to get module...
        $module = '';
        $action = ActionMappingManager::getAction($actionName);
        if( isset($action) ){
            $module = $action['module'];
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $username = isset($_SESSION['USER']) ? $_SESSION['USER']->getUsername() : '';
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
        Metrics::start(self::$desc);
        // Extra padding to cover the status code
        self::$logger->info(self::$username . " >>>     " . self::$desc);
    }

    public static function log_stop( $status_code, $contentSize ){
        self::$logger->info(self::$username . " <<< " . $status_code . ' ' . self::$desc . " content-length: $contentSize");
        Metrics::stop(self::$desc);
    }
}
?>
