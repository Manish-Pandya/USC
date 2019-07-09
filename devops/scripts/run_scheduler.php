<?php

require_once '/var/www/html/rsms/ApplicationBootstrapper.php';

// Bootstrap RSMS application
ApplicationBootstrapper::bootstrap(null, array(
    // Override Log configuration
    "logging.configfile" => dirname(__FILE__) . "/scheduler-log4php-config.php"
));

$moduleFilters = null;
if( $argc > 1 ){
    $moduleFilters = array_slice($argv, 1);
}

// Execute scheduler
Scheduler::run($moduleFilters);

?>