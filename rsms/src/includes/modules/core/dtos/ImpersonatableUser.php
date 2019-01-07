<?php
class ImpersonatableUser {
    private $user;
    public function __construct($user){
        $this->user = $user;
    }

    public function getUsername(){ return $this->user->getUsername(); }
    public function getFirst_name(){ return $this->user->getFirst_name(); }
    public function getLast_name(){ return $this->user->getLast_name(); }
}
?>