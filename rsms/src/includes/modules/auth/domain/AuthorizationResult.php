<?php

class AuthorizationResult implements I_AuthResult {
    private $success;
    private $authorization;
    private $type;

    public function __construct(bool $success, $authorization, $type){
        $this->success = $success;
        $this->authorization = $authorization;
        $this->type = $type;
    }

    function success(){ return $this->success; }
    public function getAuthorization(){ return $this->authorization; }
    public function getType(){ return $this->type; }
}

?>
