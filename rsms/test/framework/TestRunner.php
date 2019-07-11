<?php
class TestRunner {
    public const TEST_PREFIX = 'test__';
    private $results = array();

    public function getResults(){
        return $this->results;
    }

    public function runAll(Array $test_class_names){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        foreach($test_class_names as $class ){
            $instance = new $class();
            if( $instance instanceof I_Test ){
                $LOG->info("Running $class");
                $this->run($instance);
            }
            else{
                $LOG->warn("'$class' does not implement I_Test");
            }
        }
    }

    public function run( I_Test $tests ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        $tests_name = get_class($tests);
        $results = array();

        $tests->setup();
        $methods = get_class_methods( $tests_name );
        foreach($methods as $method){
            if( substr($method, 0, strlen(TestRunner::TEST_PREFIX)) == TestRunner::TEST_PREFIX ){
                $results[$method] = $this->run_test( array($tests, $method) );
            }
        }

        $this->results[$tests_name] = $results;

        $LOG->info($results);

        return $results;
    }

    function run_test( $callable ){
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
            return $t->getMessage();
        }
        finally {
            $LOG->info("*** TEAR DOWN $test_name ***");
            DBConnection::get()->rollback();
        }

        return true;
    }
}
?>
