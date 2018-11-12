<?php

class QueuedEmail extends GenericCrud {
    /** Name of the DB Table */
    protected static $TABLE_NAME = "email_queue";

    /** Key/Value Array listing column names mapped to their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "sent_date"             => "timestamp",
        "message_id"            => "integer",
        "template_id"           => "integer",
        "recipients"            => "text",
        "cc_recipients"         => "text",
        "send_from"             => "text",
        "subject"               => "text",
        "body"                  => "text",

        //GenericCrud
        "key_id"                => "integer",
        "date_created"          => "timestamp",
        "date_last_modified"    => "timestamp",
        "is_active"             => "boolean",
        "last_modified_user_id" => "integer",
        "created_user_id"       => "integer"
    );

    private $sent_date;
    private $message_id;
    private $template_id;
    private $recipients;
    private $cc_recipients;
    private $send_from;
    private $subject;
    private $body;

    public function __construct(){
        $this->setIs_active(true);
    }

    // Required for GenericCrud //
    public function getTableName(){
        return self::$TABLE_NAME;
    }
    public function getColumnData(){
        return self::$COLUMN_NAMES_AND_TYPES;
    }

    public function getSent_date() { return $this->sent_date; }
    public function setSent_date( $val ) { $this->sent_date = $val; }
    public function getMessage_id() { return $this->message_id; }
    public function setMessage_id( $val ) { $this->message_id = $val; }
    public function getTemplate_id() { return $this->template_id; }
    public function setTemplate_id( $val ) { $this->template_id = $val; }
    public function getRecipients() { return $this->recipients; }
    public function setRecipients( $val ) { $this->recipients = $val; }
    public function getCc_recipients() { return $this->cc_recipients; }
    public function setCc_recipients( $val ) { $this->cc_recipients = $val; }
    public function getSend_from() { return $this->send_from; }
    public function setSend_from( $val ) { $this->send_from = $val; }
    public function getSubject() { return $this->subject; }
    public function setSubject( $val ) { $this->subject = $val; }
    public function getBody() { return $this->body; }
    public function setBody( $val ) { $this->body = $val; }
}

?>