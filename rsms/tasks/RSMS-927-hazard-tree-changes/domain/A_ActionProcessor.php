<?php

class ActionProcessorResult {
    public $success;
    public $message;
    public $repeatable;

    public function __construct($success, $message = null, $repeatable = false){
        $this->success = $success;
        $this->message = $message;
        $this->repeatable = $repeatable;
    }
}

abstract class A_ActionProcessor {
    protected $meta;
    protected $appActionManager;
    protected $STATS = array();

    public function __construct( ActionManager &$actionManager, &$meta ){
        $this->appActionManager = $actionManager;
        $this->meta = $meta;
    }

    abstract function validate( Action &$action ): ActionProcessorResult;
    abstract function perform( Action &$action ): ActionProcessorResult;
    abstract function verify( Action &$action ): bool;

    protected function stat( $stat_name, $increment = 1 ){
        if( !isset($this->STATS[$stat_name]) ){
            $this->STATS[$stat_name] = 0;
        }

        $this->STATS[$stat_name] += $increment;
    }

    public function get_stats(){
        if( empty($this->STATS) ){
            // No stats
            return '';
        }

        $mapped = array_map( function($v, $k){
            return "[$k: $v]";
        }, $this->STATS, array_keys($this->STATS));

        return implode(' ', $mapped);
    }
}
?>
