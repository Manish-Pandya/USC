<?php
class EntityText {
	private $entity_id;
	private $text;

	
	public function getEntity_id(){ return $this->entity_id; }
	public function getText(){ return $this->text; }
	
	public function setEntity_id($key_id){ $this->entity_id = $key_id; }
	public function setText($text) { $this->text = $text; }
}
?>