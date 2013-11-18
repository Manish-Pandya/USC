<?php
/**
 * Class to handle action calls.
 * 
 * @author Mitch
 */
class ActionDispatcher {
	
	private $dataSource;
	private $actionMappingFactory;
	private $defaultErrorPage = 'forbidden.php';
	//TODO: Revisit default error code
	private $defaultErrorCode = 500;
	
	private $LOG;
	
	private $destinationPage;
	private $action_result;
	
	/**
	 * Constructor
	 * 
	 * @param Array $dataSource
	 * @param string $defaultErrorPage: Optional field to specify the default
	 * error page. 
	 */
	public function __construct(Array $dataSource, $actionMappingFactory = NULL){
		$this->dataSource = $dataSource;
		$this->actionMappingFactory = $actionMappingFactory;
		
		$this->LOG = Logger::getLogger(__CLASS__);
	}
	
	public function setDefaultErrorPage($errorPage){
		$this->defaultErrorPage = $errorPage;
	}
	
	public function getDefaultErrorPage(){
		return $this->defaultErrorPage;
	}
	
	public function getActionMappings(){
		if( $this->actionMappingFactory == NULL ){
			return ActionMappingFactory::readActionConfig();
		}
		else{
			return $this->actionMappingFactory->getConfig();
		}
	}
	
	/**
	 * Dispatch to the named action.
	 * 
	 * @param unknown $actionName
	 * @return ActionResult: Descriptor of the result
	 * 
	 * @see ActionMappingFactory
	 * @see ActionResult
	 */
	public function dispatch( $actionName ){
		$result = new ActionResult();
		
		if( $actionName == NULL ){
			$this->LOG->error("Error in ActionDispatcher - no action name specified");
			$this->dispatchError($result);
		}
		else{
			$this->readActionConfigurationAndDispatch($actionName, $result);
		}
		
		return $result;
	}
	
	/**
	 * Reads the available action mappings and verifies that $actionName is mapped.
	 * 
	 * @param unknown $actionName
	 * @param unknown $result
	 */
	public function readActionConfigurationAndDispatch($actionName, & $result){
		//Read action configuration
		$actionConfig = $this->getActionMappings();
		
		//Determine if we can dispatch the action
		if( !array_key_exists($actionName, $actionConfig)){
			$this->LOG->error("Invalid action name '$actionName' - No such action exists");
		
			// Invalid action specified
			$this->dispatchError( $result );
		}
		else{
			$this->dispatchValidAction($actionName, $actionConfig, $result);
		}
	}
	
	/**
	 * Dispatches the named action from the mappings (actionConfig), storing the
	 * results in the ActionResult reference.
	 * 
	 * @param string $actionName
	 * @param Array $actionConfig
	 * @param ActionResult $result
	 */
	public function dispatchValidAction($actionName, &$actionConfig, &$result){
		// We have a valid action name, so retrieve the details from the config
		$actionMapping = $actionConfig[$actionName];
		$action_function = $actionMapping->actionFunctionName;
		
		$this->LOG->debug("Checking user roles for action $actionName");
		$allowActionExecution = $this->checkRoles($actionMapping);
		$allowStr = $allowActionExecution ? "TRUE" : "FALSE";
		$this->LOG->debug("Granting user access to $actionName: $allowStr" );
		
		if( $allowActionExecution ){
			$result->actionFunctionResult = $this->doAction($actionMapping);
				
			//NULL indicates something was wrong
			if( $result->actionFunctionResult == NULL || $result instanceof ActionError ){
				// Forward to the failure page
				$this->dispatchError($result, $actionMapping);
			}
			else{
				// Forward to the success page
				$this->dispatchSuccess($result, $actionMapping);
			}
		}
		else{
			//Access Denied!
			
			//Dispatch as error
			$this->dispatchError($result, $actionMapping);
			
			// Set value to error message
			$result->actionFunctionResult = new ActionError('Access denied');
			
			// Override HTTP status code to not-authorized
			$result->statusCode = 401;
		}
	}
	
	/**
	 * Sets the destination and response code values in the given result
	 * to their respecive error values
	 * 
	 * @param ActionResult $result
	 * @param ActionMapping $actionMapping
	 * 
	 * @see ActionMapping
	 */
	public function dispatchError( ActionResult &$result, ActionMapping $actionMapping = NULL){
		$error_page = $this->defaultErrorPage;
		$error_code = $this->defaultErrorCode;
		
		// Dispatch to error page
		if( $actionMapping != NULL ){
			$error_page = $actionMapping->error_page;
			$error_code = $actionMapping->error_code;
		}
		
		$result->destinationPage = $error_page;
		$result->statusCode = $error_code;
	}
	
	/**
	 * Sets the destination and response code values in the given result
	 * to their respecive success values
	 * 
	 * @param ActionResult $result
	 * @param ActionMapping $actionMapping
	 * @see ActionMapping
	 */
	public function dispatchSuccess( ActionResult &$result, ActionMapping $actionMapping ){
		$result->destinationPage = $actionMapping->success_page;
		$result->statusCode = $actionMapping->success_code;
	}
	
	/**
	 * Checks the roles contained in the dataSource against the
	 * roles specified in the action mapping
	 * 
	 * @param ActionMapping $actionMaping
	 * @return boolean
	 */
	public function checkRoles( ActionMapping $actionMapping ){
		//Get roles allowed from mapping
		$allowed_roles = $actionMapping->roles;
		
		//Get user's role from our data source
		$user_roles = array();
		if( array_key_exists("ROLE", $this->dataSource) ){
			$user_roles = $this->dataSource["ROLE"];
		}
		
		//Check that we need any roles at all
		$grantAccess = empty($allowed_roles);
		
		if( !$grantAccess ){
			//Check that user has allowed role(s)
			foreach( $allowed_roles as $role){
				$grantAccess = in_array($role, $user_roles);
				
				//Don't bother checking others if we find a match
				if( $grantAccess ){
					break;
				}
			}
		}
		
		return $grantAccess;
	}
	
	/**
	 * Calls the action function specified in the given action mapping.
	 * 
	 * @param ActionMapping $actionMapping
	 * 
	 * @return unknown: The return value of the called function,
	 * 	or NULL if the if the function does not exist
	 */
	public function doAction( ActionMapping $actionMapping ){
		$action_function = $actionMapping->actionFunctionName;
		
		if( function_exists( $action_function ) ){
			//call the specified action function
			$this->LOG->debug("Executing action function '$action_function'");
			
			// Action functions are expected to return the desired data
			$functionResult = $action_function();
			
			return $functionResult;
		}
		else{
			//TODO: Show critical error; function doesn't exist
			$msg = "Mapped function '$action_function' does not exist";
			$this->LOG->error( $msg );
			return new ActionError( $msg );
		}
		
	}
	
}
?>