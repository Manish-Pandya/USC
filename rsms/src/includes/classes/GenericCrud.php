<?php
/**
 * Base class for CRUD-related operations. This abstract class is
 * a basis for entity classes, and provides functions to enable the DAO
 *
 * @author Mitch
 * @see GenericDAO.php
 */
abstract class GenericCrud {

	// Abstract functions to support generic CRUD

	/** Retrieves the DB Table name for this entity */
	public abstract function getTableName();

	/** Retrieves a key/value array mapping DB columns to type names for this entity */
	public abstract function getColumnData();

	// Member fields

	// Primary key
	protected $key_id;

	// creation date
	protected $date_created;

	// last update date
	protected $date_last_modified;

	// Active
	protected $is_active;

	// User who last updated
	protected $last_modified_user_id;

	// User who created
	protected $created_user_id;

	// Array of EntityMap objects that describe which properties are entities and how to load them.
	protected $entityMaps;

	public function __toString(){
		return '[' .get_class($this) . " key_id=" . $this->getKey_Id() . "]";
	}

	// Accessors / Mutators

	public function getKey_id(){
		return $this->key_id;
	}

	public function setKey_id($keyid){
		$this->key_id = $keyid;
	}

	public function getDate_created(){
		return $this->date_created;
	}

	public function setDate_created($dateCreated){
		$this->date_created = $dateCreated;
	}

	public function getDate_last_modified(){
		return $this->date_last_modified;
	}

	public function getLast_modified_user_id(){
		return $this->last_modified_user_id;
	}

	public function getCreated_user_id() {
		return $this->created_user_id;
	}

	public function setDate_last_modified($dateLastModified){
		$this->date_last_modified = $dateLastModified;
	}

	public function getIs_active(){
		return $this->is_active;
	}

	public function setIs_active($isActive){
		$this->is_active = $isActive; }

	public function setLast_modified_user_id($id){
		$this->last_modified_user_id = $id; }

	public function setCreated_user_id($id) {
		$this->created_user_id = $id;
	}

	public function getEntityMaps(){
		return $this->entityMaps;
	}

	public function setEntityMaps($entity_maps){
		if ($this->entityMaps !== null) {
			$entity_maps = array_merge($this->entityMaps, $entity_maps);
		}
		$this->entityMaps = $entity_maps;
	}

		// CRUD Utilities

	/** Returns TRUE if $this has a value for its primary key */
	public function hasPrimaryKeyValue(){
		return $this->getKey_id() != null;
	}

	/**
	 * Populates all fields declared in {@code getColumnNames()} on {@code $this}
	 * with the associated values contained in {@code $record}, where {@code $record}
	 * is expected to be an array (or an object accessible such as an array)
	 *
	 * @param unknown $record
	 */
	function populateFromDbRecord( $record ){
		//TODO: What about relationships?

		//Get just the keys
		$columns = array_keys( $this->getColumnData() );
		foreach( $columns as $field ) {
			$fname = $field;
			$fname[0] = strtoupper($fname[0]);
			//Build name of the mutator function
			$setterName = "set$fname";

			//Pass field value to the mutator

			// NOTE: DB call instantiates stdClass fields as all lower case,
			//  so we must access them as lower case
			$lowerField = strtolower($fname);

			// NOTE: DB returns $record as an instance of stdClass,
			//  so we acces $field with -> instead of as an array
			$this->$setterName( $record->$lowerField );
		}
	}

	//TODO: Data-Access Operations

}
?>