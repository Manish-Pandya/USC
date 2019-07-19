<?php
/**
 * Collects test classes
 */
class DeclaredClassTestCollector implements I_TestCollector {

    public function collectTestsInstances(){
        // Collect all loaded I_Test implementations
        $classes = get_declared_classes();
        $test_instances = array();
        foreach($classes as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface( I_Test::class )) {
                $test_instances[] = new $klass();
            }
        }

        return $test_instances;
    }

    public function collectTestMethods( $testClass ){
        // Collect all methods which are prefixed as a test
        $test_methods = array_filter(
            get_class_methods( $testClass ),
            function($name){
                return TestRunner::str_starts_with( TestRunner::TEST_PREFIX, $name );
            }
        );

        // Get just the values to prevent index holes
        return array_values($test_methods);
    }
}
?>
