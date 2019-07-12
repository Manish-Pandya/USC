<?php
/**
 * Collects test classes
 */
class DeclaredClassTestCollector implements I_TestCollector {

    public function collect(){
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
}
?>
