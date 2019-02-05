<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class Authorization extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "authorization";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pi_authorization_id"			=> "integer",
		"isotope_id"					=> "integer",
		"max_quantity"					=> "float",
		"approval_date"					=> "timestamp",
		"revocation_date"				=> "timestamp",
		"form"							=> "text",
        "original_pi_auth_id"			=> "text",
        "principal_investigator_id"		=> "integer",


		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	//access information

	/** Reference to the Isotope entity that this authorization contains */
	private $isotope_id;
	private $isotope;
	private $isotopeName;

	/** id of principal_investigator this authorization is about */
	private $pi_authorization_id;
	// Note to self:
	// ommited a place to store the reference to the principal investigator itself
	// because (according to current specs at least) a principal investigator is
	// gotten first, and THEN authorization, not the other way around.

	/** maximum curie concentration that can be used */
	private $max_quantity;

	/** timestamp containing the date this authorization was... Authorized. */
	private $approval_date;

	/** timestamp containing the date this aurhtoization will expire */
	private $revocation_date;

	/** varchar containing the physical form of the authorized isotope */
	private $form;

    /**
     *
     * @var array of parcels ordered under this authorization
     */
    private $parcels;

    /**
        Each child authorization of a PI's PIAuthorizations that has the same isotope and same form should have the same UID in this field for audit/inventory calculation purposes
        By default, it should be set to the key_id of the PIAuthorization in which it, or it's ancestors first appeared, with its form appended, assuring uniqueness.
    */
    private $original_pi_auth_id;


    /** The id of the PI that owns the PIAuthorization of which this PI is a part. stored for ease of audit when running inventories */
    private $principal_investigator_id;


	public function __construct() {

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getIsotope");
		return $entityMaps;
	}

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public static $PARCELS_RELATIONSHIP = array(
        "className" => "Parcel",
        "tableName" => "parcel",
        "keyName"   => "key_id",
        "foreignKeyName" => "authorization_id"
    );

	// Accessors / Mutators
	public function getIsotope_id() { return $this->isotope_id; }
	public function setIsotope_id($newId) { $this->isotope_id = $newId; }

	public function getIsotope() {
		if($this->isotope == null && $this->getIsotope_id() !== null) {
			$isotopeDAO = new GenericDAO(new Isotope());
			$this->isotope = $isotopeDAO->getById($this->getIsotope_id());
		}
		return $this->isotope;
	}
	public function setIsotope($newIsotope) {
		$this->isotope = $newIsotope;
	}
	public function getIsotopeName(){
		$this->isotopeName = null;
		if($this->getIsotope() != null){
			$this->isotopeName = $this->getIsotope()->getName();
		}
		return $this->isotopeName;
	}

    public function getParcels(){}
    public function setParcels($parcels){$this->parcels = $parcels;}

	public function getPi_authorization_id() { return $this->pi_authorization_id; }
	public function setPi_authorization_id($newId) { $this->pi_authorization_id = $newId; }

	public function getMax_quantity() { return $this->max_quantity; }
	public function setMax_quantity($newQuantity) { $this->max_quantity = $newQuantity; }

	public function getApproval_date() { return $this->approval_date; }
	public function setApproval_date($newDate) { $this->approval_date = $newDate; }

	public function getRevocation_date() { return $this->revocation_date; }
	public function setRevocation_date($newDate) { $this->revocation_date = $newDate; }

	public function getForm(){return $this->form;}
	public function setForm($form){$this->form = $form;}

    public function makeOriginal_pi_auth_id(){
        if(($this->original_pi_auth_id || $this->principal_investigator_id == null) && $this->pi_authorization_id != null && $this->hasPrimaryKeyValue()){
            $piAuthDao = new GenericDAO(new PIAuthorization());
            $piAuth = $piAuthDao->getById($this->pi_authorization_id);
            $l = Logger::getLogger(__FUNCTION__);
            if($piAuth != null && $piAuth->getPrincipal_investigator_id() != null){
                $group = new WhereClauseGroup(array(new WhereClause("principal_investigator_id", "=", $piAuth->getPrincipal_investigator_id() )));
                $piAuths = $piAuthDao->getAllWhere($group);

                $piAuthIds = array();
                foreach($piAuths as $pia){
                    $piAuthIds[] = $pia->getKey_id();
                }

                $thisDao = new GenericDAO(new Authorization());
                $group = new WhereClauseGroup(array(
                    new WhereClause("pi_authorization_id", "IN", $piAuthIds),
                    new WhereClause("isotope_id", "=", $this->isotope_id)
                ));
                $siblingsInclusive = $thisDao->getAllWhere($group);
                $siblingMap = array();
                foreach($siblingsInclusive as $key=>$sibling){
                    if(is_object($sibling)){
                        //we assume that an authorization with a null form is for any form
                        $form = $sibling->getForm() != null ? strtoupper($sibling->getForm()) : "ANY";
                        if(!array_key_exists($form ,$siblingMap )){
                            $siblingMap[$form] = $sibling->getIsotope_id() . "-" . $form;
                        }
                        $sibling->setOriginal_pi_auth_id($siblingMap[$form]);
                        $sibling->setPrincipal_investigator_id($piAuth->getPrincipal_investigator_id());
                        if($siblingMap[$form] != null && $sibling->getKey_id() != null)
                            $sibling = $thisDao->save($sibling);
                    }
                }
            }

        }
        return $this->original_pi_auth_id;

    }
    public function getOriginal_pi_auth_id(){
        return $this->original_pi_auth_id;
    }
    public function setOriginal_pi_auth_id($id){
        $this->original_pi_auth_id = $id;
    }

    public function getPrincipal_investigator_id(){
        return $this->principal_investigator_id;
    }
    public function setPrincipal_investigator_id($id){
        $this->principal_investigator_id = $id;
    }
}
?>