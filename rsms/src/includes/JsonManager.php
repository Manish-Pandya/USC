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
	public static function encode($value, $entityMaps = NULL){
		// Time how long it takes to encode this
		$start = time();
		$jsonable = JsonManager::buildJsonableValue($value, $entityMaps);
		$t = time() - $start;

		// Warn for long-running encodings
		if( $t > 10 ){
			$l = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
			$l->warn("Building JSON-able value took $t seconds");
		}

		return json_encode($jsonable);
	}

	/**
	 * Constructs a 'JSON-able' value based on the parameter. The returned value is either of a PHP primitive type,
	 * or an array of such information that can be easily JSON-encoded.
	 */
	public static function buildJsonableValue(&$value, &$entityMaps = NULL){
		$jsonable = $value;

		//Differentiate Objects and Arrays
		if( is_object($value) ){
			////$this->LOG->trace( 'Building a JsonableValue for simple ' . $input );
			//Simply convert the object
			$jsonable = JsonManager::objectToBasicArray($value, $entityMaps);
		}
		else if( is_array($value) ){
			//Convert each element of the array
			$jsonable = array();

			foreach( $value as $element ){
				// json-ify
				$jsonable[] = JsonManager::buildJsonableValue($element, $entityMaps);
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

	private static function readInputStream( $stream ){
		if( empty( $stream ) ){
			Logger::getLogger(__CLASS__)->warn("No stream specified");
			return NULL;
		}

		//read JSON from input stream
		return file_get_contents( $stream );
	}

	public static function readRawJsonFromInputStream( $stream='php://input' ){
		$input = JsonManager::readInputStream($stream);

		if( !empty( $input ) ){
			// Return raw JSON object
			return json_decode($input, true);
		}
		else {
			//No data read from input stream.
			Logger::getLogger(__CLASS__)->warn( "Nothing to JSON-decode; no data read from input stream: $stream" );
			return NULL;
		}
	}

	/**
	 * Reads data from the given stream and decodes it as JSON.
	 *
	 * The stream defaults to php://input
	 *
	 * @throws Exception
	 * @return Ambigous <object, NULL>|NULL
	 */
	public static function decodeInputStream( $modelObject=null, $stream='php://input' ){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

		$input = JsonManager::readInputStream($stream);

		//Only attempt to convert if data is read from the stream
		if( !empty( $input) ){

			//decode JSON to object
			try{
				$decodedObject = JsonManager::decode($input, $modelObject);

				//$this->LOG->trace( 'Decoded to: ' . $decodedObject);

				return $decodedObject;
			}
			catch( Exception $e){
				throw new Exception('Unable to decode JSON from Input Stream', NULL, $e);
			}
		}
		else{
			//No data read from input stream.
			$LOG->debug( "Nothing to JSON-decode; no data read from input stream: $stream" );
			return NULL;
		}
	}

	public static function getFile( $stream='php://input' ){
		$LOG = Logger::getLogger('files test');
		$LOG->fatal(file_get_contents($stream));
		if( empty( $stream ) ){
			//$this->LOG->error("No stream specified; cannot decode JSON");
			return NULL;
		}else{
			return $stream;
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
	 * @return Object || Array populated by the array
	 */
	public static function assembleObjectFromDecodedArray($decodedJsonArray, $object = NULL){

        $LOG = Logger::getLogger(__FUNCTION__);


		//Make sure we have a base object

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
			else{
				$LOG->debug("Unexpected value encountered at decoded assmbly at key: $key");
			}
		}

        if(JsonManager::getIsArrayOfType($decodedJsonArray)){
            return $decodedJsonArray;
        }

		//Transform the decoded array into the object
		//	This can be done by the DtoManager using an empty prefix
        if( $object == NULL ){
            $object = JsonManager::buildModelObject($decodedJsonArray, $object);
		}

        if( $object == NULL ){
			$LOG->error("Error assembling object from decoded JSON - NULL base object");
            return NULL;
			// We have a problem!
		}

        //if object is null, pull ["Class"] from decode to infer type
		return DtoManager::autoSetFieldsFromArray($decodedJsonArray, $object, '');
	}

	/**
	 * If $object is omitted or NULL, infers a class from the array's 'Class' index,
	 * instantiates it, and returns it. If $object is given and not null,
	 * the object is returned unchanged.
	 *
	 * @param array $decodedJsonArray
	 * @param string|null $object
	 * @return object|string
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

    /**
     * UTILITY function for assembleObjectFromDecodedArray
     * determines if an array is composed of objects of a single class
     *
     * @param Array $array
     * @return boolean
     *
     */
    public static function getIsArrayOfType(array $array){
        if(!is_array($array) || !isset($array[0]) || !get_class($array[0])) return false;

        //store the class of the first index
        $type = get_class($array[0]);

        foreach($array as $value){
            //if the class has changed or has no value, we know we don't have an array of a single type
            if(!get_class($value) || get_class($value) != $type) return false;
        }

        return true;
    }

	public static function objectToBasicArray(&$object, &$entityMaps = NULL){
		// Check for marker interface indicating that this object should be json_encode'd raw
		if( $object instanceof IRawJsonable ){
			return $object;
		}

		//Call Accessors
		$objectVars = JsonManager::callObjectAccessors($object, $entityMaps);

		foreach( $objectVars as $key=>&$value ){
			$value = JsonManager::buildJsonableValue($value, $entityMaps);
		}

		return $objectVars;
	}

	static function prepareEntityMaps(&$object, &$overrideEntityMaps){
		$objEntityMaps = null;

		if( method_exists($object, 'getEntityMaps') ){
			$objEntityMaps = $object->getEntityMaps();
		}

		if( isset($overrideEntityMaps) ){
			// Override entity maps
			$LOG->debug("Overriding registered entity maps");
			EntityManager::with_entity_maps($classname, $overrideEntityMaps);
		}

		return EntityManager::merge_entity_maps($objEntityMaps, $overrideEntityMaps);
	}

	/**
	 * Returns an associative array containing the results of
	 * calling all 'getter' functions on the given object.
	 *
	 * @param mixed $object
	 * @return Array
	 */
	public static function callObjectAccessors(&$object, &$overrideEntityMaps = NULL){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

		$cached = SerialCache::getCachedEntity($object);
		if( isset($cached) ){
			$serlog = Logger::getLogger('SerialCache');
			if( $serlog->isTraceEnabled()){
				$serlog->trace("Return cached value for " . SerialCache::gen_entity_key($object));
			}

			return $cached;
		}

		$classname = get_class($object);

		// Prepare instance- and request-specific entity maps, if any
		$entityMaps = JsonManager::prepareEntityMaps($object, $overrideEntityMaps);

		// Get the list of accessor functions (filtered by entity maps)
		$accessors = EntityManager::get_registered_accessors( $object, $entityMaps );

		$LOG->trace("Calling accessors on $classname");

		//Retain the object's type in the json
		$objectVars = array('Class'=>$classname);

		foreach ($accessors as $getter) {
			//Call function to get value
			$value = $object->$getter();

			//use function name to infer the associated key
			$key = str_replace('get', '', $getter);
			if ($key == "Is_active") {
				$value = (boolean) $value;
			}

			//Associate key with value
			$objectVars[$key] = $value;
		}

		SerialCache::cacheSerializedEntity($objectVars);
		return $objectVars;
	}

	public static function extractEntityMapOverrides(&$dataSource){
		$entityMappingOverrides = array();
		foreach( array(EntityMap::$TYPE_EAGER, EntityMap::$TYPE_LAZY) as $type){
			if( array_key_exists($type, $dataSource) ){
				$overrides = explode(',', $dataSource[$type]);
				foreach($overrides as $accessor){
					$entityMappingOverrides[] = new EntityMap($type, $accessor);
				}
			}
		}

		if( empty($entityMappingOverrides) ){
			$entityMappingOverrides = null;
		}

		return $entityMappingOverrides;
	}
}
?>