<?php
/**
 * Collects test classes
 */
class ArrayTestCollector implements I_TestCollector {

    private $testClassesAndMethods = array();

    public function __construct( Array $tests ){
        foreach ($tests as $class => $methods) {
            // Check that class exists (Autoloading if necessary)
            if( class_exists($class, true) ){
                foreach($methods as $m){
                    if( $m !== '*' && !method_exists( $class, $m ) ){
                        // Method does not exist
                        throw new ErrorException("Specified method '$class::$m' does not exist.");
                    }
                }

                $this->testClassesAndMethods[$class] = $methods;
            }
            else {
                throw new ErrorException("Test class '$class' does not exist. Did you forget a (-p) parameter?");
            }
        }
    }

    public function collectTestsInstances(){
        // Collect Specified I_Test implementations
        $test_instances = array();
        foreach($this->testClassesAndMethods as $klass => $methods) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface( I_Test::class )) {
                $test_instances[] = new $klass();
            }
        }

        return $test_instances;
    }

    public function collectTestMethods( $testClass ){
        $methods = $this->testClassesAndMethods[ $testClass ] ?? ['*'];

        if( $methods[0] === '*' ){
            // Collect all Test methods
            return DeclaredClassTestCollector::getAllDeclaredTestMethods($testClass);
        }
        else {
            // Return named methods
            return $methods;
        }
    }
}
?>
