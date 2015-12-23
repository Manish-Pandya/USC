<?php

include_once '../GenericCrud.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class BioSafetyCabinet extends Equipment {

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRoom");
        $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator");
		$this->setEntityMaps($entityMaps);

	}
    
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
        //return array_merge(parent::$COLUMN_NAMES_AND_TYPES, this:);
		return this::$COLUMN_NAMES_AND_TYPES;
	}

	public function getDue_date(){
		
		$dueDate = new DateTime($this->getCertification_date());
		
		if($this->getFrequency() == "Annually"){
			$dueDate->modify('+1 year');
		}else if($this->getFrequency() == "Bi-annually"){
			$dueDate->modify('+2 years');
        }else{
			return null;
		}
		$this->setDue_date($dueDate->format('Y-m-d H:i:s'));		
		
		return $this->due_date;
	}

}
?>