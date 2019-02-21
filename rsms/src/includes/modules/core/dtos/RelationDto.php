<?php
Class RelationDto implements JsonSerializable {
	use DtoGenerator;

	private $table;
	private $parentId;
	private $childId;

	public function getParentId(){
		return $this->parentId;
	}
	
	public function setParentId($parentId){
		$this->parentId = $parentId;
	}
	
	public function getChildId(){
		return $this->childId;
	}
	
	public function setChildId($childId){
		$this->childId = $childId;
	}
	
	public function getTable(){return $this->table;}
	public function setTable($table){$this->table = $table;}

}
