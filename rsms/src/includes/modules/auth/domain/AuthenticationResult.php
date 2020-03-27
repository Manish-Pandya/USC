<?php

class AuthenticationResult implements I_AuthResult {
    private $success;
    private $subject;

    public function __construct(bool $success, $subject){
        $this->success = $success;
        $this->subject = $subject;
    }

    function success(){ return $this->success; }
    public function getSubject(){ return $this->subject; }
}

?>
