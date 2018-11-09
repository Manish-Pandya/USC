<?php

class InspectionEmailMessage_Processor implements MessageTypeProcessor {
    public static $MTYPE_NO_DEFICIENCIES = 'PostInspectionNoDeficiencies';
    public static $MTYPE_DEFICIENCIES_FOUND = 'PostInspectionDeficienciesFound';
    public static $MTYPE_DEFICIENCIES_CORRECTED = 'PostInspectionDeficienciesCorrected';

    public static function getMessageTypeName( LabInspectionStateDto $inspectionState ){
        $messageType = null;
        if( $inspectionState->getTotals() == 0){
            $messageType = self::$MTYPE_NO_DEFICIENCIES;
        }
        else if( $inspectionState->getTotals() > $inspectionState->getCorrecteds()){
            $messageType = self::$MTYPE_DEFICIENCIES_FOUND;
        }
        else {
            $messageType = self::$MTYPE_DEFICIENCIES_CORRECTED;
        }

        return $messageType;
    }

    public function process(Message $message){
        //TODO
    }
}

?>