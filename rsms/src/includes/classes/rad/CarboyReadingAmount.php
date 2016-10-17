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
        "decayed_carboy_uci"  		=> "float",
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

    /** microcuries of relevant isotope in the carboy at the time analysis was performed **/
    private $carboy_uci;
    /** microcuries of relevant isotope currently in the carboy accounting for decay, until the carboy is poured.  once the carboy is poured, this value is persisted in the db for reporting **/
    private $decayed_carboy_uci;

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

    /*
     * @return CarboyUseCycle $this->carboy_use_cycle
     */
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
		date_default_timezone_set('America/New_York');

		//the date this reading happened
		$originalDate = new DateTime();
		$originalDate->setTimestamp(time($this->getDate_read()));

		$isotope = $this->getIsotope();
		//if we don't have a volume or isotope, return NULL
		if($isotope == NULL)return;

		//multiply this sample's dpm by 100, because the sample is 1/100th of a ml
		$dpmPerML = $this->getCurie_level() * 100;
		//convert the dpm to MCi
		$sampleMci = $dpmPerML * (4.5 * pow(10,-10));

        //get the CURRENT total microcuries in the carboy;

		$daysToDecay = $this->getDecayTime($isotope->getHalf_life(), $sampleMci);

		//18 months is the max time allowed for keeping waste on hand.
		$maxDate = clone $originalDate;
		$maxDate = $maxDate->add(new DateInterval("P18M"));

		//get the number of days between the date the reading was taken and 18 months from now
		$diff = $maxDate->diff($originalDate)->format('%a');

		if($daysToDecay >= $diff){
			//high energy isotope with short half-life, defined by RSO as those with half-lives longer than 120 days (must decay as much as possible)
			if($isotope->getHalf_life() < 120){
				$daysToDecay = $diff;
			}
			//low energy isotope with long half-life (can be poured immediately)
			else{
				$daysToDecay = 0;
			}
		}

		if($daysToDecay == 0 ){
			$this->pour_allowed_date  = date("Y-m-d H:i:s",$originalDate->getTimestamp());
		}
		//the pour allowed date has passed.  amount can be poured
		elseif($daysToDecay < 0){
			$this->pour_allowed_date  = date("Y-m-d H:i:s" , $now->sub(new DateInterval('P'.abs($daysToDecay).'D'))->getTimestamp());
		}
		//pour date is in the future or now.
		else{
			$this->pour_allowed_date  = date("Y-m-d H:i:s" , $originalDate->add(new DateInterval('P'.$daysToDecay.'D'))->getTimestamp());
		}

		return $this->pour_allowed_date;
		//mCi per liter = mCi/(carboy volume in ml / 1000)?

	}

	private function getDecayTime($halfLife, $mCi){
		$LOG = Logger::getLogger(__CLASS__);
		if($mCi < .00005)return 0;
		$targetMCI = .00005;
		//the time in days(because half-lives are stored in the database in days) to decay.  we always round up to make sure that the carboy is fully decayed.
		return ceil(($halfLife/-(log(2))) * log($targetMCI/$mCi));
	}

    public function getCarboy_uci(){
        $l = Logger::getLogger(__FUNCTION__);
        $volume =  $this->getCarboy_use_cycle()->getVolume();

        //multiply this sample's dpm by 100, because the sample is 1/100th of a ml
        $dpmPerML = $this->getCurie_level() * 100;
        //convert the dpm to MCi
        $sampleMci = $dpmPerML * (4.5 * pow(10,-10));

        //convert the mCi in the sample to uCi
        $sampleUci = $sampleMci * 1000;

        //get the total microcuries in the whole carboy, at the time the analysis was performed
        $this->carboy_uci = $sampleUci * $volume;

        $stuff = array($volume, $dpmPerML, $sampleMci, $this->carboy_uci);
        $l->fatal($stuff);
        return $this->carboy_uci;
    }

    public function getDecayed_carboy_uci(){
        if($this->hasPrimaryKeyValue()){
            $cycle = $this->getCarboy_use_cycle();
            $LOG = Logger::getLogger(__FUNCTION__);
            //get the date we want to decay to.
            //if the carboy has been poured, we stop decaying at the pour date. In that case, we persist the value after calculating it
            if($cycle != null && $cycle->getPour_date() != null
                && $this->getDate_read() != null
                && $this->getCarboy_uci != null
                && $this->getIsotope() != null) {

                $endDate = $cycle->getPour_date();
            }else{
                $endDate = date('Y-m-d H:i:s');
            }

            $origUci = $this->getCarboy_uci();


            $endDateObj = new DateTime($endDate);
            $beginDateObj = new DateTime($this->getDate_read());

            //get the number of days between the time the reading was made and the date the carboy was poured
            //of today's date if the carboy hasn't been poured yet
            $decayDays = $endDateObj->diff($beginDateObj)->days;


            //calculate the current uci in the carboy
            $this->decayed_carboy_uci = $origUci * exp(-0.693147*$decayDays/$this->getIsotope()->getHalf_life());

            //if the cycle has been poured, persist this value in the database so it can be grabbed for reporting
            if($cycle->getPour_date() != null){
                $dao = new GenericDAO($this);
                $dao->save($this);
            }
        }

        return $this->decayed_carboy_uci;
    }

    public function setDecayed_carboy_uci($uci){$this->decayed_carboy_uci = $uci; }

}
?>