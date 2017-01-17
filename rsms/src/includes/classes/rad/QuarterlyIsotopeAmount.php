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
        "quarterly_inventory_id" 	=> "integer",
    	"starting_amount"			=> "float",
    	"ending_amount"				=> "float",
        "total_ordered"				=> "float",
    	"transfer_in"				=> "float",
    	"transfer_out"				=> "float",
        "solid_waste"				=> "float",
    	"liquid_waste"				=> "float",
        "scint_vial_waste"			=> "float",
    	"other_waste"				=> "float",
    	"on_hand"				    => "float",

    	"authorization_id"			=> "int",

        //GenericCrud
        "key_id"                    => "integer",
        "is_active"                 => "boolean",
        "date_created"              => "timestamp",
        "created_user_id"           => "integer",
        "date_last_modified"        => "timestamp",
        "last_modified_user_id"     => "integer"
    );


    //access information

	/** id of the PIQuarterlyInventory that is the parent of this amount **/
	private $pi_quarterly_inventory_id;

	/** amount of the isotope in inventory at the beginning of the quarter */
	private $starting_amount;

	/** amount of the isotope in inventory at the end of the quarter */
	private $ending_amount;

	private $authorization_id;
	private $authorization;

	private $total_ordered;

	private $transfer_in;

	private $transfer_out;

	private $solid_waste;

	private $liquid_waste;

	private $scint_vial_waste;

	private $other_waste;

	private $on_hand;

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
		return $this->pi_quarterly_inventory_id;
	}
	public function setQuarterly_inventory_id($quarterly_inventory_id) {
		$this->pi_quarterly_inventory_id = $quarterly_inventory_id;
	}

	public function getAuthorization_id() {
		return $this->authorization_id;
	}
	public function setAuthorization_id($authorization_id) {
		$this->authorization_id = $authorization_id;
	}

	public function getAuthorization(){
		$LOG = Logger::getLogger(__CLASS__);
		if($this->authorization == NULL  && $this->hasPrimaryKeyValue()){
			$authDao = new GenericDAO(new Authorization());
			$this->authorization = $authDao->getById($this->authorization_id);
		}
		$LOG->debug($this->authorization);

		return $this->authorization;
	}

	public function getStarting_amount() {
		return $this->starting_amount;
	}
	public function setStarting_amount($starting_amount) {
		$this->starting_amount = $starting_amount;
	}


	public function getTotal_ordered(){return $this->total_ordered;}
	public function setTotal_ordered($total_ordered){$this->total_ordered = $total_ordered;}

	public function getTransfer_in(){return $this->transfer_in;}
	public function setTransfer_in($transfer_in){$this->transfer_in = $transfer_in;}

	public function getTransfer_out(){return $this->transfer_out;}
	public function setTransfer_out($transfer_out){$this->transfer_out = $transfer_out;}

	public function getEnding_amount() {
		return $this->ending_amount;
	}
	public function setEnding_amount($ending_amount) {
		$this->ending_amount = $ending_amount;
	}


	public function getSolid_waste() {
		return $this->solid_waste;
	}
	public function setSolid_waste($solid_waste) {
		$this->solid_waste = $solid_waste;
	}

	public function getLiquid_waste() {
		return $this->liquid_waste;
	}
	public function setLiquid_waste($liquid_waste) {
		$this->liquid_waste = $liquid_waste;
	}

	public function getScint_vial_waste() {
		return $this->scint_vial_waste;
	}
	public function setScint_vial_waste($scint_vial_waste) {
		$this->scint_vial_waste = $scint_vial_waste;
	}

	public function getOther_waste() {
		return $this->other_waste;
	}
	public function setOther_waste($other_waste) {
		$this->other_waste = $other_waste;
	}

	public function getOn_hand() {
		return $this->on_hand;
	}
	public function setOn_hand($on_hand) {
		$this->on_hand = $on_hand;
	}

}
?>