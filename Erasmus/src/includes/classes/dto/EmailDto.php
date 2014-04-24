<?php
class EmailDto {
	private $entity_id;
	private $recipient_ids;
	private $text;

	
	public function getEntity_id(){ return $this->entity_id; }
	public function getRecipient_ids(){ return $this->recipient_ids; }
	public function getText(){ return $this->text; }
	
	public function setEntity_id($key_id){ $this->entity_id = $key_id; }
	public function setRecipient_ids($recipient_ids) { $this->recipient_ids = $recipient_ids; }
	public function setText($text) { $this->text = $text; }
}
?>