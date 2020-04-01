<?php
class CandidateUser {
    private $username;

    public function __toString(){
        return '[' . self::class . " name='$this->username']";
    }

    public function __construct( string $username ){
        $this->username = $username;
    }

    public function getUsername(){ return $this->username; }
    public function getAccess_requests(){
        $dao = new UserAccessRequestDAO();
        return $dao->getByNetworkUsername($this->username);
    }
}
?>
