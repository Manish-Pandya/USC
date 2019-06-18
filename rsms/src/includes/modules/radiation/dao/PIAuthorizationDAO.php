<?php
class PIAuthorizationDAO extends GenericDAO {
    const RAD_AUTH_USER_TABLE = 'rad_authorized_users';

    public function __construct(){
        parent::__construct(new PIAuthorization());

    }

    /**
     * Get all Users which are authorized by one of the PI's Amendments
     */
    public function getAllAuthorizedUsersForPi( $pi_id ){
        // Get all Users listed in the auth/user view limited by PI
        $AUTH_FIELD_PI_ID = Field::create('principal_investigator_id', self::RAD_AUTH_USER_TABLE);
        return QueryUtil::selectFrom( new User() )
            ->joinTo( DataRelationship::fromValues( User::class, self::RAD_AUTH_USER_TABLE, 'key_id', 'user_id' ) )
            ->where( $AUTH_FIELD_PI_ID, '=', $pi_id)
            ->getAll();
    }

    /**
     * Get all PIAuthorizations which list the user as Trained Personnel
     */
    public function getUserAuthorizations( $user_id ){
        // Get all PIAuthorizations listed in the auth/user view limited by User
        $AUTH_FIELD_USER_ID = Field::create('user_id', self::RAD_AUTH_USER_TABLE);
        return QueryUtil::selectFrom( $this->modelObject )
            ->joinTo( DataRelationship::fromValues( PIAuthorization::class, self::RAD_AUTH_USER_TABLE, 'key_id', 'pi_authorization_id' ) )
            ->where( $AUTH_FIELD_USER_ID, '=', $user_id )
            ->getAll();
    }
}
?>
