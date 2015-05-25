<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */
 class CarboyReadingAmount extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "carboy_reading_amount";

    /** Key/Value array listing column names and their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        "curie_level"          		=> "float",
        "carboy_use_cycle_id"       => "integer",
    	"isotope_id"				=> "integer",
    	"date_read"					=> "timestamp",
    		
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

    /** Reference to the CarboyUseCycle containing this amount. */
    private $carboy_use_cycle;
    private $carboy_use_cycle_id;

    /* The key_id of the isotope up in this CarboyUsageAmount */
    private $isotope_id;
    /** My own private isotope */
	private $isotope;
	
	/** the date this amount will reach .05 mCi/liter. */ 
	private $pour_allowed_date;
    
	/** date this reading took place, according to the client machine it was done on **/
	private $date_read;
	
    public function __construct() {

    	// Define which subentities to load
    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("lazy", "getCarboy_use_cycle");
    	$entityMaps[] = new EntityMap("lazy", "getIsotope");
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

    public function getCarboy_use_cycle() {
    	//NOTE: may not have a carboy(_id) because not all uses are liquid waste.
    	if($this->carboy_use_cycle == NULL && $this->getCarboy_use_cycle_id() != null) {
    		$carboyDao = new GenericDAO(new CarboyUseCycle());
    		$this->carboy_use_cycle = $carboyDao->getById($this->getCarboy_use_cycle_id());
    	}
    	return $this->carboy_use_cycle;
    }
    public function setCarboy_use_cycle($newCarboy) {$this->carboy_use_cycle = $newCarboy;}

    public function getCarboy_use_cycle_id() { 
    	$LOG = Logger::getLogger(__CLASS__);
    	$LOG->debug('carboy id is '.$this->carboy_use_cycle_id);
    	 
    	return $this->carboy_use_cycle_id; 
    }
    public function setCarboy_use_cycle_id($newValue) { $this->carboy_use_cycle_id = $newValue; }

	public function getIsotope_id(){return $this->isotope_id;}
	public function setIsotope_id($id){$this->isotope_id = $id;}
	
 	public function getIsotope() {
		if($this->isotope == null) {
			$isotopeDAO = new GenericDAO(new Isotope());
			$this->isotope = $isotopeDAO->getById($this->getIsotope_id());
		}
		return $this->isotope;
	}

	public function getDate_read(){return $this->date_read;}
	public function setDate_read($date_read){$this->date_read = $date_read;}
	
	public function getPour_allowed_date(){		
		$LOG = Logger::getLogger(__CLASS__);
		date_default_timezone_set('America/New_York');
		//get the volume of the carboy containing this amount
		$volume =  $this->getCarboy_use_cycle()->getVolume();
		$isotope = $this->getIsotope();
		//if we don't have a volume or isotope, return NULL
		if($volume == NULL || $isotope == NULL)return;
		
		//multiply this sample's dpm by 1000, because the sample is 1/1000th the volume of the scint vial
		$scintVialDpm = $this->getCurie_level() * 1000;
		
		//multiply the result by the volume of the carboy to get the total dpm for this isotope in the whole thing
		$caboyDpm = $scintVialDpm * $volume;
		
		//convert the dmp to mCi (dpm * (4.50x10^-10) = mCi)
		$carboyMci = $caboyDpm * (4.5 * pow(10,-10));
		
		$daysToDecay = $this->getDecayTime($isotope->getHalf_life(), $carboyMci);
		
		//get the date this amount can be poured on
		
		//the front end parses timestamps nicely, so we return this as one
		
		$now = new DateTime();
		//apparently, PHP believes -0 is a thing.
		// https://bugs.php.net/bug.php?id=54842 (signed 0)
		
		//180 days is the max time allowed for keeping waste on hand.  Set accordingly?
		if($daysToDecay >= 180)$daysToDecay = 180;
		
		$originalDate = new DateTime();
		$originalDate->setTimestamp(time($this->getDate_read()));
		$LOG->debug($originalDate);
		$LOG->debug($daysToDecay);
		if($daysToDecay == -0){
			$this->pour_allowed_date  = date("Y-m-d H:i:s" , $now->getTimestamp());
		}
		//the pour allowed date has passed.  amount can be poured
		elseif($daysToDecay < 0){
			$this->pour_allowed_date  = date("Y-m-d H:i:s" , $now->sub(new DateInterval('P'.abs($daysToDecay).'D'))->getTimestamp());
		}
		//pour date is in the future.
		else{
			$this->pour_allowed_date  = date("Y-m-d H:i:s" , $originalDate->add(new DateInterval('P'.$daysToDecay.'D'))->getTimestamp());
		}
		
		return $this->pour_allowed_date;
		//mCi per liter = mCi/(carboy volume in ml / 1000)?
		
	}
	
	private function getDecayTime($halfLife, $mCi){
		if($mCi < .05)return 0;
		$targetMCI = .05;
		//the time in days to decay.  we always round up to make sure that the carboy is fully decayed.
		return ceil(($halfLife/-log(2)) * log($targetMCI/$mCi));
	}
	
}
?>