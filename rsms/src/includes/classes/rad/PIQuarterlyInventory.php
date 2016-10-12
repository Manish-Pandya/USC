<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */

 class PIQuarterlyInventory extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "pi_quarterly_inventory";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "user_id"      				=> "integer",
    	"principal_investigator_id"	=> "integer",
    	"sign_off_date"				=> "timestamp",
    	"quarterly_inventory_id"	=> "integer",

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
    protected static $ISOTOPE_AMOUNTS_RELATIONSHIP = array(
    		"className" => "QuarterlyIsotopeAmount",
    		"tableName" => "quarterly_isotope_amount",
    		"keyName"	=> "key_id",
    		"foreignKeyName"	=> "quarterly_inventory_id"
    );
	/** date the lab signed off on this inventory **/
	private $sign_off_date;

	/** id of the user who signed off on this inventory **/
	private $user_id;

	/** id of the PI who runs the lab(s) this inventory was done on **/
	private $principal_investigator_id;
	private $principal_investigator;

	/** id of the QuarterlyInventory parent **/
	private $quarterly_inventory_id;
	private $quarterly_inventory;

	/** Isotopes and quantities for each isotope the PI had on hand at the end of the last inventory **/
	private $quarterly_isotope_amounts;

	/** Start of date range for this inventory **/
	private $start_date;

	/** End of date range for this inventory **/
	private $end_date;

    /** Due date for this inventory **/
    private $due_date;

    private $status;

    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("eager", "getQuarterly_isotope_amounts");
    	$entityMaps[] = new EntityMap("lazy", "getQuarterly_inventory");

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
	public function getSign_off_date() {
		return $this->sign_off_date;
	}
	public function setSign_off_date($sign_off_date) {
		$this->sign_off_date = $sign_off_date;
	}

	public function getUser_id() {
		return $this->user_id;
	}
	public function setUser_id($user_id) {
		$this->user_id = $user_id;
	}

	public function getPrincipal_investigator_id() {
		return $this->principal_investigator_id;
	}
	public function setPrincipal_investigator_id($principal_investigator_id) {
		$this->principal_investigator_id = $principal_investigator_id;
	}
	public function getPrincipal_investigator(){
		if($this->principal_investigator == NULL && $this->getPrincipal_investigator_id() != null) {
			$piDao = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $piDao->getById($this->getPrincipal_investigator_id());
		}
		return $this->principal_investigator;
	}

	public function getQuarterly_isotope_amounts(){
		if($this->quarterly_isotope_amounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->quarterly_isotope_amounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$ISOTOPE_AMOUNTS_RELATIONSHIP));
		}

		return $this->quarterly_isotope_amounts;
	}
	public function setQuarterly_isotope_amounts($amounts){$this->quarterly_isotope_amounts = $amounts;}

	public function getQuarterly_inventory_id() {
		return $this->quarterly_inventory_id;
	}
	public function setQuarterly_inventory_id($quarterly_inventory_id) {
		$this->quarterly_inventory_id = $quarterly_inventory_id;
	}

	public function getQuarterly_inventory(){
		if($this->quarterly_inventory == NULL && $this->hasPrimaryKeyValue()){
			$inventoryDao = new GenericDAO(new QuarterlyInventory());
			$this->quarterly_inventory = $inventoryDao->getById($this->quarterly_inventory_id);
		}
		return $this->quarterly_inventory;
	}

    public function getDue_date(){
        if($this->getQuarterly_inventory() != NULL){
            $this->due_date = $this->getQuarterly_inventory()->getDue_date();
        }
        return $this->due_date;
    }

    public function getStatus(){
        if($this->getSign_off_date() != null){
            return "COMPLETE";
        }else{
            if(strtotime("now") - strtotime($this->getDue_date()) > 0){
                return "OVERDUE";
            }else{
                return "PENDING";
            }
        }
    }

}
?>