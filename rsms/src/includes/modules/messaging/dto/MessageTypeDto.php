<?php
class MessageTypeDto {
    private $module;
    private $typeName;
    private $typeDescription;

    public function __construct($module, $typeName, $typeDescription){
        $this->module = $module;
        $this->typeName = $typeName;
        $this->typeDescription = $typeDescription;
    }

    public function getModule(){ return $this->module; }
    public function setModule( $val ){ $this->module = $val; }

    public function getTypeName(){ return $this->typeName; }
    public function setTypeName( $val ){ $this->typeName = $val; }

    public function getTypeDescription(){ return $this->typeDescription; }
    public function setTypeDescription( $val ){ $this->typeDescription = $val; }
}
?>