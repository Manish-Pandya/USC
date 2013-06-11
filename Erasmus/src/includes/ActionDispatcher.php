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
	
	private $LOG;
	
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
	 * @return string: Name of the page to forward to
	 * 
	 * @see ActionMappingFactory
	 */
	public function dispatch( $actionName ){
		if( $actionName == NULL ){
			$this->LOG->error("Error in ActionDispatcher - no action name specified");
			return $this->dispatchError();
		}
		
		//Read action configuration
		$actionConfig = $this->getActionMappings();
		
		if( !array_key_exists($actionName, $actionConfig)){
			$this->LOG->error("Invalid action name '$actionName' - No such action exists");
			
			// Invalid action specified
			return $this->dispatchError();
		}
		
		// We have a valid action name, so retrieve the details from the config
		$actionMapping = $actionConfig[$actionName];
		$action_function = $actionMapping->actionFunctionName;

		$this->LOG->debug("Checking user roles for action $actionName");
		$allowActionExecution = $this->checkRoles($actionMapping);
		$allowStr = $allowActionExecution ? "TRUE" : "FALSE";
		$this->LOG->debug("Granting user access to $actionName: $allowStr" );
		
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
		else{
			//Access Denied!
			return $this->dispatchError($actionMapping);
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
	 * @return boolean: TRUE if the function call was successfull; FALSE if the
	 * function returns false OR if the function does not exist
	 */
	public function doAction( ActionMapping $actionMapping ){
		$action_function = $actionMapping->actionFunctionName;
		
		if( function_exists( $action_function ) ){
			//call the specified action function
			$this->LOG->debug("Executing action function '$action_function'");
			$functionSuccess = $action_function();
			return $functionSuccess;
		}
		else{
			//TODO: Show critical error; function doesn't exist
			$this->LOG->error("Mapped function '$action_function' does not exist");
			return FALSE;
		}
		
	}
	
}
?>