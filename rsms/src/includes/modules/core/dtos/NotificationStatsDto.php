<?php
/**
 * DTO indended to hold an Array of NotificationStat objects,
 * and a 'statdefs' field indended for customized use
 */
class NotificationStatsDto implements IRawJsonable {
    public $stats;
    public $statdefs;

    public function __construct($stats, $statdefs = null){
        $this->stats = $stats;
        $this->statdefs = $statdefs;
    }
}
?>
