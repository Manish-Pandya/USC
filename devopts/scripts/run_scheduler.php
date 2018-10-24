<?php

// Set up RSMS application
require_once '/var/www/html/rsms/Application.php';

$moduleFilters = null;
if( $argc > 1 ){
    $moduleFilters = array_slice($argv, 1);
}

// Execute scheduler
Scheduler::run($moduleFilters);

?>