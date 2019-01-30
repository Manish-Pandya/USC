<?php
class MessageTypeDto {
    private static $_RECIPIENTS_DESCRIPTION_ACCESSOR_NAME = 'getRecipientsDescription';

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

    public function getRecipientsDescription(){
        if( class_exists( $this->processorName) &&  method_exists($this->processorName, self::$_RECIPIENTS_DESCRIPTION_ACCESSOR_NAME)){
            return call_user_func(
                array(new $this->processorName, self::$_RECIPIENTS_DESCRIPTION_ACCESSOR_NAME)
            );
        }

        else{
            Logger::getLogger(__CLASS__)->warn("Message processor $this->processorName does not describe its Recipients");
        }

        return "";
    }

    public function __toString(){
        return "[" . get_class($this) . " module='$this->module' type='$this->typeName' processor='$this->processorName']";
    }
}
?>