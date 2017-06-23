<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breede, GraySail LLC
 */
class ChemFumeHoodFeatureRelation extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "chem_fume_hood_feature_relation";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

		//GenericCrud
		"key_id"					=> "integer",
		"chem_fume_hood_feature_id" => "integer",
		"chem_fume_hood_id"			=> "integer",
		"name"						=> "text"
	);

	private $chem_fume_hood_feature_id;
	private $chem_fume_hood_id;
	private $name;

	public function __construct(){

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getChem_fume_hood_feature_id(){ return $this->chem_fume_hood_feature_id; }
	public function setChem_fume_hood_feature_id($chem_fume_hood_feature_id){ $this->chem_fume_hood_feature_id = $chem_fume_hood_feature_id; }

	public function getChem_fume_hood_id(){return $this->chem_fume_hood_id;}
	public function setChem_fume_hood_id($chem_fume_hood_id){$this->chem_fume_hood_id = $chem_fume_hood_id;}

	public function getName(){return $this->name;}
	public function setName($name){$this->name = $name;}

}
?>