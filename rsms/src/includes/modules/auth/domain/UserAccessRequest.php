<?php
class UserAccessRequest extends GenericCrud {
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_DENIED = 'DENIED';

    public const TABLE_NAME = 'user_access_request';
    protected const COLUMN_NAMES_AND_TYPES = array(
        "network_username" => "text",
        "principal_investigator_id" => "integer",
        "status" => "text",

        //GenericCrud
        "key_id"                => "integer",
        "date_created"          => "timestamp",
        "date_last_modified"    => "timestamp",
        "is_active"             => "boolean",
        "last_modified_user_id" => "integer",
        "created_user_id"       => "integer"
    );

    public static function defaultEntityMaps(){ return []; }
    public function getTableName(){ return self::TABLE_NAME; }
    public function getColumnData(){ return self::COLUMN_NAMES_AND_TYPES; }

    private $network_username;
    private $principal_investigator_id;
    private $status = self::STATUS_PENDING;

    public function __construct(){}

    public function getNetwork_username(){ return $this->network_username; }
    public function setNetwork_username( $val ){ $this->network_username = $val; }

    public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
    public function setPrincipal_investigator_id( $val ){ $this->principal_investigator_id = $val; }

    public function getStatus(){ return $this->status; }
    public function setStatus( $val ){ $this->status = $val; }

    // Transient
    public function getPrincipal_investigator_name(){
        $pi = QueryUtil::selectFrom(new PrincipalInvestigator())
            ->where(Field::create('key_id', 'principal_investigator'), '=', $this->principal_investigator_id)
            ->getOne();

        return $pi->getName();
    }
}
?>
