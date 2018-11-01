<?php
class MacroDto {
    private $key;
    private $value;

    public function __construct($key, $val){
        $this->key = $key;
        $this->value = $val;
        Logger::getRootLogger()->info("Created MacroDto: $this->key = $this->value");
    }

    public function getKey(){ return $this->key; }
    public function setKey($val){ $this->key = $val; }
    public function getValue(){ return $this->value; }
    public function setValue($val){ $this->value = $val; }
}
?>