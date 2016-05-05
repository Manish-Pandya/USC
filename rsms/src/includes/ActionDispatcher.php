<?php
/**
 * Class to handle action calls.
 *
 * @author Mitch
 */
class ActionDispatcher {

    private $dataSource;
    private $sessionSource;
    private $actionMappingFactory;
    private $defaultErrorPage = 'forbidden.php';
    //TODO: Revisit default error code
    private $defaultErrorCode = 500;

    // name of actionManager class to use; depends on whether radiation is enabled.
    private $actionManagerType;

    private $LOG;

    private $destinationPage;
    private $result;

    /**
     * Constructor
     *
     * @param Array $dataSource
     * @param string $defaultErrorPage: Optional field to specify the default
     * error page.
     */
    public function __construct(Array $dataSource, Array $sessionSource = NULL, $actionMappingFactory = NULL){
        $this->dataSource = $dataSource;
        if($sessionSource != NULL)$this->sessionSource = $sessionSource;
        $this->actionMappingFactory = $actionMappingFactory;

        $this->LOG = Logger::getLogger(__CLASS__);

        // Which "ActionManager" class is used depends on whether the radiation
        // module is enabled.
        if( isRadiationEnabled() ) {
            $this->actionManagerType = "Rad_ActionManager";
        }else if( isVerificationEnabled() ){ 
            $this->actionManagerType = "Verification_ActionManager";
        }else if ( isHazardInventoryEnabled() ){
        	$this->actionManagerType = "HazardInventoryActionManager";
        }else if ( isEquipmentEnabled() ){
        	$this->actionManagerType = "Equipment_ActionManager";
        }
        else if ( isCommitteesEnabled() ){
        	$this->actionManagerType = "Committees_ActionManager";
        }
        else {
            $this->actionManagerType = "ActionManager";
        }
        $this->LOG->fatal( 'getting action manager type' );
        $this->LOG->fatal( $this->actionManagerType );
    }

    public function setDefaultErrorPage($errorPage){
        $this->defaultErrorPage = $errorPage;
    }

    public function getDefaultErrorPage(){
        return $this->defaultErrorPage;
    }

    public function getActionMappings(){
        if( $this->actionMappingFactory == NULL ){

            $actionMappings = ActionMappingFactory::readActionConfig();

            if( isRadiationEnabled() ) {
                $actionMappings = array_merge($actionMappings, Rad_ActionMappingFactory::readActionConfig());
            }
            
			//Verfication's server-side controller (VerificationActionManager extends HazardInventory's, so we "extend" the ActionMappings as well)
            if( isVerificationEnabled() ) {
            	$actionMappings = array_merge($actionMappings, Verification_ActionMappingFactory::readActionConfig());
				$actionMappings = array_merge($actionMappings, HazardInventoryActionMappingFactory::readActionConfig());
            }
            
            if( isHazardInventoryEnabled() ) {
            	$actionMappings = array_merge($actionMappings, HazardInventoryActionMappingFactory::readActionConfig());
            }        
            
            if( isEquipmentEnabled() ) {
            	$actionMappings = array_merge($actionMappings, Equipment_ActionMappingFactory::readActionConfig());
            }

            if( isCommitteesEnabled() ){
            	$actionMappings = array_merge($actionMappings, Committees_ActionMappingFactory::readActionConfig());
            }
            
            return $actionMappings;
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
        $this->result = new ActionResult();

        if( $actionName == NULL ){
            $this->LOG->error("Error in ActionDispatcher - no action name specified");
            $this->dispatchError($this->result);
        }
        else{
            $this->readActionConfigurationAndDispatch($actionName, $this->result);
        }

        return $this->result;
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

            // Send ActionError to client for debugging purposes
            $error = new ActionError("Invalid action name '$actionName' - No such action exists");
            $result->actionFunctionResult = $error;

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
     *
     */
    public function dispatchValidAction($actionName, &$actionConfig, &$result, $checkRoles = true){
        // We have a valid action name, so retrieve the details from the config
        $actionMapping = $actionConfig[$actionName];
        $action_function = $actionMapping->actionFunctionName;

        //$this->LOG->debug("Checking user roles for action $actionName");

        if($actionMapping->checkRoles == true){
            $allowActionExecution = $this->checkRoles($actionMapping);
        }else{
            $allowActionExecution = true;
        }


        $allowStr = $allowActionExecution ? "TRUE" : "FALSE";
        $this->LOG->debug("Granting user access to $actionName: $allowStr" );

        if( $allowActionExecution ){
            $this->result->actionFunctionResult = $this->doAction($actionMapping);

            //NULL indicates something was wrong
            if( $this->result->actionFunctionResult === NULL  ){
                // Forward to the failure page
                $this->dispatchError($this->result, $actionMapping);
            }
            //ActionError indicates a nonfatal error to be handled by the frontend
            else if($this->result->actionFunctionResult instanceof ActionError) {
                $this->LOG->warn("Dispatch Complete, but returning a " . get_class($this->result->actionFunctionResult)
                    . ' with message: ' . ($this->result->actionFunctionResult->getMessage()) );
                $this->dispatchSuccess($this->result, $actionMapping);
            }
            else{
                // Forward to the success page
                $this->dispatchSuccess($this->result, $actionMapping);
            }
        }
        else{
            //Access Denied!

            //Dispatch as error
            $this->dispatchError($this->result, $actionMapping);

            // Set value to error message
            $this->result->actionFunctionResult = new ActionError('Access denied');

            // Override HTTP status code to not-authorized
            $this->result->statusCode = 401;
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
    public function dispatchError( ActionResult &$result, ActionMapping $actionMapping = NULL, $errorCode = NULL){
        $error_page = $this->defaultErrorPage;
        $error_code = $this->defaultErrorCode;

        // Dispatch to error page
        if( $errorCode != null ){
            //Error code was specified
            $error_code = $errorCode;
        }
        else if( $actionMapping != NULL ){
            $error_page = $actionMapping->error_page;
            $error_code = $actionMapping->error_code;
        }

        $result->destinationPage = $error_page;
        $result->statusCode = $error_code;

        $this->LOG->debug("Dispatching error. Code=$result->statusCode | Page=$result->destinationPage");
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

        $this->LOG->debug("Dispatching success. Code=$result->statusCode | Page=$result->destinationPage");
    }

    /**
     * Checks the roles contained in the dataSource against the
     * roles specified in the action mapping
     *
     * @param ActionMapping $actionMaping
     * @return boolean
     */
    public function checkRoles( ActionMapping $actionMapping ){
        $LOG = Logger::getLogger(__CLASS__);
        //Get roles allowed from mapping
        $allowed_roles = $actionMapping->roles;
        //$LOG->debug($allowed_roles);
        //Get user's role from our data source
        $user_roles = array();
        if( array_key_exists("ROLE", $this->sessionSource) ){
            $user_roles = $this->sessionSource["ROLE"]["userRoles"];
        }
        //$LOG->debug($this->sessionSource["ROLE"]["userRoles"]);

        //Check that we need any roles at all
        $grantAccess = empty($allowed_roles);

        //Are any of the currently logged in user's roles in the allowed roles for the ActionManager method we called?
        if( !$grantAccess ) $grantAccess = count( array_intersect($allowed_roles, $user_roles)) > 0;

        return $grantAccess;
    }

    /**
     * Calls the action function specified in the given action mapping.
     *
     * @param ActionMapping $actionMapping
     *
     * @return unknown: The return value of the called function,
     *  	or NULL if the if the function does not exist
     */
    public function doAction( ActionMapping $actionMapping ){
        $action_function = $actionMapping->actionFunctionName;
        $actions = new $this->actionManagerType();

        if( method_exists( $actions, $action_function ) ){
            //call the specified action function
            //$this->LOG->debug("Executing action function '$action_function'");
            $functionResult = $actions->$action_function();

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
