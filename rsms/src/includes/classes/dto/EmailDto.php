<?php
class EmailDto {
	public $entity_id;
	public $recipient_ids;
	public $text;
	public $other_emails;

	
	public function getEntity_id(){ return $this->entity_id; }
	public function getRecipient_ids(){ return $this->recipient_ids; }
	public function getText(){ return $this->text; }
	public function getOther_emails(){ return $this->other_emails; }
	
	
	public function setEntity_id($key_id){ $this->entity_id = $key_id; }
	public function setRecipient_ids($recipient_ids) { $this->recipient_ids = $recipient_ids; }
	public function setText($text) { $this->text = $text; }
	public function setOther_emails($other_emails) { $this->other_emails = $other_emails; }
}
?>