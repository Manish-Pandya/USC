<?php

class AuthorizationResult implements I_AuthResult {
    private $success;
    private $authorization;
    public function __construct(bool $success, $authorization){
        $this->success = $success;
        $this->authorization = $authorization;
    }

    function success(){ return $this->success; }
    public function getAuthorization(){ return $this->authorization; }
}

?>
