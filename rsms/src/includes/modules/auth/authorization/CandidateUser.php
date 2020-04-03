<?php
class CandidateUser {
    private $username;
	private $first_name;
	private $last_name;
	private $email;

    public function __toString(){
        return '[' . self::class . " name='$this->username']";
    }

    public function __construct( string $username, string $first_name, string $last_name, string $email ){
        $this->username = $username;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
    }

    public function getUsername(){ return $this->username; }
    public function getFirst_name(){ return $this->first_name; }
    public function getLast_name(){ return $this->last_name; }
    public function getEmail(){ return $this->email; }

    public function getCurrent_access_request(){
        $dao = new UserAccessRequestDAO();
        $all = $dao->getByNetworkUsername($this->username);

        if( empty($all) ){
            return null;
        }

        return $all[0];
    }

    public function getIsFulfilled(){
        $dao = new UserDAO();
        $user = $dao->getUserByUsername($this->username);

        // This candidate is 'fulfilled' if a user with our username exists
        return isset($user) && $user instanceof User && $user->hasPrimaryKeyValue();
    }
}
?>
