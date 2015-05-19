<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */
 class CarboyReadingAmount extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "carboy_reading_amount";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "curie_level"          		=> "float",
        "carboy_use_cycle_id"       => "integer",
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

    /** Reference to the CarboyUseCycle containing this amount. */
    private $carboy_use_cycle;
    private $carboy_use_cycle_id;

    /* The key_id of the isotope up in this CarboyUsageAmount */
    private $isotope_id;
    /** My own private isotope */
	private $isotope;
    
	
    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("lazy", "getCarboy_use_cycle");
    	$entityMaps[] = new EntityMap("lazy", "getIsotope");
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

    public function getCarboy_use_cycle() {
    	//NOTE: may not have a carboy(_id) because not all uses are liquid waste.
    	if($this->carboy_use_cycle == NULL && $this->getCarboy_use_cycle_id() != null) {
    		$carboyDao = new GenericDAO(new CarboyUseCycle());
    		$this->carboy_use_cycle = $carboyDao->getById($this->getCarboy_use_cycle_id());
    	}
    	return $this->carboy_use_cycle;
    }
    public function setCarboy_use_cycle($newCarboy) {$this->carboy_use_cycle = $newCarboy;}

    public function getCarboy_use_cycle_id() { 
    	$LOG = Logger::getLogger(__CLASS__);
    	$LOG->debug('carboy id is '.$this->carboy_use_cycle_id);
    	 
    	return $this->carboy_use_cycle_id; 
    }
    public function setCarboy_use_cycle_id($newValue) { $this->carboy_use_cycle_id = $newValue; }

	public function getIsotope_id(){return $this->isotope_id;}
	public function setIsotope_id($id){$this->isotope_id = $id;}
    
}
?>