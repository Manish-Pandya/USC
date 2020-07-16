<?php
/**
 * A simple tuple describing a named statistic
 */
class NotificationStat implements IRawJsonable{
    public $name;
    public $count;

    public function __construct($name, $count){
        $this->name = $name;
        $this->count = $count;
    }
}
?>
