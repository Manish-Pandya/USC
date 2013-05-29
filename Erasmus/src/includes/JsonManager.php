<?php

/**
 * Utility class containing used for JSON construction / manipulation for DTOs
 * 
 * @author Mitch Martin
 */
class JsonManager {
	
	public static $FUNCTION_TO_JSON = 'toJson';
	
	/**
	 * Encodes the given value to JSON. If the given value is an object, 
	 * it is processed by JsonManager::objectToJson; otherwise
	 * json_encode is called.
	 * 
	 * @param unknown $value
	 * @return string
	 */
	public static function encode($value){
		if( is_object($value) ){
			return JsonManager::objectToJson($value);
		}
		else{
			return json_encode($value);
		}
	}
	
	/**
	 * Alias of jsonToObject 
	 * @param unknown $json
	 * @param unknown $object
	 */
	public static function decode($json, $object){
		return JsonManager::jsonToObject($json, $object);
	}
	
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
		//Decode the json to associative array
		$decodedArray = json_decode($json, true);
		
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
		
		//Call Accessors
		$objectVars = JsonManager::callObjectAccessors($object);
		
		//Build and encode key/value pairs
		$jsonProperties = JsonManager::encodeJsonKeyValuePairs($objectVars);
		
		//TODO: check for errors?
		
		return '{' . join(',', $jsonProperties) . '}';
	}
	
	/**
	 * Transforms the given associative array into a JSON-encoded key-value pair.
	 * 
	 * @param Array $array
	 * @return string
	 */
	public static function encodeJsonKeyValuePairs($array){
		$jsonProperties = array();
		
		foreach( $array as $key=>$value){
			$encoded_value = JsonManager::encode($value);
			//TODO: check for error?
			$encoded_key = JsonManager::encode( strval($key) );
			$jsonProperties[] = $encoded_key . ':' . $encoded_value;
		}
		
		return $jsonProperties;
	}
	
	/**
	 * Returns an associative array containing the results of
	 * calling all 'getter' functions on the given object.
	 * 
	 * @param mixed $object
	 * @return Array
	 */
	public static function callObjectAccessors($object){
		$LOG = Logger::getLogger( __CLASS__ );
		
		$classname = get_class($object);
		$functions = get_class_methods( $classname);
		
		$LOG->trace("Calling accessors on $classname");
		
		//get all functions named get*
		$objectVars = array();
		foreach( $functions as $func ){
			//IGNORE getTableName and getColumnData
			//TODO: don't reference these functions by name!
			if( strstr($func, 'get') && $func != 'getTableName' && $func != 'getColumnData' ){
				$LOG->trace("Calling $classname#$func()");
				//Call function to get value
				$value = $object->$func();
		
				//use function name to infer the associated key
				$key = str_replace('get', '', $func);
		
				//Associate key with value
				$objectVars[$key] = $value;
			}
		}
		
		return $objectVars;
	}
}
?>