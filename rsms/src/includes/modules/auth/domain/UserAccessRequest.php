<?php
class UserAccessRequest extends GenericCrud {
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_DENIED = 'DENIED';

    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_DENIED
    ];

    public const TABLE_NAME = 'user_access_request';
    protected const COLUMN_NAMES_AND_TYPES = array(
        "network_username" => "text",
		"first_name" => "text",
		"last_name"		=> "text",
        "email"		=> "text",

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

    public static function defaultEntityMaps(){
        return [
            EntityMap::lazy("getPrincipalInvestigator")
        ];
    }
    public function getTableName(){ return self::TABLE_NAME; }
    public function getColumnData(){ return self::COLUMN_NAMES_AND_TYPES; }

    private $network_username;
	private $first_name;
	private $last_name;
	private $email;
    private $principal_investigator_id;
    private $status = self::STATUS_PENDING;

    // Transient
    private $pi;

    public function __construct(){}

    public function getNetwork_username(){ return $this->network_username; }
    public function setNetwork_username( $val ){ $this->network_username = $val; }

	public function getFirst_name(){ return $this->first_name; }
	public function setFirst_name($first_name){ $this->first_name = $first_name; }

	public function getLast_name(){ return $this->last_name; }
	public function setLast_name($last_name){ $this->last_name = $last_name; }

	public function getEmail(){ return $this->email; }
	public function setEmail($email){ $this->email = $email; }

    public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
    public function setPrincipal_investigator_id( $val ){ $this->principal_investigator_id = $val; }

    public function getStatus(){ return $this->status; }
    public function setStatus( $val ){ $this->status = $val; }

    // Transient
    public function getPrincipalInvestigator(){
        if( !isset($this->pi) ){
            $this->pi = QueryUtil::selectFrom(new PrincipalInvestigator())
                ->where(Field::create('key_id', 'principal_investigator'), '=', $this->principal_investigator_id)
                ->getOne();
        }

        return $this->pi;
    }

    public function getPrincipal_investigator_name(){
        $pi = $this->getPrincipalInvestigator();
        return $pi->getName();
    }

    public function getIs_potential_duplicate(){
        $pi_user = $this->getPrincipalInvestigator()->getUser();
        $duplicate_first = $this->getLast_name()  == $pi_user->getLast_name();
        $duplicate_last = $this->getFirst_name() == $pi_user->getFirst_name();

        return $duplicate_first && $duplicate_last;
    }
}
?>
