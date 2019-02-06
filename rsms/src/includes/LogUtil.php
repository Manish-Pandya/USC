<?php
class LogUtil {
    public static function log_stack($logger, $msg = '', $levelname = 'error'){
        $level = LoggerLevel::toLevel($levelname);
        if( $logger->isEnabledFor($level)){
            $e = new Exception();
            $logger->$levelname("$msg:\n    " . str_replace("\n", "\n    ", $e->getTraceAsString()));
        }
    }

    public static function get_logger(...$parts){
        return Logger::getLogger( implode('.', $parts));
    }
}
?>