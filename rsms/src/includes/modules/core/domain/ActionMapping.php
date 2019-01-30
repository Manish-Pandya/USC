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
	public $checkRoles;
	
	public $success_code;
	public $error_code;
	
	//TODO: Revisit default code values
	public function __construct($action, $success_page, $error_page, Array $roles = array(), $checkRoles = true, $success_code = 200, $error_code = 500){
		$this->actionFunctionName = $action;
		$this->success_page = $success_page;
		$this->error_page = $error_page;
		$this->roles = $roles;
		$this->success_code = $success_code;
		$this->error_code = $error_code;
		$this->checkRoles = $checkRoles;
	}
}
?>