<?php

class UserAccessRequestDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new UserAccessRequest());
    }

    public function getByNetworkUsername( string $username, $status = NULL ){
        $f_username = Field::create('network_username', UserAccessRequest::TABLE_NAME);

        $q = QueryUtil::selectFrom( $this->modelObject )
            ->where( $f_username, '=', $username);

        if( $status != null ){
            $f_status = Field::create('status', UserAccessRequest::TABLE_NAME);

            $q->where($f_status, '=', $status);
        }

        return $q->getAll();
    }

    public function getByPrincipalInvestigator( int $pi_id, $status = NULL ){
        $f_pi = Field::create('principal_investigator_id', UserAccessRequest::TABLE_NAME);

        $q = QueryUtil::selectFrom( $this->modelObject )
            ->where( $f_pi, '=', $pi_id);

        if( $status != null ){
            $f_status = Field::create('status', UserAccessRequest::TABLE_NAME);

            $q->where($f_status, '=', $status);
        }

        return $q->getAll();
    }
}
?>
