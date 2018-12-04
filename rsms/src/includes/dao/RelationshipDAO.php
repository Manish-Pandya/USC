<?php

class RelationshipDAO {

    /*
	 * @param RelationMapping relationship
	 */
	function getRelationships( RelationMapping $relationship ){
		//sometimes, in many to many relationships, we are asking for what we usually think of as the child objects to get their collection of parents
		//in those cases, we reverse the relationships
		if($relationship->getIsReversed()){
			$parentColumn = $relationship->getChildColumn();
			$childColumn  = $relationship->getParentColumn();
		}else{
			$parentColumn = $relationship->getParentColumn();
			$childColumn  = $relationship->getChildColumn();
		}
		$stmt = "SELECT $parentColumn as parentId, $childColumn as childId FROM " . $relationship->getTableName();
		$stmt = DBConnection::prepareStatement($stmt);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "RelationDto");
			foreach($result as &$dto){
				$dto->setTable($relationship->getTableName());
			}
			//$this->LOG->trace($result);
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}
		
		// 'close' the statment
		$stmt = null;

		return $result;
	}
}
?>