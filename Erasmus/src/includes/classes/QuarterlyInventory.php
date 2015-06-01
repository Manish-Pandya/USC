<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */
 class QuarterlyInventory extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "quarterly_inventory";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
    	"start_date"				=> "timestamp",
    	"end_date"					=> "timestamp",

        //GenericCrud
        "key_id"                    => "integer",
        "is_active"                 => "boolean",
        "date_created"              => "timestamp",
        "created_user_id"           => "integer",
        "date_last_modified"        => "timestamp",
        "last_modified_user_id"     => "integer"
    );

    //access information
    /** Relationships */
    protected static $PI_QUARTERLY_INVENTORIES_RELATIONSHIP = array(
    		"className" => "PIQuarterlyInventory",
    		"tableName" => "pi_quarterly_inventory",
    		"keyName"	=> "key_id",
    		"foreignKeyName"	=> "quarterly_inventory_id"
    );
    
	/** id of the PI who runs the lab(s) this inventory was done on **/
	private $pi_quarterly_inventories;
	
	/** Start of date range for this inventory **/
	private $start_date;
	
	/** End of date range for this inventory **/
	private $end_date;
	
    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("eager", "getQuarterly_isotope_amounts");
    	 
    	$this->setEntityMaps($entityMaps);
    }

    // Required for GenericCrud
    public function getTableName() {
        return self::$TABLE_NAME;
    }

    public function getColumnData() {
        return self::$COLUMN_NAMES_AND_TYPES;
    }
    
    //Accessors/Mutators
	public function getStart_date() {
		return $this->start_date;
	}
	public function setStart_date($start_date) {
		$this->start_date = $start_date;
	}
	
	public function getEnd_date() {
		return $this->end_date;
	}
	public function setEnd_date($end_date) {
		$this->end_date = $end_date;
	}
	    
	public function getPi_quarterly_inventories(){
		$LOG = Logger::getLogger(__CLASS__);
		if($this->pi_quarterly_inventories === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->pi_quarterly_inventories = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$PI_QUARTERLY_INVENTORIES_RELATIONSHIP));
		}
		return $this->pi_quarterly_inventories;
	}
	
	public function setPi_quarterly_inventories($inventories){$this->pi_quarterly_inventories = $inventories;}
	
}
?>