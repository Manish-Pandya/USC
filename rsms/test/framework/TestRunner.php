<?php
class TestRunner {
    public const TEST_PREFIX = 'test__';
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
        foreach($methods as $method){
            if( substr($method, 0, strlen(TestRunner::TEST_PREFIX)) == TestRunner::TEST_PREFIX ){
                Assert::logAssertions();

                // Run test(s)
                $overall_result = $this->run_test_method( array($tests, $method) );

                // Get logged assertions
                $assertions = Assert::getAssertions();

                $results[$method] = array(
                    'pass' => $overall_result,
                    'assertions' => $assertions
                );
            }
        }

        $this->results[$tests_name] = $results;

        return $results;
    }

    /**
     * Runs a single test function
     *
     * @param Callable $callable Callable representing the test to run
     *
     * @return True if the test executed without error; string describing the error otherwise
     */
    function run_test_method( $callable ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        try{
            $test_name = is_array($callable) ? $callable[1] : '';
            $LOG->info("*** BEGIN TEST $test_name ***");
            DBConnection::get()->beginTransaction();

            call_user_func($callable);

            $LOG->info("Test $test_name passed");
        }
        catch(Throwable $t){
            $LOG->error("Test $test_name failed due to error: " . $t->getMessage());
            $LOG->error($t->getMessage() . ":\n    " . str_replace("\n", "\n    ", $t->getTraceAsString()));

            // Log additional assertion failure for non-assertion exception
            if( (!$t instanceof AssertionError) ){
                Assert::log_assert(false, $t->getMessage());
            }

            return get_class($t) . ': ' . $t->getMessage();
        }
        finally {
            $LOG->info("*** TEAR DOWN $test_name ***");
            DBConnection::get()->rollback();
        }

        return true;
    }
}
?>
