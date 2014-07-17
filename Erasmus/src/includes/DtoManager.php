<?php

include_once dirname(__FILE__) . '/../Application.php';

/**
 * Utility class containing static functions used to build Data Transfer Objects (DTOs).
 * 
 * @author Mitch Martin
 * @author GraySail, LLC
 */
class DtoManager {
	
	/**
	 * Automatically calls setter functions in the given object based on
	 * values in the $_REQUEST array.
	 * 
	 * This function simply delegates to DtoManager::autoSetFieldsFromArray
	 * 
	 * @param unknown $baseObj
	 * 	Object on which to call setters
	 * 
	 * @param string $prefixName
	 * 	Optional value used to identify valid key/values in the request. If
	 * 	omitted, the name of $baseObj's class will be used.
	 * 
	 * @see DtoManager::autoSetFieldsFromArray
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
		
		//Make sure we have a prefix to look for.
		if($prefixName === null) {
			$prefixName = DtoManager::getDefaultPrefixNameForObject($baseObject);
		}
		
		$LOG->trace("Collecting information from array for object of class " . get_class($baseObject) . " using prefix '$prefixName'");
		
		// Get the field names that are prefixed as keys from the array
		$fieldNamesAndValues = DtoManager::getPrefixedFieldNamesAndValuesFromArray($prefixName, $array);
		
		//Attempt to set each field on the base object
		foreach($fieldNamesAndValues as $fieldName => $fieldValue) {
			
			//TODO: Skip other known non-fields
			if( $fieldName == 'Class' ){
				//'Class' is special
				continue;
			}
		
			//Note: Array values will be handled if the fields are keyed as arrays,
			//	like: name="obj.fieldname[]"
			
			//Build the name of the setter function
			$setterName = DtoManager::buildSetterName($fieldName);
			
			// Ensure the function exists before it is called
			// Build tuple to check if the setter is callable on our base object
			$callable = array( $baseObject, $setterName );
			
			// Check callablility
			if( is_callable($callable) ){
				// Call the setter for the field with the request value
				$LOG->trace("Calling $setterName() on object of class " . get_class($baseObject));
				$baseObject->$setterName( $fieldValue );

				//TODO: Remember that this field was set?
				//DtoManager::rememberSetFieldName($baseObject, $fieldName);
			}
			else{
				//Generated function cannot be called on the given object.
				
				//Warn that the function does not exist
				$LOG->warn("Cannot set field '$fieldName' on name '$prefixName' - No such function: '$setterName' on class " . get_class($baseObject));
			}
		}
		
		//Just regurgitate $baseObject
		return $baseObject;
	}
	
	/**
	 * Returns a valid prefix based on the given name. A prefix is generated by
	 * appending an underscore, unless the prefixName is empty.
	 * 
	 * @param string $prefixName
	 * @return string
	 */
	public static function getPrefix( $prefixName ){
		if( strlen($prefixName) > 0 ){
			return $prefixName . '_';
		}
		else return $prefixName;
	}
	
	/**
	 * Processes the given array and returns all field names contained as keys.
	 * 
	 * @param string $prefixName
	 * @param Array $array
	 * @return array
	 */
	public static function getPrefixedFieldNamesAndValuesFromArray($prefixName, Array $array){
		$prefix = DtoManager::getPrefix($prefixName);
		
		//Get array keys
		$keys = array_keys($array);
		
		$fieldNamesAndValues = array();
		
		foreach( $keys as $key ){
			if( strlen($prefix) == 0 || strstr($key, $prefix)) {
				$fieldName = DtoManager::getFieldNameFromPrefixedKey($prefix, $key);
				$fieldNamesAndValues[$fieldName] = $array[$key];
			}
		}
		
		return $fieldNamesAndValues;
	}
	
	/**
	 * Returns the field name contained in the given key by removing the named prefix.
	 * 
	 * @param string $prefix
	 * @param string $key
	 * @return string
	 */
	public static function getFieldNameFromPrefixedKey( $prefix, $key ){
		if( strlen($prefix) == 0 ){
			return $key;
		}
		else{
			// Split they key by prefix
			$keySplit = explode($prefix, $key);
			
			//	The field name will be the second index; we can ignore the rest
			$fieldName = $keySplit[1];
			
			return $fieldName;
		}
	}
	
	/**
	 * Generates a setter name for the given field name.
	 * 
	 * Uses the standard setter convention to generate a value similar to:
	 * 	setFieldName
	 * 
	 * @param string $fieldName
	 * @return string
	 */
	public static function buildSetterName($fieldName){
		// Ensure that the field name begins with an upper-case letter
		$upperCaseFieldName = $fieldName;
		$upperCaseFieldName[0] = strtoupper($upperCaseFieldName[0]); 
		
		// Prepend field name with 'set'
		$setterName = "set" . $upperCaseFieldName;
		
		return $setterName;
	}
	
	/**
	 * Generates a prefix name to use based on the given object.
	 * 
	 * The prefix name will be a lower-case version of the object's class
	 * 
	 * @param unknown $baseObject
	 * @return string
	 */
	public static function getDefaultPrefixNameForObject( $baseObject ){
		//Default to object's class name
		$prefixName = get_class($baseObject);
		
		// lower case
		$prefixName = strtolower($prefixName);
		
		return $prefixName;
	}
	
	//FIXME: Would we really need this functionality?
	private static function rememberSetFieldName($object, $fieldName){
		//Get array of field names
		if( isset( $object->SET_FIELDS ) ){
			$setFields = $object->SET_FIELDS;
		}
		else{
			//Create if none exists
			$setFields = array();
		}
	
		//Add this field name to array
		$setFields[] = $fieldName;
	
		//Set array onto object
		$object->SET_FIELDS = $setFields;
	}
}

?>