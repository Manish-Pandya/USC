<?php
class Coalesce implements IField {
    public static function fields(Field...$fields){
        return new Coalesce($fields);
    }

    private $fields = array();
    private function __construct(Array $fields){
        $this->fields = $fields;
    }

    public function write(){
        $inner = implode(', ', array_map( function($f){ return $f->write(); }, $this->fields ));
        return "COALESCE( $inner )";
    }
}
?>
