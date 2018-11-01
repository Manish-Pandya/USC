<?php
class MessageTypeDto {
    private $module;
    private $typeName;
    private $typeDescription;
    private $macroDescriptions;

    public $processorName;
    public $contextTypes;

    public function __construct($module, $typeName, $typeDescription, $processorName, $contextTypes){
        $this->module = $module;
        $this->typeName = $typeName;
        $this->typeDescription = $typeDescription;

        $this->processorName = $processorName;
        $this->contextTypes = $contextTypes;
    }

    public function getModule(){ return $this->module; }
    public function setModule( $val ){ $this->module = $val; }

    public function getTypeName(){ return $this->typeName; }
    public function setTypeName( $val ){ $this->typeName = $val; }

    public function getTypeDescription(){ return $this->typeDescription; }
    public function setTypeDescription( $val ){ $this->typeDescription = $val; }

    public function getMacroDescriptions(){ return $this->macroDescriptions; }
    public function setMacroDescriptions( $val ){ $this->macroDescriptions = $val; }

    public function __toString(){
        return "[" . get_class($this) . " module='$this->module' type='$this->typeName' processor='$this->processorName']";
    }
}
?>