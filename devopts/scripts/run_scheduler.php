<?php

// Set up RSMS application
require_once '/var/www/rsms/Application.php';

// Execute scheduler
Scheduler::run();

?>