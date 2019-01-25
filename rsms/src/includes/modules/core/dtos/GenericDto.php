<?php

class GenericDto implements IRawJsonable {

    public static function create(Array $fields){
        $obj = new GenericDto();
        foreach( $fields as $name=>$value) {
            $obj->$name = $value;
        }

        return $obj;
    }

    public function __construct( Array $fields ){
        foreach( $fields as $name=>$value) {
            $this->$name = $value;
        }
    }
}