<?php

class GenericDto implements IRawJsonable {

    public function __construct( Array $fields ){
        $this->apply($fields);
    }

    public function apply( Array $fields ){
        foreach( $fields as $name=>$value) {
            $this->$name = $value;
        }
    }
}