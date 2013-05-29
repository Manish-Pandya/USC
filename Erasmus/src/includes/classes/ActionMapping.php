<?php

/**
 * Simple class to encapsulate action-mapping information:
 * - Action function name
 * - Success page
 * - Failure page
 * - Array of roles allowed to use mapping
 * 
 * @author Mitch
 *
 */
class ActionMapping {
	public $actionFunctionName;
	public $success_page;
	public $error_page;
	public $roles;
	
	public function __construct($action, $success_page, $error_page, $roles){
		$this->actionFunctionName = $action;
		$this->success_page = $success_page;
		$this->error_page = $error_page;
		$this->roles = $roles;
	}
}
?>