<?php

// Set up RSMS application
require_once '/var/www/html/rsms/ApplicationBootstrapper.php';
ApplicationBootstrapper::bootstrap(null, array(
    ApplicationBootstrapper::CONFIG_SERVER_DB_NAME => 'rsms_1097'
));

// Register the task framework for autoloading?
Autoloader::register_class_dir( dirname(__FILE__) . '/domain');

require_once 'actions.php';
?>
