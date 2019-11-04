<?php

class TestSetup {
    public const OPTS = "hp:t:r:";

    private static $ASSERT_EXCEPTION = false;
    private static $INCLUDE_FILES = null;
    private static $INCLUDE_DIRS = null;
    private static $TESTS = null;
    private static $REPORTERS = null;

    private static $TEST_RUNNER = null;
    private static $REPORT_WRITER = null;

    public static function init(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->info("Initialize TestSetup");

        self::parse_options();

        self::$TEST_RUNNER = self::configure_test_runner();
        self::$REPORT_WRITER = self::configure_report_writer();
    }

    public static function getTestRunner(){
        return self::$TEST_RUNNER;
    }

    public static function getReportWriter(){
        return self::$REPORT_WRITER;
    }

    private static function configure_test_runner(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        //////////////////////////////////////////////////////////
        // Make sure we throw exceptions on assertion failures
        if( self::$ASSERT_EXCEPTION === TRUE ){
            $LOG->info("Enable assert exceptions");
            ini_set('assert.exception', 1);
        }
        else {
            $LOG->info("Disabling assert exceptions");
            $LOG->warn("TestRunner may not properly identify failed tests");
        }

        //////////////////////////////////////////////////////////
        // Include directories (recursively)
        if( !empty(self::$INCLUDE_DIRS) ){
            $LOG->info("Including " . count(self::$INCLUDE_DIRS) . " root directories");
            foreach(self::$INCLUDE_DIRS as $param){
                self::load_dir($param, true);
            }
        }

        //////////////////////////////////////////////////////////
        // Include specific file paths
        if( !empty(self::$INCLUDE_FILES) ){
            $LOG->info("Including " . count(self::$INCLUDE_FILES) . " files");
            foreach(self::$INCLUDE_FILES as $param){
                self::load_path($param);
            }
        }

        //////////////////////////////////////////////////////////
        // Determine Test Collector
        $testCollector = null;
        if( !empty( self::$TESTS ) ){
            // Collect specific test classes/methods
            $testCollector = new ArrayTestCollector( self::$TESTS );
        }
        else {
            // Collect all loaded Tests
            $testCollector = new DeclaredClassTestCollector();
        }

        $LOG->info("Using Collector: " . get_class($testCollector));

        //////////////////////////////////////////////////////////
        // Determine Runner
        $testRunner = new TestRunner( $testCollector );
        return $testRunner;
    }

    private static function configure_report_writer(){
        // TODO
        return new ConsoleTestReportWriter();
    }

    private static function parse_options(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $options = getopt( TestSetup::OPTS );

        if( isset($options['h']) ){
            echo "HELP\n";
            exit();
        }

        self::parse_assertion_exeptions( $options['x'] ?? TRUE );
        self::parse_paths( $options['p'] ?? null );
        self::parse_tests( $options['t'] ?? null );
        self::parse_reporters( $options['r'] ?? null );
    }

    static function parse_assertion_exeptions( $assert_ex_opt ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        self::$ASSERT_EXCEPTION = is_array($assert_ex_opt)
            ? $assert_ex_opt[0]
            : $assert_ex_opt;
    }

    static function parse_reporters( $reporters_opts ){
        // TODO
    }

    /**
     * Parses Test (-t) options
     *
     * Test options define test names (and optionally Methods)
     */
    static function parse_tests( $test_opts ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        if( !isset( $test_opts ) ){
            return;
        }
        else if ( !is_array($test_opts) ){
            $test_opts = array($test_opts);
        }

        $tests_to_run = array();

        foreach( $test_opts as $param ){
            $def = explode( '::', $param );

            if( !empty($def) ){
                $testclass = $def[0];
                $testfn = $def[1] ?? '*';

                $methods = $tests_to_run[$testclass] ?? array();

                if( in_array('*', $methods) ){
                    // All methods included; ignore any more
                    $LOG->debug("Ignoring '$testclass::$testfn' - all methods will be run");
                }
                else if( $testfn === '*' ){
                    $LOG->debug("Including '$testclass::*' - all methods will be run");
                    $methods = array('*');
                }
                else {
                    $LOG->debug("Including '$testclass::$testfn'");
                    $methods[] = $testfn;
                }

                $tests_to_run[$testclass] = $methods;
            }
        }

        self::$TESTS = $tests_to_run;
    }

    /**
     * Parses Path (-p) options
     *
     * Path options define Files or Directories which
     * should be loaded recursively
     */
    static function parse_paths( $path_opts ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        if( !isset( $path_opts ) ){
            return;
        }
        else if ( !is_array($path_opts) ){
            $path_opts = array($path_opts);
        }

        self::$INCLUDE_DIRS = array();
        self::$INCLUDE_FILES = array();

        // Identify paths
        $LOG->info("Load scripts in specified path(s)");
        foreach( $path_opts as $param ){
            // Include a file
            if( is_file($param) && file_exists($param) ){
                self::$INCLUDE_FILES[] = $param;
            }

            // Include contents of Directory
            else if( is_dir($param) && file_exists($param) ){
                self::$INCLUDE_DIRS[] = $param;
            }

            // Invalid -p parameter
            else {
                $LOG->warn("Invalid -p parameter '$param'");
            }
        }
    }

    static function load_dir( $dir, $recurse = false ){
        $results = scandir($dir);
        foreach ($results as $result){
            //ignore these
            if ($result === '.' or $result === '..') continue;
    
            $path = "$dir/$result";
    
            if( is_file($path) && file_exists($path) ){
                self::load_path($path);
            }
            else if( $recurse && is_dir($path) && file_exists($path) ){
                self::load_dir($path, $recurse);
            }
        }
    }

    static function load_path( $path ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->debug("Loading $path");
        require_once $path;
    }
}
?>
