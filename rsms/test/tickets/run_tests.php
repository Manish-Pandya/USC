<?php

// Include test framework
// TODO: Autoload this
require_once dirname(__FILE__) . '/../framework/I_Test.php';
require_once dirname(__FILE__) . '/../framework/TestRunner.php';

// Set up RSMS application
require_once '/var/www/html/rsms/ApplicationBootstrapper.php';
ApplicationBootstrapper::bootstrap(null, array(
    ApplicationBootstrapper::CONFIG_SERVER_CACHE_ENABLE => false
));

// Make sure we throw exceptions on assertion failures
ini_set('assert.exception', 1);

// Set up test runner
$runner = new TestRunner();

// Identify tests to run
$ticket_dirs = null;
if( $argc > 1 ){
    $ticket_dirs = array_slice($argv, 1);
}

// Find test classes in dirs
foreach( $ticket_dirs as $dir ){
    $results = scandir($dir);
    foreach ($results as $result){
        //ignore these
        if ($result === '.' or $result === '..') continue;
        require_once $dir . '/' . $result;
    }
}

// Collect all loaded I_Test implementations
$classes = get_declared_classes();
$test_classes = array();
foreach($classes as $klass) {
    $reflect = new ReflectionClass($klass);
    if($reflect->implementsInterface( I_Test::class )) {
        $test_classes[] = $klass;
    }
}

if( empty($test_classes) ){
    exit("No tests to run\n");
}

// Run tests
$runner->runAll( $test_classes );

// Echo results
function pass(      ){ return "\e[0;32mPASS\e[0m"; }
function fail( $str ){ return "\e[0;31mFAIL: $str\e[0m"; }

foreach( $runner->getResults() as $testname => $testresults ){
    echo "$testname:\n";
    foreach( $testresults as $test => $res ){
        echo "  $test: " . ($res === true ? pass() : fail($res)) . "\n";
    }
}
?>
