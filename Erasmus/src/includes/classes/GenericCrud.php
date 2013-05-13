<?php
/**
 * Base class for CRUD-related operations.
 * 
 * @author Mitch
 */
abstract class GenericCrud {
	
	// Abstract functions to support generic CRUD
	
	/** Retrieves the DB Table name for this entity */
	public static abstract function getTableName();
	
	/** Retrieves a key/value array mapping DB columns to type names for this entity */
	public static abstract function getColumnData();
	
	// Accessors / Mutators
	
	// CRUD Operations?
	
	// CRUD Utilities
	
	/**
	 * Populates all fields declared in {@code getColumnNames()} on {@code $this}
	 * with the associated values contained in {@code $record}
	 * 
	 * @param unknown $record
	 */
	function populateFromDbRecord( $record ){
		//Get just the keys
		$columns = array_keys( $this->getColumnData() );
		foreach( $columns as $field ) {
			$this->$field = $record->$field;
		}
	}
}
?>