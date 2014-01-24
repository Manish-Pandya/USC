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
	private $key_id;
	
	// creation date
	private $dateCreated;
	
	// last update date
	private $dateLastModified;
	
	// Active
	private $isActive;
	
	public function __toString(){
		return '[' .get_class($this) . " key_id=" . $this->getKeyId() . "]";
	}
	
	// Accessors / Mutators
	
	public function getKeyId(){
		return $this->key_id;
	}
	
	public function setKeyId($keyid){
		$this->key_id = $keyid;
	}
	
	public function getDateCreated(){
		return $this->dateCreated;
	}
	
	public function setDateCreated($dateCreated){
		$this->dateCreated = $dateCreated;
	}
	
	public function getDateLastModified(){
		return $this->dateLastModified;
	}
	
	public function setDateLastModified($dateLastModified){
		$this->dateLastModified = $dateLastModified;
	}
	
	public function getIsActive(){
		return $this->isActive;
	}
	
	public function setIsActive($isActive){
		$this->isActive = $isActive; }
	
	// CRUD Utilities
	
	/** Returns TRUE if $this has a value for its primary key */
	public function hasPrimaryKeyValue(){
		return $this->getKeyId() != null;
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
			// NOTE: DB returns $record as an instance of stdClass,
			//  so we acces $field with -> instead of as an array
			$this->$setterName( $record->$field );
		}
	}
	
	//TODO: Data-Access Operations
	
}
?>