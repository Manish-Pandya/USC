<?php

/**
 * Helper class which maps a Macro string to a resolver closure.
 */
class MacroResolver {

    public $class;
    public $key;
    public $desc;
    private $resolverFn;

    public function __construct($class, $key, $desc, $resolverFn){
        $this->class = $class;
        $this->key = $key;
        $this->desc = $desc;
        $this->resolverFn = $resolverFn;
    }

    public function resolve( $thing ){
        // Call the resolver function with whatever parameter was provided
        return call_user_func($this->resolverFn, $thing);
    }

    public function describe(){
        return $this->desc;
    }

    public function __toString(){
        return "[" . get_class($this) . ".$this->class '$this->key']";
    }
}

?>