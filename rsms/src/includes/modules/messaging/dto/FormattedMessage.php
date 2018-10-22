<?php

class FormattedMessage {
    public $to;
    public $from;
    public $subject;
    public $body;
    public $cc;

    public function __construct($subject, $body, $to, $from, $cc){
        $this->subject = $subject;
        $this->body = $body;
        $this->to = $to;
        $this->from = $from;
        $this->cc = $cc;
    }
}

?>