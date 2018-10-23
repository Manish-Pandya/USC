<?php

// Set up RSMS application
require_once '/var/www/html/rsms/Application.php';

// Execute scheduler
Scheduler::run();

?>