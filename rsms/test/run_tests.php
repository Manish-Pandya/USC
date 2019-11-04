<?php

// Set up RSMS application
require_once '/var/www/html/rsms/ApplicationBootstrapper.php';
ApplicationBootstrapper::bootstrap(null, array(
    ApplicationBootstrapper::CONFIG_SERVER_CACHE_ENABLE => false,
    ApplicationBootstrapper::CONFIG_LOGGING_CONFIGFILE => dirname(__FILE__) . '/test-log4php.php',
));

// Register the test framework for autoloading
Autoloader::register_class_dir( dirname(__FILE__) . '/framework');

$LOG = Logger::getLogger('run_tests');

require_once dirname(__FILE__) . '/framework/TestSetup.php';

// Get CLI options
TestSetup::init();

// Configure test runner
$runner = TestSetup::getTestRunner();
$reportWriter = TestSetup::getReportWriter();

// Collect and Run tests
$LOG->debug("Running tests");
$results = $runner->runTests( $reportWriter );

// Analyze results and write Report(s)
$LOG->debug("Print test results");
$reportWriter->writePhase('Generating Report');
$reportWriter->writeReport($results);
$LOG->debug("Completed test running");
?>
