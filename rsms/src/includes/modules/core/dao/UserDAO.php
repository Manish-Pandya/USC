<?php

class UserDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new User());
	}

	function userHasRole($user_id, $role_id){
		$sql = "SELECT COUNT(*) > 0 AS has_role FROM user_role WHERE user_id = ? AND role_id = ? ";
		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(1, $user_id);
		$stmt->bindValue(2, $role_id);

		if($stmt->execute()){
			return (bool) $stmt->fetch()[0];
		}

		$error = $stmt->errorInfo();
		return new QueryError($error[2]);
	}

	function getUserByUsername($username){
		$this->LOG->debug("Looking up user with username $username");

		$user = new User();

		//Prepare to query the user table by username
		$stmt = DBConnection::prepareStatement('SELECT * FROM ' . $user->getTableName() . ' WHERE username = ?');
		$stmt->bindParam(1,$username,PDO::PARAM_STR);
		$stmt->setFetchMode(PDO::FETCH_CLASS, "User");			// Query the db and return one user
		if ($stmt->execute()) {
			$result = $stmt->fetch();
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$this->LOG->error('Returning QueryError with message: ' . $error->getMessage());

			$result = new QueryError($error[2]);
		}

		// 'close' the statment
		$stmt = null;

		return $result;
	}
}
?>
