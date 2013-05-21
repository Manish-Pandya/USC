<?php

/**
 * Utility class containing static functions used to build Data Transfer Objects (DTOs).
 * 
 * @author Mitch Martin
 * @author GraySail, LLC
 */
class DtoBuilder {
	
	/**
	 * Automatically calls setter functions in the given object based on
	 * values in the request.
	 * 
	 * @param unknown $obj
	 * 	Object on which to call setters
	 * 
	 * @param string $prefixName
	 * 	Optional value used to identify valid key/values in the request. If
	 * 	omitted, the name of $obj's class will be used.
	 * 
	 * @see $_REQUEST
	 */
	public static function autoSetFieldsFromRequest($obj, $prefixName = null) {
		// Get the keys in the request
		$keys = array_keys($_REQUEST);
	
		var_dump($keys);
	
		//Make sure we have a prefix to look for.
		if($prefixName == null) {
			//Default to object's class name
			$prefixName = get_class($obj);
	
			// lower case
			$prefixName[0] = strtolower($prefixName[0]);
		}
	
		// Append underscore to the prefix
		// Request keys use underscore instead of period
		$prefixName .= "_";
	
		// Check each key for the prefix
		foreach($keys as $key) {
	
			//If key contains prefix
			if(strstr($key, $prefixName)) {
				// Split key by prefix so we can infer the prefixed field name
				$fName = explode($prefixName, $key);
					
				// Ensure that the field name begins with an upper-case letter
				$fName[1][0] = strtoupper($fName[1][0]);
				
				//TODO: Check for method existence / handle error
				//	This will result in a Fatal error if the generated
				//	method name does not exist!
				
				// Call the setter for the field with the request value
				$obj->{"set".$fName[1]}($_REQUEST[$key]);
			}
		}
	}
}

?>