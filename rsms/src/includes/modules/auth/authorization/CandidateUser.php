<?php
class CandidateUser {
    private $username;
    public function __toString(){
        return '[' . self::class . " name='$this->username']";
    }
    public function __construct($username){ $this->username = $username; }
    public function getKey_id(){ return null; }
    public function getRoles(){ return []; }
    public function getUsername(){ return $this->username; }
}
?>
