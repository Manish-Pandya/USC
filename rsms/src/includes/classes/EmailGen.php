<?php
/**
 * EmailGen short summary.
 *
 * EmailGen description.
 *
 * @version 1.0
 * @author intoxopox
 */

class EmailGen extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "email_madlib";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"corpus"				=> "text",
		"title"					=> "text",
		"module"				=> "text",
		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer"
		);

	/** Relationships */
	protected static $ROOMS_RELATIONSHIP = array(
			"className"			=>	"Room",
			"tableName"			=>	"room",
			"keyName"			=>	"key_id",
			"foreignKeyName"	=>	"building_id"
	);

	/**
	 * Example corpus string
	 * @var mixed
	 */
	private $corpus = "This is a test story about {fish} and how they {toot} underwater.
		More specifically, how do {fish}'s {toot}s look, sound, and smell from the air.
		Would a low-flying {bird} be able to detect {fish}'s {toot}?
		Would {bird} be compelled to then {eat} {fish}
		Inquiring minds want to {know}";
	private $title;
	private $module;

	/**
	 * Summary of __construct
	 * @param mixed $corpus
	 */
	public function __construct($corpus) {
		if ($corpus != null) $this->corpus = $corpus;

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getCorpus");
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud //
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors //
	public function getCorpus(){ return $this->corpus; }
	public function setCorpus($deadBaby){ $this->corpus = $deadBaby; }

	public function getTitle(){ return $this->title; }
	public function setTitle($thingus){ $this->title = $thingus; }

	public function getModule(){ return $this->module; }
	public function setModule($thingus){ $this->module = $thingus; }

	/**
	 * Sample method for macro replacement
	 * @return string
	 */
	public function swapFish() {
		return "Flynn the really really fun Fish";
	}

	/**
	 * Macro key/value replacement map
	 * @return string[]
	 */
	public function macroMap() {
		return array(
			"{fish}"	=>	$this->swapFish(),
			"{toot}"	=>	"smelly fart",
			"{bird}"	=>	"Tweety Bird",
			"{eat}"		=>	"devour"
		);
	}

	/**
	 * Runs through the corpus string and replaces all string parts matching macroMap keys with their corrisponding value.
	 * @return mixed
	 */
	public function parse() {
		return str_replace(array_keys($this->macroMap()), array_values($this->macroMap()), $this->corpus);
	}

}