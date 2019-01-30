<?php

/**
 *
 *
 *
 * @author David Hamiter
 */
class ChemFumeHood extends Equipment {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "chem_fume_hood";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "type"		            		=> "text",
        "serial_number"		        	=> "text",
        "make"          	   			=> "text",
        "model"     		    		=> "text",
        "comments"                      => "text",

		"id_number"						=> "text",
		"manufacturer"						=> "text",

		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);

	/** Relationships */
	protected static $USE_RELATIONSHIP = array(
		"className"	=>	"ChemFumeHoodUseRelation",
		"tableName"	=>	"chem_fume_hood_use_relation",
		"keyName"	=>	"key_id",
		"foreignKeyName" =>	"chem_fume_hood_id"
	);

	/** Relationships */
	protected static $FEATURE_RELATIONSHIP = array(
		"className"	=>	"ChemFumeHoodFeatureRelation",
		"tableName"	=>	"chem_fume_hood_feature_relation",
		"keyName"	=>	"key_id",
		"foreignKeyName" =>	"chem_fume_hood_id"
	);

    private $selectedInspection;
	private $id_number;
	private $manufacturer;
	private $uses;
	private $features;

	public function __construct(){
        parent::__construct();
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
        //return array_merge(parent::$COLUMN_NAMES_AND_TYPES, this:);
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public function getSelectedInspection(){
		return $this->selectedInspection;
	}

	public function setSelectedInspection($selectedInspection){
		$this->selectedInspection = $selectedInspection;
	}

	public function getId_number(){
		return $this->id_number;
	}
	public function setId_number($id_number){
		$this->id_number = $id_number;
	}

	public function getManufacturer(){
		return $this->manufacturer;
	}
	public function setManufacturer($manufacturer){
		$this->manufacturer = $manufacturer;
	}

	public function getUses(){
		if($this->uses === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->uses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$USE_RELATIONSHIP));
		}
		return $this->uses;
	}
	public function setUses($uses){ $this->uses = $uses; }

	public function getFeature(){
		if($this->features === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->features = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$FEATURE_RELATIONSHIP));
		}
		return $this->features;
	}
	public function setFeatures($features){ $this->features = $features; }

}
?>