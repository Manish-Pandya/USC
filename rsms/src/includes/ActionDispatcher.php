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
        $this->LOG = Logger::getLogger(__CLASS__);
        $this->dataSource = $dataSource;
        if($sessionSource != NULL){
            $this->sessionSource = $sessionSource;
        }
        else{
            $this->LOG->warn("No session source provided to ActionDispatcher");
            $this->sessionSource = array();
        }
        $this->actionMappingFactory = $actionMappingFactory;
    }

    public function setDefaultErrorPage($errorPage){
        $this->defaultErrorPage = $errorPage;
    }

    public function getDefaultErrorPage(){
        return $this->defaultErrorPage;
    }

    /**
     * Dispatch to the named action.
     *
     * @param  $actionName
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
     * @param string $actionName
     * @param ActionResult|ActionError $result
     */
    public function readActionConfigurationAndDispatch($actionName, & $result){
        $actionConfig = ActionMappingManager::getAction($actionName);

        //Determine if we can dispatch the action
        if( $actionConfig == null ){
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
        $actionMapping = $actionConfig['mapping'];

        //$this->LOG->debug("Checking user roles for action $actionName");

        if($actionMapping->checkRoles == true){
            $allowActionExecution = $this->checkRoles($actionMapping);
        }else{
            $allowActionExecution = true;
        }


        $allowStr = $allowActionExecution ? "TRUE" : "FALSE";
        $this->LOG->debug("Granting user access to $actionName: $allowStr" );

        if( $allowActionExecution ){
            $this->result->actionFunctionResult = $this->doAction($actionConfig);

            //NULL indicates something was wrong
            if( $this->result->actionFunctionResult === NULL  ){
                $this->LOG->warn("Null action result; forwarding to failure page");
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
            // Override HTTP status code to not-authorized
            $this->result->message = "User is not authorized for " . $actionMapping->actionFunctionName;
            $this->dispatchError($this->result, $actionMapping, 401);

            // Set value to error message
            $this->result->actionFunctionResult = new ActionError('Access denied');
        }
    }

    /**
     * Sets the destination and response code values in the given result
     * to their respecive error values
     *
     * @param ActionResult $result
     * @param ActionMapping|null $actionMapping
     * @param int $errorCode
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

        // If our result is an ActionError with a nonzero status, override the status code
        if( $result->actionFunctionResult instanceof ActionError && $result->actionFunctionResult->getStatusCode() > 0){
            $result->statusCode = $result->actionFunctionResult->getStatusCode();
        }

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
        //Get roles allowed from mapping
        $allowed_roles = $actionMapping->roles;
        $this->LOG->trace("Check user roles for $actionMapping->actionFunctionName");

        //Get user's role from our data source
        $user_roles = array();
        if( array_key_exists("ROLE", $this->sessionSource) ){
            $user_roles = $this->sessionSource["ROLE"]["userRoles"];
        }
        $this->LOG->trace('User has roles: ' . json_encode($user_roles));

        //Check that we need any roles at all
        $grantAccess = empty($allowed_roles);

        //Are any of the currently logged in user's roles in the allowed roles for the ActionManager method we called?
        if( !$grantAccess ) {
            $this->LOG->trace("Required role(s): " . json_encode($allowed_roles));
            $grantAccess = count( array_intersect($allowed_roles, $user_roles)) > 0;
        }

        return $grantAccess;
    }

    /**
     * Calls the action function specified in the given action mapping.
     *
     * @param array $actionConfig
     *
     * @return ActionResult|ActionError|null: The return value of the called function,
     *  	or NULL if the if the function does not exist
     */
    public function doAction( $actionConfig ){
        $actionModule = $actionConfig['module'];
        $actionMapping = $actionConfig['mapping'];

        $action_function = $actionMapping->actionFunctionName;
        $actions = $actionConfig['manager'];
        $actionManagerType = get_class($actions);

        $this->LOG->trace("doAction [$actionModule] $action_function on $actionManagerType");

        if( method_exists( $actions, $action_function ) ){
            // Attempt to extract parameters from request
            $reflected = new ReflectionMethod($actions, $action_function);
            $param_names = array();
            $func_args = array();
            foreach( $reflected->getParameters() as $arg ){
                $param_names[] = $arg->name;
                $func_args[ $arg->name ] = self::getValueFromRequest($arg);
            }

            ////
            // Before calling pre-function, check for security fn
            if( $actionMapping instanceof SecuredActionMapping && $actionMapping->preconditionFunction != null){
                $this->LOG->trace("Execute Security condition function $actionMapping->preconditionFunction");
                $preResult = call_user_func_array($actionMapping->preconditionFunction, $func_args);

                if( !$preResult ){
                    $this->LOG->error("Security condition '$actionMapping->preconditionFunction' failed");
                    return new ActionError("Unauthorized by Security condition failure", 403);
                }
            }

            ////
            // Before calling action, check for preconditions
            $pre_action = "pre_$action_function";
            if( method_exists( $actions, $pre_action ) ){
                $this->LOG->trace("Execute pre-action function $pre_action");
                $preResult = call_user_func_array( array($actions, $pre_action), $func_args);
                $preResult = $actions->$pre_action();

                if( !$preResult ){
                    $this->LOG->error("Pre-Action disallows execution");
                    return new ActionError("Pre-Action disallows execution");
                }
            }

            ////
            // Call the specified action function
            if( $this->LOG->isTraceEnabled() ){
                $this->LOG->trace("Executing action function '$actionManagerType::$action_function(" . implode(', ', $param_names) . ")'");
            }

            // Passing arguments by name
            $functionResult = call_user_func_array( array($actions, $action_function), $func_args);

            return $functionResult;
        }
        else{
            //TODO: Show critical error; function doesn't exist
            $msg = "Mapped function '$action_function' does not exist on $actionManagerType";
            $this->LOG->error( $msg );
            return new ActionError( $msg );
        }

    }

    /**
     * Utility function for retrieving a request parameter by name.
     * Special conversions (such as for 'null' and 'false' strings) are performed.
     *
     * If a reflection parameter is passed and its declared type matches
     * that of the parsed Request Body, the parsed body will be returned
     */
    public static function getValueFromRequest( &$paramOrName ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $valueName = is_string($paramOrName) ? $paramOrName : $paramOrName->name;

        if( array_key_exists($valueName, $_REQUEST) ){
            $val = $_REQUEST[ $valueName ];

            // convert 'null' and 'false' strings to NULL and FALSE constants
            if( is_string($val) ){
                if( stristr($val, "null") ){
                    $val = NULL;
                }
                else if( stristr($val, "false") ){
                    $val = false;
                }
            }

            $LOG->trace("$valueName=$val");
            return $val;
        }
        else if ( !is_string($paramOrName) ){
            // This is a Parameter reflection

            // Is there input which matches parameter type available?
            $body = self::getRequestBody();
            if( isset($body) && $paramOrName->hasType() && get_class($body) == $paramOrName->getType() ){
                $LOG->trace("$valueName=$body");
                return $body;
            }

            // Is there a default value available?
            if ( $paramOrName->isOptional() && $paramOrName->isDefaultValueAvailable() ){
                $LOG->trace("$valueName=DEFAULT");
                return $paramOrName->getDefaultValue();
            }

        }

        return null;
    }

    static $_REQUEST_BODY;
    static $_DECODED_REQUEST_BODY = false;
    static function getRequestBody(){
        if( self::$_DECODED_REQUEST_BODY == true ){
            return self::$_REQUEST_BODY;
        }

        // If request body is json data, then decode it
        if( isset($_SERVER["CONTENT_TYPE"]) && stristr($_SERVER["CONTENT_TYPE"], 'application/json') ){
            $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
            $LOG->trace("Attempt to parse input of type " . $_SERVER["CONTENT_TYPE"]);
            try {
                self::$_REQUEST_BODY = JsonManager::decodeInputStream();
            }
            catch( Exception $e ){
                // eat it
                $LOG->error($e);
            }
        }

        self::$_DECODED_REQUEST_BODY = true;
        return self::$_REQUEST_BODY;
    }
}
?>
