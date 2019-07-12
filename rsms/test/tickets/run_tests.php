<?php

// Include test framework
// TODO: Autoload this
require_once dirname(__FILE__) . '/../framework/I_Test.php';
require_once dirname(__FILE__) . '/../framework/Assert.php';
require_once dirname(__FILE__) . '/../framework/TestRunner.php';
require_once dirname(__FILE__) . '/../framework/I_TestCollector.php';
require_once dirname(__FILE__) . '/../framework/DeclaredClassTestCollector.php';

// Set up RSMS application
require_once '/var/www/html/rsms/ApplicationBootstrapper.php';
ApplicationBootstrapper::bootstrap(null, array(
    ApplicationBootstrapper::CONFIG_SERVER_CACHE_ENABLE => false,
    ApplicationBootstrapper::CONFIG_LOGGING_CONFIGFILE => dirname(__FILE__) . '/test-log4php.php',
));

$LOG = Logger::getLogger('run_tests');

// Make sure we throw exceptions on assertion failures
$LOG->debug("Enable assert exceptions");
ini_set('assert.exception', 1);

// Identify tests to run
$LOG->debug("Read script arguments");
$ticket_dirs = null;
if( $argc > 1 ){
    $ticket_dirs = array_slice($argv, 1);
    $LOG->trace( implode(',', $ticket_dirs));
}

// Load all files in specified dirs
$LOG->debug("Load scripts in specified directories");
foreach( $ticket_dirs as $dir ){
    $results = scandir($dir);
    foreach ($results as $result){
        //ignore these
        if ($result === '.' or $result === '..') continue;
        $path = "$dir/$result";
        $LOG->debug("Loading $path");
        require_once $path;
    }
}

// Set up test runner
$LOG->debug("Set up test runner");
$runner = new TestRunner(new DeclaredClassTestCollector());

// Collect and Run tests
$LOG->debug("Running tests");
$results = $runner->runTests();

// Echo results
$LOG->debug("Print test results");
function pass( $str = NULL ){ return "\e[0;32m[PASS]" . (isset($str) ? ": $str" : '') . "\e[0m"; }
function fail( $str = NULL ){ return "\e[0;31m[FAIL]" . (isset($str) ? ": $str" : '') . "\e[0m"; }

foreach( $results as $testname => $testresults ){
    echo "$testname:\n";
    foreach( $testresults as $test => $res ){
        $passOrError = $res['pass'];
        $assertions = $res['assertions'];

        echo "  $test: " . ($passOrError === true ? pass() : fail($passOrError)) . "\n";
        foreach( $assertions as $a ){

            echo "    " . ($a[1] ? pass($a[0]) : fail($a[0])) . "\n";
        }
    }
}

$LOG->debug("Completed test running");
?>
