<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Perry Cate
 */
 class ParcelUseAmount extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "parcel_use_amount";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "curie_level"          		=> "float",
        "waste_type_id"         	=> "integer",
        "carboy_id"          		=> "integer",
    	"waste_bag_id"				=> "integer",
    	"parcel_use_id"				=> "integer",
        "scint_vial_collection_id"  => "integer",
        "miscellaneous_waste_id"  => "integer",
    	"comments"					=> "text",
        "isotope_id"                => "integer",

        //GenericCrud
        "key_id"                    => "integer",
        "is_active"                 => "boolean",
        "date_created"              => "timestamp",
        "created_user_id"           => "integer",
        "date_last_modified"        => "timestamp",
        "last_modified_user_id"     => "integer"
    );

    /** Relationships */
    protected static $PARCELUSE_RELATIONSHIP = array(
    		"className" => "ParcelUse",
    		"tableName" => "parcel_use",
    		"keyName"	=> "key_id",
    		"foreignKeyName" => "parcel_id"
    );


    //access information

    /** Float amount of radiation in curies */
    private $curie_level;

    /** Reference to the waste type this use amount consists of */
    private $waste_type;
    private $waste_type_id;

    /** Reference to the carboy containing this amount. Null if not liquid waste. */
    private $carboy;
    private $carboy_id;

    private $scint_vial_collection_id;

    /** Reference to the WasteBag containing this amount.  Null if not solid wast */
    private $waste_bag;
    private $waste_bag_id;

    /** Id of is use amount's parent parcel use. */
    private $parcel_use_id;

    private $container_name;

    //comments field used to describe ParcelUseAmounts with Waste_type "Other"
    private $comments;

    /* The name of the isotope up in this ParcelUseAmount */
    private $isotope_name;

    /* The key_id of the isotope up in this ParcelUseAmount */
    private $isotope_id;

    /** boolean to indicate if this ParcelUseAmount's container has been picked up **/
    private $isPickedUp;

    private $miscellaneous_waste_id;

    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("lazy", "getCarboy");
    	$entityMaps[] = new EntityMap("eager", "getWaste_type");
    	$entityMaps[] = new EntityMap("eager", "getContainer_name");
    	$this->setEntityMaps($entityMaps);
    }

    // Required for GenericCrud
    public function getTableName() {
        return self::$TABLE_NAME;
    }

    public function getColumnData() {
        return self::$COLUMN_NAMES_AND_TYPES;
    }

    // Accessors / Mutators
    public function getCurie_level() { return $this->curie_level; }
    public function setCurie_level($newValue) { $this->curie_level = $newValue; }

    public function getWaste_type() {
    	if($this->waste_type == NULL) {
    		$wasteDao = new GenericDAO(new WasteType());
    		$this->waste_type = $wasteDao->getById($this->getWaste_type_id());
    	}
    	return $this->waste_type;
    }
    public function setWaste_type($newType) {
    	$this->waste_type = $newType;
    }

    public function getWaste_type_id() { return $this->waste_type_id; }
    public function setWaste_type_id($newValue) { $this->waste_type_id = $newValue; }

    public function getCarboy() {
    	//NOTE: may not have a carboy(_id) because not all uses are liquid waste.
    	if($this->carboy == NULL && $this->getCarboy_id() != null) {
    		$carboyDao = new GenericDAO(new Carboy());
    		$this->carboy = $carboyDao->getById($this->getCarboy_id());
    	}
    	return $this->carboy;
    }
    public function setCarboy($newCarboy) {
    	$this->carboy = $newCarboy;
    }

    public function getCarboy_id() {
    	$LOG = Logger::getLogger(__CLASS__);
    	$LOG->debug('carboy id is '.$this->carboy_id);

    	return $this->carboy_id;
    }
    public function setCarboy_id($newValue) { $this->carboy_id = $newValue; }

    public function getParcel_use_id() { return $this->parcel_use_id; }
    public function setParcel_use_id($newId) { $this->parcel_use_id = $newId; }


    public function getWaste_bag_id(){return $this->waste_bag_id;}
    public function setWaste_bag_id($waste_bag_id){$this->waste_bag_id = $waste_bag_id;}

    public function getScint_vial_collection_id(){
		return $this->scint_vial_collection_id;
	}

	public function setScint_vial_collection_id($scint_vial_collection_id){
		$this->scint_vial_collection_id = $scint_vial_collection_id;
	}

    public function getContainer_name(){
    	if($this->getWaste_bag_id() != NULL && $this->container_name == NULL){
    		$wasteBagDao = new GenericDAO(new WasteBag());
    		$wasteBag = $wasteBagDao->getById($this->getWaste_bag_id());
    		$container = $wasteBag->getContainer();
            if($container != null){
                $this->container_name = $container->getName();
            }
    	}
    	return $this->container_name;
    }
	public function getComments() {	return $this->comments;	}
	public function setComments($comments) {$this->comments = $comments;}

	public function getIsotope_name() {
        if($this->isotope_id == null){
		    $useDao = new GenericDAO(new ParcelUse());
		    $use = $useDao->getById($this->getParcel_use_id());
		    $parcel = $use->getParcel();
		    $isotope = $parcel->getIsotope();
            if($isotope != null){
                $this->isotope_name = $isotope->getName();
            }
        }
         //this ParcelUseAmount belongs to MiscellaneousWaste as opposed to a parcel
        else{
            $isotopeDao = new GenericDAO(new Isotope());
            $isotope = $isotopeDao->getById($this->isotope_id);
            if($isotope != null){
                $this->isotope_name = $isotope->getName();
            }
        }
		return $this->isotope_name;
	}

	public function getIsotope_id(){
        //ParcelUseAmounts that are used to record miscellaneous waste as opposed to use in lab experiments will have an isotope_id persisted in the db
        if($this->isotope_id == null){
            $useDao = new GenericDAO(new ParcelUse());
            $use = $useDao->getById($this->getParcel_use_id());
            $parcel = $use->getParcel();
            $isotope = $parcel->getIsotope();
            $this->isotope_id = $isotope->getKey_id();
        }
        return $this->isotope_id;
	}
    public function setIsotope_id($id){$this->isotope_id = $id;}

    public function getIsPickedUp(){
        $l = Logger::getLogger(__FUNCTION__);
        $this->isPickedUp = false;
        global $db;
		$queryString = "SELECT * from parcel_use_amount a
	                            left join waste_bag c
	                            ON a.waste_bag_id = c.key_id
	                            left join carboy_use_cycle d
	                            ON a.carboy_id = d.key_id
	                            left join scint_vial_collection e
	                            ON a.scint_vial_collection_id = e.key_id
	                            INNER join pickup f
	                            ON c.pickup_id = f.key_id
	                            OR d.pickup_id = f.key_id
	                            OR e.pickup_id = f.key_id
	                            where a.key_id = ?
	                            AND f.status != 'REQUESTED';";

		$stmt = $db->prepare($queryString);
        $stmt->bindParam(1,$this->getKey_id(),PDO::PARAM_INT);
		$stmt->execute();
        $this->isPickedUp = count($stmt->fetchAll(PDO::FETCH_CLASS, "ParcelUseAmount")) != 0;
        return (boolean) $this->isPickedUp;
    }

    public function getMiscellaneous_waste_id(){return $this->miscellaneous_waste_id;}
	public function setMiscellaneous_waste_id($miscellaneous_waste_id){$this->miscellaneous_waste_id = $miscellaneous_waste_id;}

}
?>