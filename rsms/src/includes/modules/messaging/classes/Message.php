<?php

class Message extends GenericCrud {
    /** Name of the DB Table */
    protected static $TABLE_NAME = "message_queue";

    /** Key/Value Array listing column names mapped to their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "module"                => "text",
        "message_type"          => "text",
        "context_descriptor"    => "text",
        "sent_date"             => "timestamp",
        "send_on"               => "timestamp",

        //GenericCrud
        "key_id"                => "integer",
        "date_created"          => "timestamp",
        "date_last_modified"    => "timestamp",
        "is_active"             => "boolean",
        "last_modified_user_id" => "integer",
        "created_user_id"       => "integer"
    );

    private $module;
    private $message_type;
    private $context_descriptor;
    private $sent_date;
    private $send_on;

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

    public function getModule(){ return $this->module; }
    public function setModule($val){ $this->module = $val; }

    public function getMessage_type(){ return $this->message_type; }
    public function setMessage_type( $val ){ $this->message_type = $val; }

    public function getContext_descriptor(){ return $this->context_descriptor; }
    public function setContext_descriptor( $val ){ $this->context_descriptor = $val; }

    public function getSent_date(){ return $this->sent_date; }
    public function setSent_date( $val ){ $this->sent_date = $val; }

    public function getSend_on(){ return $this->send_on; }
    public function setSend_on( $val ){ $this->send_on = $val; }

    public function __toString(){
        return '[' . get_class($this) . " $this->module:$this->message_type($this->context_descriptor)]";
    }
}

?>