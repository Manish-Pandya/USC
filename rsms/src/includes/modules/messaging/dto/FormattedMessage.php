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

    public function getTo(){ return $this->to; }
    public function getFrom(){ return $this->from; }
    public function getSubject(){ return $this->subject; }
    public function getBody(){ return $this->body; }
    public function getCc(){ return $this->cc; }

    public function __toString(){
        $to = $this->to;
        if( is_array($to) ){
            $to = implode(',', $to);
        }

        $cc = $this->cc;
        if( is_array($cc) ){
            $cc = implode(',', $cc);
        }

        return "FormattedMessage[$to | $cc | $this->from | $this->subject]";
    }
}

?>