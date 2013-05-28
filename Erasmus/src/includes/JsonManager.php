<?php

/**
 * Utility class containing used for JSON construction / manipulation for DTOs
 * 
 * @author Mitch Martin
 */
class JsonManager {
	
	public static $FUNCTION_TO_JSON = 'toJson';
	
	/**
	 * Decodes the given JSON string and uses the key/value pairs to call
	 * mutator methods on $object.
	 * 
	 * @param string $json
	 * @param mixed $object
	 * 
	 * @see DtoManager
	 */
	public static function jsonToObject($json, $object){
		$jsonService = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		
		//Decode the json to associative array (indicated by SERVICES_JSON_LOOSE_TYPE)
		$decodedArray = $jsonService->decode($json);
		
		//Transform the decoded array into the object
		//	This can be done by the DtoManager using an empty prefix
		return DtoManager::autoSetFieldsFromArray($decodedArray, $object, '');
	}
	
	/**
	 * Encodes the given object to a JSON string.
	 * 
	 * If $object contains a #toJson function, it will be called and returned.
	 * Otherwise, values are obtained by processing accessor methods on $object
	 * (identified by get*)
	 * 
	 * @param mixed $object
	 * @return string
	 */
	public static function objectToJson($object){
		$LOG = Logger::getLogger( __CLASS__ );
		
		//If object has a toJson function, call it
		$callable = array( $object, JsonManager::$FUNCTION_TO_JSON );
		if( is_callable( $callable ) ){
			$LOG->trace("Encoding object to JSON by calling toJson() function");
			return $object->$callable[1]();
		}
		//Otherwise, infer the fields to generate JSON
		else{
			$LOG->trace("Encoding object to JSON by inferrence");
			return JsonManager::inferJsonProperties($object);
		}
	}
	
	/**
	 * Builds a JSON representation of the given object by
	 * processing it for accessor methods (identified by get*) used
	 * to generate the key/value pairs.
	 * 
	 * @param mixed $object
	 * @return string
	 */
	public static function inferJsonProperties($object){
		$LOG = Logger::getLogger( __CLASS__ );
		
		//Use Services_JSON to encode object
		$jsonService = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		
		//Call Accessors instead of reading public properties
		$classname = get_class($object);
		$functions = get_class_methods( $classname);
		
		$LOG->debug("Ecoding $classname object to JSON");
		
		//get all functions named get*
		$objectVars = array();
		foreach( $functions as $func ){
			//IGNORE getTableName and getColumnData
			//TODO: don't reference these functions by name!
			if( strstr($func, 'get') && $func != 'getTableName' && $func != 'getColumnData' ){
				//Call function to get value
				$value = $object->$func();
		
				//use function name to infer the associated key
				$key = str_replace('get', '', $func);
		
				//Associate key with value
				$objectVars[$key] = $value;
			}
		}
		
		// ALSO check public properties
		// (Taken from Services_JSON)
		$objectVars = array_merge( $objectVars, get_object_vars($object) );
		
		//Call name_value on Service_JSON to print the name / value pair
		// (Taken from Services_JSON)
		//This will also recursively evaluate/encode values in objectVars
		$properties = array_map(
			array($jsonService, 'name_value'),
			array_keys($objectVars),
			array_values($objectVars)
		);
		
		// Check for error(s)
		// (Taken from Services_JSON)
		foreach($properties as $property) {
			if($jsonService->isError($property)) {
				$LOG->error("Error encoding $classname object to JSON: $property");
				return $property;
			}
		}
		
		// (Taken from Services_JSON)
		return '{' . join(',', $properties) . '}';
	}
}
?>