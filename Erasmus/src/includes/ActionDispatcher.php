<?php
/**
 * Class to handle action calls.
 * 
 * @author Mitch
 */
class ActionDispatcher {
	
	private $dataSource;
	private $defaultErrorPage;
	
	/**
	 * Constructor
	 * 
	 * @param Array $dataSource
	 * @param string $defaultErrorPage: Optional field to specify the default
	 * error page. 
	 */
	public function __construct(Array $dataSource, $defaultErrorPage = 'forbidden.php'){
		$this->dataSource = $dataSource;
		$this->defaultErrorPage = $defaultErrorPage;
	}
	
	/**
	 * Dispatch to the named action.
	 * 
	 * @param unknown $actionName
	 * @return string: Name of the page to forward to
	 * 
	 * @see ActionMappingDefinitions
	 */
	public function dispatch( $actionName ){
		if( $actionName == null ){
			return $this->dispatchError();
		}
		
		//Read action configuration
		$actionConfig = ActionMappingDefinitions::readActionConfig();
		
		if( !array_key_exists($actionName, $actionConfig)){
			// Invalid action specified
			return $this->dispatchError();
		}
		
		// We have a valid action name, so retrieve the details from the config
		$actionMapping = $actionConfig[$actionName];
		$action_function = $actionMapping->actionFunctionName;
		
		$allowActionExecution = $this->checkRoles($actionMaping);
		
		if( $allowActionExecution ){
			$functionSuccess = $this->doAction($actionMapping);
			
			if( $functionSuccess ){
				// Forward to the success page
				return $this->dispatchSuccess($actionMapping);
			}
			else{
				// Forward to the failure page
				return $this->dispatchError($actionMapping);
			}
		}
	}
	
	/**
	 * Returns the name of the error page to forward to
	 * 
	 * @param ActionMapping $actionMapping
	 * @return string
	 * 
	 * @see ActionMapping
	 */
	public function dispatchError( ActionMapping $actionMapping = NULL){
		// Dispatch to error page
		if( $actionMapping == NULL ){
			return $this->defaultErrorPage;
		}
		else{
			return $actionMapping->error_page;
		}
	}
	
	/**
	 * Returns the name of the success page to forward to.
	 * @param ActionMapping $actionMapping
	 * @see ActionMapping
	 */
	public function dispatchSuccess( ActionMapping $actionMapping ){
		return $actionMapping->success_page;
	}
	
	/**
	 * Checks the roles contained in the dataSource against the
	 * roles specified in the action mapping
	 * 
	 * @param ActionMapping $actionMaping
	 * @return boolean
	 */
	public function checkRoles( ActionMapping $actionMaping ){
		$allowed_roles = $actionMapping->roles;
		
		return 
			empty($allowed_roles) ||
			in_array($this->dataSource["ROLE"], $allowed_roles);
	}
	
	/**
	 * Calls the action function specified in the given action mapping.
	 * 
	 * @param ActionMapping $actionMapping
	 * @return boolean: TRUE if the function call was successfull; FALSE if the
	 * function returns false OR if the function does not exist
	 */
	public function doAction( ActionMapping $actionMapping ){
		//TODO: Encapsulate the function in a testible way?
		
		$action_function = $actionMapping->actionFunctionName;
		
		if( function_exists( $action_function ) ){
			//call the specified action function
			$functionSuccess = $action_function();
			return $functionSuccess;
		}
		else{
			//TODO: Show critical error; function doesn't exist
			return FALSE;
		}
		
	}
}
?>