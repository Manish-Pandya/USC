<?php
class ParcelAuthorization extends RadCrud {
    public const TABLE_NAME = 'parcel_authorization';
    public const COLUMN_NAMES_AND_TYPES = [
        "parcel_id"						=> "integer",
        "authorization_id"				=> "integer",
        "percentage"					=> "float",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
    ];

    private $parcel_id;
    private $authorization_id;
    private $percentage;

    // Transient
    private $isotope;
    private $authorization;
    private $parcel;

    public static function defaultEntityMaps(){
		return [];
	}

    public function __construct() {}

	// Required for GenericCrud
	public function getTableName() {
		return self::TABLE_NAME;
	}

	public function getColumnData() {
		return self::COLUMN_NAMES_AND_TYPES;
    }

    public function getParcel_id(){ return $this->parcel_id; }
    public function setParcel_id($val){ $this->parcel_id = $val; }

    public function getAuthorization_id(){ return $this->authorization_id; }
    public function setAuthorization_id($val){ $this->authorization_id = $val; }

    public function getPercentage(){ return (float) $this->percentage; }
    public function setPercentage($val){ $this->percentage = (float) $val; }

    // Transient
	public function getIsotope() {
		if($this->isotope == null && $this->getAuthorization_id() != null) {
            $authDao = new GenericDAO(new Authorization());
            $auth = $authDao->getById($this->authorization_id);
            $this->isotope = $auth->getIsotope();
		}
		return $this->isotope;
    }

}
?>
