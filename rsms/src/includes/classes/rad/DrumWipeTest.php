<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class DrumWipeTest extends RadCrud
{
    /** Name of the DB Table */
	protected static $TABLE_NAME = "drum_wipe_test";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"drum_id"						=> "integer",
			"reading_type"					=> "text",
			"surface_reading"				=> "float",
            "background_reading"            => "float",

			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);

	/** Relationships */
	public static $DRUM_WIPE_RELATIONSHIP = array(
			"className" => "DrumWipe",
			"tableName" => "drum_wipe",
			"keyName"   => "key_id",
			"foreignKeyName" => "drum_wipe_test_id"
	);

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getDrum");
		$entityMaps[] = new EntityMap("lazy","getDrum_wipes");

		$this->setEntityMaps($entityMaps);
	}

	//access information
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	private $drum_id;
	private $drum;

    private $hasWipes;
	private $drum_wipes;

	/** Wipe test readings can be done with LSC, Alpha/Beta or MCA counters  **/
	private $reading_type;

	/** surface level reading done by RSO staff */
	private $surface_reading;

    /** background level reading done by RSO staff */
    private $background_reading;

	public function getDrum_id(){return $this->drum_id;}
	public function setDrum_id($id){$this->drum_id = $id;}

	public function getDrum(){
		$dao = new GenericDAO(new Drum());
		$this->drum = $dao->getById($this->drum);
		return $this->drum;
	}

	public function getDrum_wipes() {
		if($this->drum_wipes == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->drum_wipes = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$DRUM_WIPE_RELATIONSHIP));
		}
		return $this->drum_wipes;
	}
	public function setDrum_wipes($wipes){$this->drum_wipes = $wipes;}

	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}

	public function getSurface_reading() {return $this->surface_reading;}
	public function setSurface_reading($background_level) {$this->surface_reading = $background_level;}

    public function getBackground_reading() {return $this->background_reading;}
	public function setBackground_reading($background_level) {$this->background_reading = $background_level;}

    public function getHasWipes(){
        return count($this->getDrum_wipes()) > 0;
    }
    public function setHasWipes($has){$this->hasWipes = $has;}
}