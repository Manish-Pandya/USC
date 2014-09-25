<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Perry Cate
 */
 class ParcelUseAmount extends GenericCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "parcel_use_amount";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "curie_level"          		=> "float",
        "waste_type_id"         	=> "integer",
        "carboy_id"          		=> "integer",
    	"parcel_use_id"				=> "integer",

        //GenericCrud
        "key_id"                    => "integer",
        "is_active"                 => "boolean",
        "date_created"              => "timestamp",
        "created_user_id"           => "integer",
        "date_last_modified"        => "timestamp",
        "last_modified_user_id"     => "integer"
    );

    //access information

    /** Float amount of radiation in curies */
    private $curie_level;
    
    /** Reference to the waste type this use amount consists of */
    private $waste_type;
    private $waste_type_id;

    /** Reference to the carboy containing this amount. Null if not liquid waste. */
    private $carboy;
    private $carboy_id;
    
    /** Id of is use amount's parent parcel use. */
    private $parcel_use_id;
    
    public function __construct() {
    	
    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("eager", "getCarboy");
    	$entityMaps[] = new EntityMap("eager", "getWaste_type");
    	$this->setEntityMaps($entityMaps);
    }

    // Required for GenericCrud
    public function getTableName() {
        return self::$TABLE_NAME;
    }

    public function getColumnData() {
        return self::$COLUMN_NAMES_AND_TYPES;
    }

    // Accessors / Mutators
    public function getCurie_level() { return $this->curie_level; }
    public function setCurie_level($newValue) { $this->curie_level = $newValue; }

    public function getWaste_type() {
    	if($this->waste_type == NULL) {
    		$wasteDao = new GenericDAO(new WasteType());
    		$this->waste_type = $wasteDao->getById($this->getWaste_type_id());
    	}
    	return $this->waste_type;
    }
    public function setWaste_type($newType) {
    	$this->waste_type = $newType;
    }
    
    public function getWaste_type_id() { return $this->waste_type_id; }
    public function setWaste_type_id($newValue) { $this->waste_type_id = $newValue; }

    public function getCarboy() {
    	//NOTE: may not have a carboy(_id) because not all uses are liquid waste.
    	if($this->carboy == NULL && $this->getCarboy_id() != null) {
    		$carboyDao = new GenericDAO(new Carboy());
    		$this->carboy = $carboyDao->getById($this->getCarboy_id());
    	}
    	return $this->carboy;
    }
    public function setCarboy($newCarboy) {
    	$this->carboy = $newCarboy;
    }
    
    public function getCarboy_id() { return $this->carboy_id; }
    public function setCarboy_id($newValue) { $this->carboy_id = $newValue; }

    public function getParcel_use_id() { return $this->parcel_use_id; }
    public function setParcel_use_id($newId) { $this->parcel_use_id = $newId; }

}
?>