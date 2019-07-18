<?php

// Set up RSMS application
require_once '/var/www/html/rsms/ApplicationBootstrapper.php';
ApplicationBootstrapper::bootstrap(null, array(
    ApplicationBootstrapper::CONFIG_SERVER_CACHE_ENABLE => false,
    ApplicationBootstrapper::CONFIG_LOGGING_CONFIGFILE => dirname(__FILE__) . '/test-log4php.php',
));

// Register the test framework for autoloading
Autoloader::register_class_dir( dirname(__FILE__) . '/framework');

// Register test utilities
Autoloader::register_class_dir( dirname(__FILE__) . '/test-utils');

$LOG = Logger::getLogger('run_tests');
$LOG->info("+---------------------------------+");

// Make sure we throw exceptions on assertion failures
$LOG->debug("Enable assert exceptions");
ini_set('assert.exception', 1);

// Identify tests to run
$LOG->debug("Read script arguments");
$include_dirs = null;
if( $argc > 1 ){
    $include_dirs = array_slice($argv, 1);
    $LOG->trace( implode(',', $include_dirs));
}

// Load all files in specified dirs
function load_dir( $dir, $recurse = false ){
    $results = scandir($dir);
    foreach ($results as $result){
        //ignore these
        if ($result === '.' or $result === '..') continue;

        $path = "$dir/$result";

        if( is_file($path) && file_exists($path) ){
            load_path($path);
        }
        else if( $recurse && is_dir($path) && file_exists($path) ){
            load_dir($path, $recurse);
        }
    }
}

function load_path( $path ){
    global $LOG;
    $LOG->debug("Loading $path");
    require_once $path;
}

$LOG->debug("Load scripts in specified directories");
foreach( $include_dirs as $param ){
    $LOG->info("Check include param '$param'");

    // Include a file
    if( is_file($param) && file_exists($param) ){
        load_path($param);
    }

    // Include contents of Directory
    else if( is_dir($param) && file_exists($param) ){
        load_dir($param, true);
    }

    // Invalid parameter
    else {
        $LOG->warn("Invalid parameter '$param'");
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

function green($str){ return "\e[0;32m$str\e[0m"; }
function red($str){ return "\e[0;31m$str\e[0m"; }
function pass( $str = NULL ){ return green("[PASS]" . (isset($str) ? ": $str" : '')); }
function fail( $str = NULL ){ return red("[FAIL]" . (isset($str) ? ": $str" : '')); }

$passed = 0;
foreach( $results as $testname => $testresults ){
    echo str_pad('+', strlen($testname) + 2, '-') . "\n";
    echo "|$testname:\n";
    $pass_count = 0;
    foreach( $testresults as $test => $res ){
        $passOrError = $res['pass'];
        $assertions = $res['assertions'];

        if( $passOrError === true ){
            $pass_count++;
        }

        echo "+  $test: " . ($passOrError === true ? pass() : fail($passOrError)) . "\n";
        foreach( $assertions as $a ){

            echo "|    " . ($a[1] ? pass($a[0]) : fail($a[0])) . "\n";
        }
    }

    if( $pass_count == count($testresults) ){
        $passed++;
    }

    echo "\n";
}

$total_tests = count($results);
$percent = 0;
if( $total_tests > 0 ){
    $percent = $passed / $total_tests * 100;
}

$summary = "$passed / " . count($results) . " Passed ($percent%)";
$div = str_pad('', strlen($summary) + 2, '-');

// Decorate summary string
$summary = ($percent == 100 ? green($summary) : red($summary));

echo "\n";
echo "+$div+\n";
echo "|" . str_pad('Test Results', strlen($div), ' ', STR_PAD_BOTH) . "\n";
echo "| $summary\n";
echo "+$div+\n\n";

$LOG->debug("Completed test running");
?>
