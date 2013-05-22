<?php

include_once dirname(__FILE__) . '/../Application.php';

/**
 * Utility class containing static functions used to build Data Transfer Objects (DTOs).
 * 
 * @author Mitch Martin
 * @author GraySail, LLC
 */
class DtoBuilder {
	
	/**
	 * Automatically calls setter functions in the given object based on
	 * values in the $_REQUEST array.
	 * 
	 * This function simply delegates to DtoBuilder::autoSetFieldsFromArray
	 * 
	 * @param unknown $baseObj
	 * 	Object on which to call setters
	 * 
	 * @param string $prefixName
	 * 	Optional value used to identify valid key/values in the request. If
	 * 	omitted, the name of $baseObj's class will be used.
	 * 
	 * @see DtoBuilder::autoSetFieldsFromArray
	 */
	public static function autoSetFieldsFromRequest($baseObject, $prefixName = null) {
		return self::autoSetFieldsFromArray($_REQUEST, $baseObject, $prefixName);
	}
	
	/**
	 * Automatically calls setter functions in the given object based on
	 * values in the given Array.
	 * 
	 * @param Array $array
	 * 	Array from which to obtain values for $baseObject
	 * 
	 * @param unknown $baseObj
	 * 	Object on which to call setters
	 * 
	 * @param string $prefixName
	 * 	Optional value used to identify valid key/values in the request. If
	 * 	omitted, the name of $baseObj's class will be used.
	 */
	public static function autoSetFieldsFromArray(Array $array, $baseObject, $prefixName = null){
		$LOG = Logger::getLogger(__CLASS__);
		
		// Get the keys in the request
		$keys = array_keys($array);
		
		//Make sure we have a prefix to look for.
		if($prefixName == null) {
			//Default to object's class name
			$prefixName = get_class($baseObject);
		
			// lower case
			$prefixName[0] = strtolower($prefixName[0]);
		}
		
		// Append underscore to the prefix
		// Request keys use underscore instead of period
		$prefixName .= "_";
		
		// Check each key for the prefix
		foreach($keys as $key) {
		
			//Note: Array values will be handled if the fields are keyed as arrays,
			//	like: name="obj.fieldname[]"
				
			//If key contains prefix
			if(strstr($key, $prefixName)) {
				// Split key by prefix so we can infer the prefixed field name
				$fName = explode($prefixName, $key);
					
				// Ensure that the field name begins with an upper-case letter
				$fName[1][0] = strtoupper($fName[1][0]);
		
				//Ensure the function exists before it is called
				$setterName = "set".$fName[1];
				$callable = array( $baseObject, $setterName );
				
				if( is_callable($callable) ){
					// Call the setter for the field with the request value
					$LOG->trace("Calling $setterName()");
					$baseObject->$setterName($array[$key]);
				}
				else{
					//Generated function cannot be called on the given object.
					
					//Warn that the function does not exist
					$LOG->warn("No Such Function: '$setterName' on class " . get_class($baseObject));
				}
			}
		}
		
		//Just regurgitate $baseObject
		return $baseObject;
	}
}

?>