<?php
/**
 * Base class for CRUD-related operations.
 * 
 * @author Mitch
 */
abstract class GenericCrud {
	
	// Abstract functions to support generic CRUD
	
	/** Retrieves the DB Table name for this entity */
	public abstract function getTableName();
	
	/** Retrieves a key/value array mapping DB columns to type names for this entity */
	public abstract function getColumnData();
	
	// Accessors / Mutators
	
	// CRUD Operations?
	
	// CRUD Utilities
	
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
			$this->$setterName( $record[$field] );
		}
	}
}
?>