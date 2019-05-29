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

    public function __construct( ActionManager &$actionManager, &$meta ){
        $this->appActionManager = $actionManager;
        $this->meta = $meta;
    }

    abstract function validate( Action &$action ): ActionProcessorResult;
    abstract function perform( Action &$action ): ActionProcessorResult;
    abstract function verify( Action &$action ): bool;
}
?>
