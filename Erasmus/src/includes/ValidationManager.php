<?php
require_once dirname(__FILE__) . '/../Application.php';

/**
 * Manager class for generating FormValidator objects based on rules
 * defined by other objects (such as the DTOs being validated).
 * 
 * Base objects that require validation should provide validation rules
 * (as per formvalidator.php) through a method named getValidationRules.
 * This method should return the rules as an array of arrays:
 * 
 * array(
 *    array( field_name, validation_rule, error_message),
 *    array( "name", "req", "Name is required"),
 * );
 * 
 * @author Mitch
 * 
 * @see formvalidator.php
 * @see http://www.html-form-guide.com/php-form/php-form-validation.html
 */
class ValidationManager {
	
	/** Name of the method this manager looks for to find validation rules on an object */
	public static $FUNCTION_NAME_GET_VALIDATION_RULES = 'getValidationRules';
	
	private $LOG;
	
	public function __construct(){
		$this->LOG = Logger::getLogger(__CLASS__);
	}
	
	/**
	 * Builds, populates, and returns a FormValidator object based on the given arguments.
	 * 
	 * @param mixed *
	 * 	Any number of arguments are acceptable, and are processed as rule definitions.
	 *  A given argument is expected to be either an object, or a String=>Object array
	 * 
	 * @return FormValidator
	 */
	public function getValidator( /* varargs */ ){
		$this->LOG->trace('Building new FormValidator with ' . func_num_args() . ' base object(s).');
		$validator = new FormValidator();
		
		if( func_num_args() > 0 ) {
			//get/add rules
			$args = func_get_args();
			$this->processAndAddValidationRules($validator, $args);
		}
		
		return $validator;
	}
	
	/**
	 * Prcesses the given array for validation rules, and adds them to 
	 * the given validator reference (if any).
	 * 
	 * @param FormValidator $validator
	 * @param array $args
	 * 
	 * @see getValidator
	 */
	public function processAndAddValidationRules( &$validator, Array $args){
		$this->LOG->trace('Processing validation rules for ' . sizeof($args) . ' base object(s)' );
		
		foreach( $args as $arg ){
			//Get prefix and object for this argument
			$prefixName	= $this->getPrefixNameForValidatableObjectOrArray($arg);
			$object		= $this->getObjectForValidatableObjectOrArray($arg);
			
			//Get rules for this object
			$rules = $this->getValidationRules($object);
			
			//Add rules to validator
			$this->addValidationRulesToValidator($validator, $rules, $prefixName);
		}
	}
	
	/**
	 * Processes the given object for validation rules. Validation rules are
	 * obtained from a function called 'getValidationRules' on the object.
	 * 
	 * @param mixed $object
	 * @return multitype:
	 */
	public function getValidationRules($object){
		//Don't return null
		$rules = array();
		
		$functionName = ValidationManager::$FUNCTION_NAME_GET_VALIDATION_RULES;
		if( method_exists($object, $functionName)){
			$rules = $object->$functionName();
		}
		
		//TODO: Check for other validate functions?
		/*$functions = get_class_methods( get_class( $object ) );
		foreach ( $functions as $function ){
			if( $function != ValidationManager::$FUNCTION_NAME_GET_VALIDATION_RULES && strstr($function, 'ValidationRule') ){
				//function should return a validation rule
				$rule = $object->$function();
				$rules[] = $rule;
			}
		}*/
		
		return $rules;
	}
	
	/**
	 * Adds the rules defined in the array parameter to the validator reference.
	 * 
	 * @param FormValidator $validator
	 * 	Reference to a FormValidator object to which the given rules will be added.
	 * @param array $rules
	 * 	Array of validation rules that will be added to the validator
	 * @param string $prefix
	 * 	Optional parameter. If omitted, an empty prefix is used
	 */
	public function addValidationRulesToValidator( &$validator, Array $rules, $prefixName = ''){
		//Don't bother if rules are empty
		if( sizeof($rules) > 0 ){
			$this->LOG->debug("Adding " . sizeof($rules) . " rule(s) to validator with prefixName=$prefixName");
			
			//Get full prefix
			$prefix = DtoManager::getPrefix($prefixName);
			
			foreach( $rules as $rule ){
				//TODO: Validate $rule as an array?
				
				//Add each rule
				$validator->addValidation(
					// Combine prefix with the field name to get the validation rule name!
					$prefix . $rule[0],
					$rule[1],
					$rule[2]
				);
			}
		}
		else{
			$this->LOG->debug("No validation rules to add to validator with prefixName=$prefixName");
		}
	}
	
	/**
	 * Infers a non-array object from the argument. If $arg is an array, index=1 is
	 * expected to be an object
	 * 
	 * @param mixed $arg
	 * @return object
	 */
	public function getObjectForValidatableObjectOrArray( $arg ){
		if( is_array($arg) ){
			//$arg[1] should be object
			return $arg[1];
		}
		else{
			//$arg is object
			return $arg;
		}
	}
	
	/**
	 * Infers a prefix name from the argument. If $arg is an array, index=0 is
	 * expected to be a string to be used as the prefix name
	 * 
	 * @param mixed $arg
	 * @return string
	 */
	public function getPrefixNameForValidatableObjectOrArray( $arg ){
		if( is_array($arg) ){
			//$arg[0] should be $prefixName
			return $arg[0];
		}
		else{
			//$arg is object
			return DtoManager::getDefaultPrefixNameForObject($arg);
		}
	}
	
	/**
	 * Adds the errors from the given FormValidator to the given array. Errors
	 * are added as an array, and are set to the index "errors". If the "errors"
	 * index exists, old errors are retained.
	 * 
	 * @param FormValidator $validator
	 * @param array $array
	 */
	public function addValidationErrorsToArray(FormValidator $validator, Array &$array){
		//Expect errors to exist if this method is called!
		$error_hash = $validator->GetErrors();
		
		if( array_key_exists('errors', $array) ) {
			//Get existing error(s)
			$existingErrors = $array['errors'];
			
			//What if it is not an array?
			if( !is_array($existingErrors) ){
				//Make it one!
				$existingErrors = array($existingErrors);
			}

			// Add existing error(s) to hash
			foreach($existingErrors as $index => $error){
				$error_hash[$index] = $error;
			}
		}
		
		//Set errors to array
		$array['errors'] = $error_hash;
		
		//TODO: return anything?
	}
}

?>