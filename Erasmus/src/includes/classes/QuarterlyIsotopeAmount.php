<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */
 class QuarterlyIsotopeAmount extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "quarterly_isotope_amount";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "quarterly_inventory_id"    => "integer",
    	"quantity"					=> "float", 
    	"isotope_id"				=> "int",   	
    		
        //GenericCrud
        "key_id"                    => "integer",
        "is_active"                 => "boolean",
        "date_created"              => "timestamp",
        "created_user_id"           => "integer",
        "date_last_modified"        => "timestamp",
        "last_modified_user_id"     => "integer"
    );

    
    //access information

	/** id of the QuarterlyInventory that is the parent of this amount **/
	private $quarterly_inventory_id;
	
	/** id of the user who signed off on this inventory **/
	private $quantity;
	
	private $isotope_id;
	
    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$this->setEntityMaps($entityMaps);
    }

    // Required for GenericCrud
    public function getTableName() {
        return self::$TABLE_NAME;
    }
    
    //Accessors/Mutators
    public function getColumnData() {
        return self::$COLUMN_NAMES_AND_TYPES;
    }
    
	public function getQuarterly_inventory_id() {
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->DEBUG($this);
		return $this->quarterly_inventory_id;
	}
	public function setQuarterly_inventory_id($quarterly_inventory_id) {
		$this->quarterly_inventory_id = $quarterly_inventory_id;
	}
	
	public function getQuantity() {
		return $this->quantity;
	}
	public function setQuantity($quantity) {
		$this->quantity = $quantity;
	}
	public function getIsotope_id() {
		return $this->isotope_id;
	}
	public function setIsotope_id($isotope_id) {
		$this->isotope_id = $isotope_id;
	}
	
	
}
?>