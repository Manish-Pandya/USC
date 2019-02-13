<?php
class Field implements IField {
    const VALID_WRAPPERS = array('YEAR');

    public static function create($name, $table = null, $alias = null){
        return new Field($name, $table, $alias);
    }

    public $name;
    public $table;
    public $alias;

    private $wrappers = array();

    public function __construct($name, $table = null, $alias = null){
        $this->name = $name;
        $this->table = $table;
        $this->alias = $alias ?? $name;
    }

    public function wrap($wrapper){
        // Validate wrapper
        $wrapper = strtoupper($wrapper);
        if( in_array( $wrapper, Field::VALID_WRAPPERS )){
            $this->wrappers[] = $wrapper;
        }
        else{
            Logger::getLogger(__CLASS__)->error("Invalid field wrapper: '$wrapper'");
        }

        return $this;
    }

    public function write(){
        $f = "$this->table.$this->name";

        if( isset($this->wrappers) ){
            foreach($this->wrappers as $wrapper){
                $f = "$wrapper($f)";
            }
        }

        return $f;
    }
}
?>