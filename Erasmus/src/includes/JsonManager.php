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
		$jsonable = JsonManager::buildJsonableValue($value);
		
		return json_encode($jsonable);
	}
	
	public static function buildJsonableValue($value){
		$jsonable = $value;
		
		if( is_object($value) ){
			//Does object have special encode function?
			if( JsonManager::objectHasEncodeFunction($value) ){
				//FIXME: This will escape the to-json function's return value
				$jsonable = JsonManager::objectToJson($value);
			}
			else{
				$jsonable = JsonManager::objectToBasicArray($value);
			}
		}
		else if( is_array($value) ){
			$jsonable = array();
			
			foreach( $value as $element ){
				// json-ify
				$jsonable[] = JsonManager::buildJsonableValue($element);
			}
		}
		
		return $jsonable;
	}
	
	/**
	 * Alias of jsonToObject 
	 * @param string $json
	 * @param mixed $object (optional)
	 */
	public static function decode($json, $object = NULL){
		return JsonManager::jsonToObject($json, $object);
	}
	
	/**
	 * Reads data from the given stream and decodes it as JSON.
	 * 
	 * The stream defaults to php://input
	 * 
	 * @throws Exception
	 * @return Ambigous <object, NULL>|NULL
	 */
	public static function decodeInputStream( $stream='php://input'){
		$LOG = Logger::getLogger( __CLASS__ );
		
		//read JSON from input stream
		$input = file_get_contents( $stream );
		
		$LOG->trace( 'Data read from input stream: ' . $input );
		
		//TODO: verify that $input is actual data that can be converted
		if( !empty( $input) ){
			
			//decode JSON to object
			try{
				$decodedObject = JsonManager::decode($input);
				
				$LOG->trace( 'Decoded to: ' . $decodedObject);
				
				return $decodedObject;
			}
			catch( Exception $e){
				throw new Exception('Unable to decode JSON from Input Stream', NULL, $e);
			}
		}
		else{
			//TODO: No data read from input stream. Throw exception?
			$LOG->warn( 'No data read from input stream.' );
			return NULL;
		}
	}
	
	public static function objectHasEncodeFunction($object){
		$callable = array( $object, JsonManager::$FUNCTION_TO_JSON );
		
		return is_callable( $callable );
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
	public static function jsonToObject($json, $object = NULL){
		$LOG = Logger::getLogger( __CLASS__ );
		
		//Decode the json to associative array
		$LOG->trace("Decoding JSON: $json");
		$decodedJsonArray = json_decode($json, true);
		
		return JsonManager::assembleObjectFromDecodedArray($decodedJsonArray, $object);
	}
	
	/**
	 * Given an associative array and an optional model object, populate the model object
	 * with the data contained in the array. If no model object is provided, the type
	 * is inferred from an array index called 'Class'. If no such index exists, returns NULL
	 * 
	 * @param Array $decodedJsonArray
	 * @param string $object
	 * @return Object populated by the array
	 */
	public static function assembleObjectFromDecodedArray($decodedJsonArray, $object = NULL){
		$LOG = Logger::getLogger( __CLASS__ );
		
		//if object is null, pull ["Class"] from decode to infer type
		$object = JsonManager::buildModelObject($decodedJsonArray, $object);
		
		//Make sure we have a base object
		if( $object == NULL ){
			// We have a problem!
			$LOG->error("Error assembling object from decoded JSON - NULL base object");
			return NULL;
		}
		
		// assembe embedded entities
		// For each value in the array...
		foreach( $decodedJsonArray as $key=>$value){
			// ...If value is an Array that contains the key "Class",
			if( is_array($value) && array_key_exists('Class', $value) ){
				// ...Transform it into an entity (through this function)
				$entity = JsonManager::assembleObjectFromDecodedArray($value);
				
				// ...and reset value to entity
				$decodedJsonArray[$key] = $entity;
			}
		}
		
		//Transform the decoded array into the object
		//	This can be done by the DtoManager using an empty prefix
		return DtoManager::autoSetFieldsFromArray($decodedJsonArray, $object, '');
	}
	
	/**
	 * If $object is omitted or NULL, infers a class from the array's 'Class' index,
	 * instantiates it, and returns it. If $object is given and not null,
	 * the object is returned unchanged.
	 * 
	 * @param unknown $decodedJsonArray
	 * @param string $object
	 * @return unknown|string
	 */
	public static function buildModelObject($decodedJsonArray, $object = NULL){
		$LOG = Logger::getLogger( __CLASS__ );
		
		if( $object == NULL && array_key_exists('Class', $decodedJsonArray) ){
			//Pull class name from decoded array
			$classname = $decodedJsonArray['Class'];
			
			$LOG->trace("Inferring object type: '$classname'");
			
			//Instantiate a new object
			$inferredObject = new $classname();
			
			//Return new object
			return $inferredObject;
		}
		
		//Spit object back out
		return $object;
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
		//FIXME: Do we really need an overridable encode function?
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
	
	public static function objectToBasicArray($object){
		//Call Accessors
		$objectVars = JsonManager::callObjectAccessors($object);
		
		foreach( $objectVars as $key=>&$value ){
			$value = JsonManager::buildJsonableValue($value);
		}
		
		return $objectVars;
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
		
		//Retain the object's type in the json
		$objectVars = array('Class'=>$classname);
		
		//get all functions named get*
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