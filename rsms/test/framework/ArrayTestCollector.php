<?php
/**
 * Collects test classes
 */
class ArrayTestCollector implements I_TestCollector {

    private $classnames = array();

    public function __construct( Array $tests ){
        foreach ($tests as $class => $methods) {
            // Check that class exists (Autoloading if necessary)
            if( class_exists($class, true) ){
                $this->classnames[] = $class;

                // TODO: Support specifying method names
            }
            else {
                throw new ErrorException("Test class '$class' does not exist. Did you forget a (-p) parameter?");
            }
        }
    }

    public function collect(){
        // Collect Specified I_Test implementations
        $test_instances = array();
        foreach($this->classnames as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface( I_Test::class )) {
                $test_instances[] = new $klass();
            }
        }

        return $test_instances;
    }
}
?>
