<?php

class QueueItemDto {
    public $queue_item_id;
    public $message_id;
    public $module;
    public $message_type;
    public $scheduled_date;
    public $queued_date;
    public $template_name;
    public $email_id;
    public $template_id;
    public $recipients;
    public $cc_recipients;
    public $send_from;
    public $subject;
    public $body;
    public $sent_date;
    public $context_descriptor;

    public function getQueue_item_id(){ return $this->queue_item_id; }
    public function getMessage_id(){ return $this->message_id; }
    public function getModule(){ return $this->module; }
    public function getMessage_type(){ return $this->message_type; }
    public function getScheduled_date(){ return $this->scheduled_date; }
    public function getQueued_date(){ return $this->queued_date; }
    public function getTemplate_name(){ return $this->template_name; }
    public function getEmail_id(){ return $this->email_id; }
    public function getTemplate_id(){ return $this->template_id; }
    public function getRecipients(){ return $this->recipients; }
    public function getCc_recipients(){ return $this->cc_recipients; }
    public function getSend_from(){ return $this->send_from; }
    public function getSubject(){ return $this->subject; }
    public function getBody(){ return $this->body; }
    public function getSent_date(){ return $this->sent_date; }
    public function getContext_descriptor(){ return $this->context_descriptor; }

    public function setQueue_item_id( $val ){ $this->queue_item_id = $val; }
    public function setMessage_id( $val ){ $this->message_id = $val; }
    public function setModule( $val ){ $this->module = $val; }
    public function setMessage_type( $val ){ $this->message_type = $val; }
    public function setScheduled_date( $val ){ $this->scheduled_date = $val; }
    public function setQueued_date( $val ){ $this->queued_date = $val; }
    public function setTemplate_name( $val ){ $this->template_name = $val; }
    public function setEmail_id( $val ){ $this->email_id = $val; }
    public function setTemplate_id( $val ){ $this->template_id = $val; }
    public function setRecipients( $val ){ $this->recipients = $val; }
    public function setCc_recipients( $val ){ $this->cc_recipients = $val; }
    public function setSend_from( $val ){ $this->send_from = $val; }
    public function setSubject( $val ){ $this->subject = $val; }
    public function setBody( $val ){ $this->body = $val; }
    public function setSent_date( $val ){ $this->sent_date = $val; }
    public function setContext_descriptor( $val ){ $this->context_descriptor = $val; }
}
?>