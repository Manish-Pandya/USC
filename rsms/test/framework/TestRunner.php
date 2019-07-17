<?php
class TestRunner {
    public const TEST_PREFIX = 'test__';
    public const BEFORE_TEST_PREFIX = 'before__';
    public const AFTER_TEST_PREFIX = 'after__';

    private $results = array();
    private $collector;

    public function __construct( I_TestCollector $collector ){
        $this->collector = $collector;
    }

    public function getResults(){
        return $this->results;
    }

    public function runTests(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $test_instances = $this->collector->collect();

        foreach($test_instances as $instance ){
            $class = get_class($instance);

            if( $instance instanceof I_Test ){
                $LOG->info("Running $class");
                $this->run_test_class($instance);
            }
            else{
                $LOG->error("'$class' does not implement I_Test");
            }
        }

        return $this->getResults();
    }

    static function str_starts_with(string $needle, string $haystack){
        return substr($haystack, 0, strlen($needle)) == $needle;
    }

    /**
     * Runs all test functions in an instance of I_Test.
     *
     * @return Array of test results
     */
    public function run_test_class( I_Test $tests ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        $tests_name = get_class($tests);
        $results = array();

        $tests->setup();
        $methods = get_class_methods( $tests_name );

        // Collect method names for test execution
        $before_methods = array();
        $test_methods = array();
        $after_methods = array();

        foreach($methods as $method){
            if( self::str_starts_with(TestRunner::TEST_PREFIX, $method) ){
                $test_methods[] = $method;
            }
            else if( self::str_starts_with(TestRunner::BEFORE_TEST_PREFIX, $method) ){
                $before_methods[] = $method;
            }
            else if( self::str_starts_with(TestRunner::AFTER_TEST_PREFIX, $method) ){
                $after_methods[] = $method;
            }
        }

        foreach($test_methods as $method){
            Assert::logAssertions();

            // Run test(s)
            $overall_result = $this->run_test_method( $tests, $method, $before_methods, $after_methods );

            // Get logged assertions
            $assertions = Assert::getAssertions();

            $results[$method] = array(
                'pass' => $overall_result,
                'assertions' => $assertions
            );
        }

        $this->results[$tests_name] = $results;

        return $results;
    }

    /**
     * Runs a single test function
     *
     * @param I_Test $instance Test instance on which to run the method
     * @param string $method Name of the test method to run
     *
     * @return True if the test executed without error; string describing the error otherwise
     */
    function run_test_method( $instance, $method, $before_methods, $after_methods ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        try{
            $LOG->info("*** BEGIN TEST $method ***");
            DBConnection::get()->beginTransaction();

            // before-test
            $LOG->info("Test-method setup");
            foreach( $before_methods as $before ){
                $LOG->debug("$before");
                call_user_func( array($instance, $before) );
            }

            if( method_exists($instance, 'before_test') ){
                $LOG->info("Before-test");
                $instance->before_test();
            }

            $LOG->info("Execute test method");
            call_user_func( array($instance, $method) );

            // after-test
            $LOG->info("Test-method tear-down");
            foreach( $after_methods as $after ){
                $LOG->debug("$after");
                call_user_func( array($instance, $after) );
            }

            $LOG->info("Test $method passed");
        }
        catch(Throwable $t){
            $LOG->error("Test $method failed due to error: " . $t->getMessage());
            $LOG->error($t->getMessage() . ":\n    " . str_replace("\n", "\n    ", $t->getTraceAsString()));

            // Log additional assertion failure for non-assertion exception
            if( (!$t instanceof AssertionError) ){
                Assert::log_assert(false, $t->getMessage());
            }

            return get_class($t) . ': ' . $t->getMessage();
        }
        finally {
            $LOG->info("*** TEAR DOWN $method ***");
            DBConnection::get()->rollback();
        }

        return true;
    }
}
?>
