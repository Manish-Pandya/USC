<?php

/**
 * ActionMapping which also defines a security callback
 * to be executed prior to Action execution. Failure of
 * the security precondition will result in omission
 * of the Action function
 */
class SecuredActionMapping extends ActionMapping {
	public $preconditionFunction;

    public function __construct($action, $roles, $pre_fn = NULL){
        parent::__construct($action, "", "", $roles, count($roles) > 0);
        $this->preconditionFunction = $pre_fn;
    }
}
?>