<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */
 class CarboyUseAmount extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "carboy_use_amount";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "curie_level"          		=> "float",
        "carboy_id"          		=> "integer",
    	"isotope_id"				=> "integer",
    		
        //GenericCrud
        "key_id"                    => "integer",
        "is_active"                 => "boolean",
        "date_created"              => "timestamp",
        "created_user_id"           => "integer",
        "date_last_modified"        => "timestamp",
        "last_modified_user_id"     => "integer"
    );

    /** Relationships */
    protected static $PARCELUSE_RELATIONSHIP = array(
    		"className" => "ParcelUse",
    		"tableName" => "parcel_use",
    		"keyName"	=> "key_id",
    		"foreignKeyName" => "parcel_id"
    );
    
    
    //access information

    /** Float amount of radiation in curies */
    private $curie_level;

    /** Reference to the carboy containing this amount. Null if not liquid waste. */
    private $carboy;
    private $carboy_id;

    private $isotope_id;
    /** My own private isotope */
	private $isotope;
    
    /* The key_id of the isotope up in this ParcelUseAmount */
    private $isotope_id;

    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("lazy", "getCarboy");
    	$entityMaps[] = new EntityMap("eager", "getWaste_type");
    	$entityMaps[] = new EntityMap("eager", "getContainer_name");
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


    public function getCarboy() {
    	//NOTE: may not have a carboy(_id) because not all uses are liquid waste.
    	if($this->carboy == NULL && $this->getCarboy_id() != null) {
    		$carboyDao = new GenericDAO(new Carboy());
    		$this->carboy = $carboyDao->getById($this->getCarboy_id());
    	}
    	return $this->carboy;
    }
    public function setCarboy($newCarboy) {$this->carboy = $newCarboy;}

    public function getCarboy_id() { 
    	$LOG = Logger::getLogger(__CLASS__);
    	$LOG->debug('carboy id is '.$this->carboy_id);
    	 
    	return $this->carboy_id; 
    }
    public function setCarboy_id($newValue) { $this->carboy_id = $newValue; }

	public function getIsoptope_id(){return $this->isotope_id;}
	public function setIsotope_id($id){$this->isotope_id = $id;}
    
}
?>