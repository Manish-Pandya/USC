<?php

class MessageTemplate extends GenericCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "message_template";

    /** Key/Value Array listing column names mapped to their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "message_type"          => "text",
        "subject"               => "text",
        "corpus"                => "text",
        "title"                 => "text",
        "module"                => "text",

        //GenericCrud
        "key_id"                => "integer",
        "date_created"          => "timestamp",
        "date_last_modified"    => "timestamp",
        "is_active"             => "boolean",
        "last_modified_user_id" => "integer",
        "created_user_id"       => "integer"
        );

    protected $message_type;
    protected $subject;
    protected $corpus;
    protected $title;
    protected $module;

    public function __construct() {}

    // Required for GenericCrud //
    public function getTableName(){
        return self::$TABLE_NAME;
    }
    public function getColumnData(){
        return self::$COLUMN_NAMES_AND_TYPES;
    }

    public function getMessage_type(){ return $this->message_type; }
    public function setMessage_type($message_type){ $this->message_type = $message_type; }

    public function getSubject(){ return $this->subject; }
    public function setSubject($subject){ $this->subject = $subject; }

    public function getCorpus(){ return $this->corpus; }
    public function setCorpus($corpus){ $this->corpus = $corpus; }

    public function getTitle(){ return $this->title; }
    public function setTitle($val){ $this->title = $val; }

    public function getModule(){ return $this->module; }
    public function setModule($val){ $this->module = $val; }

}