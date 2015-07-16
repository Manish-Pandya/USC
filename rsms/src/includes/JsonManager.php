<?php

/**
 * Utility class containing used for JSON construction / manipulation for DTOs
 * 
 * @author Mitch Martin
 */
class JsonManager {
	
	private $LOG;
	
	public function __construct(){

	}
	
	/** Names of functions JsonManager should ignore when converting to JSON */
	public static $JSON_IGNORE_FUNCTION_NAMES = array(
		'getTableName',
		'getColumnData',
		'getEntityMaps'	
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
			////$this->LOG->trace( 'Building a JsonableValue for simple ' . $input );
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

		
		if( empty( $stream ) ){
			//$this->LOG->error("No stream specified; cannot decode JSON");
			return NULL;
		}
		
		//read JSON from input stream
		$input = file_get_contents( $stream );
		
		//$this->LOG->trace( 'Data read from input stream: ' . $input );
		
		//Only attempt to convert if data is read from the stream
		if( !empty( $input) ){
			
			//decode JSON to object
			try{
				$decodedObject = JsonManager::decode($input);
				
				//$this->LOG->trace( 'Decoded to: ' . $decodedObject);
				
				return $decodedObject;
			}
			catch( Exception $e){
				throw new Exception('Unable to decode JSON from Input Stream', NULL, $e);
			}
		}
		else{
			//No data read from input stream.
			//$this->LOG->error( "Nothing to JSON-decode; no data read from input stream: $stream" );
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

		
		//Decode the json to associative array
		//$this->LOG->trace("Decoding JSON: $json");
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

		
		//if object is null, pull ["Class"] from decode to infer type
		$object = JsonManager::buildModelObject($decodedJsonArray, $object);
		
		//Make sure we have a base object
		if( $object == NULL ){
			// We have a problem!
			//$this->LOG->error("Error assembling object from decoded JSON - NULL base object");
			return NULL;
		}
		
		//FIXME: Remember listed fields:
		// *SEE DtoManager::rememberSetFieldName
		//	It will be important to know what fields were given in the JSON in many cases.
		//	Since instantiating an object will set all non-JSON'd fields to default (mostly NULL),
		//	it may be impossible to know if a value was omitted or should actually be NULL.
		
		// assemble embedded entities
		// For each value in the array...
		foreach( $decodedJsonArray as $key=>$value){
			// ...If value is an Array that contains the key "Class",
			if( is_array($value) && array_key_exists('Class', $value) ){
				// ...Transform it into an entity (through this function)
				$entity = JsonManager::assembleObjectFromDecodedArray($value);
				
				// ...and reset value to entity
				$decodedJsonArray[$key] = $entity;
			}
			//We may have an array containing arrays that should be instantiated as well
			else if( is_array($value) && is_array($value[0]) && array_key_exists('Class', $value[0] ) ){
				//TODO:  instantiate child objects
				$LOG = Logger::getLogger('yo');
				$LOG->fatal('found child object');
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

		
		if( $object == NULL && array_key_exists('Class', $decodedJsonArray) ){
			//Pull class name from decoded array
			$classname = $decodedJsonArray['Class'];
			
			//$this->LOG->trace("Inferring object type: '$classname'");
			
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

		
		$classname = get_class($object);

		$functions = get_class_methods( $classname);
		
		//$this->LOG->trace("Calling accessors on $classname");
		
		//Retain the object's type in the json
		$objectVars = array('Class'=>$classname);
		
		//get all functions named get*
		foreach( $functions as $func ){
			
			$skip = false;
			//Make sure function starts with 'get' and not listed in JSON_IGNORE_FUNCTION_NAMES
			//TODO: Add class-specific names to ignore?
			if( strstr($func, 'get') && !in_array($func, JsonManager::$JSON_IGNORE_FUNCTION_NAMES) ){
				
				// check for entity loading preferences for this object
				if (method_exists($object,"getEntityMaps")) {
					$entityMaps = $object->getEntityMaps();
					if(!empty($entityMaps)) {
						foreach($entityMaps as $em){
							if($em->getEntityAccessor() == $func && $em->getLoadingType() == "lazy")	{
								$skip = true;
							}
						}
					}
				}
				
				if(!$skip){
					//$this->LOG->trace("Calling $classname#$func()");
					//Call function to get value
					$value = $object->$func();
					////$this->LOG->trace("#func() returns [$value]");
				} else {
					//$this->LOG->trace("Skipping (lazy loading) $classname#$func()");
					//Call function to get value
					$value = null;
				}

				//use function name to infer the associated key
				$key = str_replace('get', '', $func);
				if ($key == "Is_active") {
					if ($value == 0) {
						$value = false;
					} elseif ($value == 1){
						$value = true;
					} else {
						$value = null;
					}
				}
	
				//Associate key with value
				$objectVars[$key] = $value;
				
			}
		}
		
		return $objectVars;
	}
}
?>