<?php

/**
 * Utility class containing used for JSON construction / manipulation for DTOs
 * 
 * @author Mitch Martin
 */
class JsonManager {
	
	/** Names of functions JsonManager should ignore when converting to JSON */
	public static $JSON_IGNORE_FUNCTION_NAMES = array(
		'getTableName',
		'getColumnData'
	);
	
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
	
	/**
	 * Constructs a 'JSON-able' value based on the parameter. The returned value is either of a PHP primitive type,
	 * or an array of such information that can be easily JSON-encoded. 
	 */
	public static function buildJsonableValue($value){
		$jsonable = $value;
		
		//Differentiate Objects and Arrays
		if( is_object($value) ){
			//Simply convert the object
			$jsonable = JsonManager::objectToBasicArray($value);
		}
		else if( is_array($value) ){
			//Convert each element of the array
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
	public static function decodeInputStream( $stream='php://input' ){
		$LOG = Logger::getLogger( __CLASS__ );
		
		if( empty( $stream ) ){
			$LOG->error("No stream specified; cannot decode JSON");
			return NULL;
		}
		
		//read JSON from input stream
		$input = file_get_contents( $stream );
		
		$LOG->trace( 'Data read from input stream: ' . $input );
		
		//Only attempt to convert if data is read from the stream
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
			//No data read from input stream.
			$LOG->error( "Nothing to JSON-decode; no data read from input stream: $stream" );
			return NULL;
		}
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
	
	public static function objectToBasicArray($object){
		//Call Accessors
		$objectVars = JsonManager::callObjectAccessors($object);
		
		foreach( $objectVars as $key=>&$value ){
			$value = JsonManager::buildJsonableValue($value);
		}
		
		return $objectVars;
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
			//Make sure function starts with 'get' and not listed in JSON_IGNORE_FUNCTION_NAMES
			//TODO: Add class-specific names to ignore?
			if( strstr($func, 'get') && !in_array($func, JsonManager::$JSON_IGNORE_FUNCTION_NAMES) ){
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