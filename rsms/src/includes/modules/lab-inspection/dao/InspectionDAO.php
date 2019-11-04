<?php
class InspectionDAO extends GenericDAO {

    private static $STATUS_CACHE;

    public function __construct(){
        parent::__construct(new Inspection());
        if( !isset(self::$STATUS_CACHE)){
            self::$STATUS_CACHE = CacheFactory::create('Inspection Status');
        }
    }

    function getInspectionInspectors($inspectionId){
        // Inspectors are a relatively small list; let's cache them aggressively
        return $this->getRelatedItems($inspectionId, DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP));
    }

    function getInpsectionRooms($inspectionId){
        return $this->getRelatedItems($inspectionId, DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
    }

    function getInspectionStatus($inspectionId){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $key = AppCache::key_class_id(Inspection::class, $inspectionId);

        $cached = self::$STATUS_CACHE->getCachedEntity($key);
        if( !$cached ){
            $metrickey = Metrics::start('cache all inspection_status');
            $LOG->debug("Caching all inspection statuses");

            $stmt = DBConnection::prepareStatement("select * from inspection_status");
            $stmt->execute();
            $statuses = $stmt->fetchAll(PDO::FETCH_CLASS, stdClass::class);

            foreach($statuses as $status){
                self::$STATUS_CACHE->cacheEntity(
                    $status->inspection_status,
                    AppCache::key_class_id(Inspection::class, $status->inspection_id)
                );

                if( $status->inspection_id == $inspectionId ){
                    $cached = $status->inspection_status;
                }
            }

            Metrics::stop($metrickey);
        }

        return $cached;
    }

    function getInspectionsByYear($year){
        //`inspection` where (coalesce(year(`inspection`.`date_started`),`inspection`.`schedule_year`) = ?)

        //Prepare to query all from the table
		try {
            $q = QueryUtil::selectFrom(new Inspection());

            $yearFields = Coalesce::fields(
                Field::create('date_started', 'inspection')->wrap('year'),
                Field::create('schedule_year', 'inspection')
            );

            $q->where($yearFields, '=', $year, PDO::PARAM_STR);
			$result = $q->getAll();
			return $result;
		}
		catch(QueryException $er){
			return new QueryError($er->getMessage());
		}
	}

    function getNeededInspectionsByYear($year){

        // Get schedule created by stored procedure
        $sql = "CALL GetInspectionScheduleForYear(?);";
        $stmt = DBConnection::prepareStatement($sql);
		$stmt->bindParam(1,$year,PDO::PARAM_INT);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "InspectionScheduleDto");
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}
		
		// 'close' the statment
		$stmt = null;

		return $result;
    }

    function getInspectionHasDeficiencySelections( $inspectionId ) {
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // If there are deficiencies, but they were all corrected at inspection-time,
        //   then we treat this as having no deficiencies...
        // Therefore, find the number of deficiencies for this inspection which were not corrected in-inspection

        $sql = "SELECT
          count(*) > 0 as has_deficiencies
        FROM response r
        JOIN deficiency_selection ds ON ds.response_id = r.key_id
        WHERE r.inspection_id = :inspectionId
          AND ds.corrected_in_inspection = false";

        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue(':inspectionId', $inspectionId, PDO::PARAM_INT);

        // Query the db and return an array of $this type of object
        $stmt->execute();
        $hasDeficiencies = $stmt->fetch(PDO::FETCH_COLUMN);

        return (bool) $hasDeficiencies;
    }

    public function getInspectionHazardInfo( $ids ){
        $getOne = !is_array($ids);
        if( $getOne == true ){
            $ids = array($ids);
        }

        $query = QueryUtil::select( '*', 'inspection_hazards', InspectionHazardInfoDto::class)
            ->where( Field::create('inspection_id', 'inspection_hazards'), 'IN', $ids );

        if( $getOne ){
            return $query->getOne();
        }
        else{
            return $query->getAll();
        }
    }

    // Get all inspections for a PI with a minimum scheduled-year value
    public function getPiInspectionsSince( $piId, $minYear ){
        try {
            $q = QueryUtil::selectFrom(new Inspection());

            $yearFields = Coalesce::fields(
                Field::create('date_started', 'inspection')->wrap('year'),
                Field::create('schedule_year', 'inspection')
            );

            $q->where($yearFields, '>=', $minYear, PDO::PARAM_INT)
              ->where(Field::create('principal_investigator_id', 'inspection'), '=', $piId)
              ->orderBy('inspection', 'schedule_year', "DESC")
              ->orderBy('inspection', 'schedule_month', "DESC")
              ->orderBy('inspection', 'date_started', "DESC");

			$result = $q->getAll();
			return $result;
		}
		catch(QueryException $er){
			return new QueryError($er->getMessage());
		}
    }

    /**
     * Retrieve all Checklists which have Responses in an Inspection
     *
     * @param int $inspectionId The key_id of the Inspection
     *
     * @return Array of Checklists which have Responses in the Inspection
     */
    public function getChecklistsUsedInInspection( $inspectionId ){
        $QUES_RESP_REL = DataRelationship::fromArray(array(
            "className" => Response::class,
            "tableName" => "response",
            "foreignKeyName" => "question_id",
            "sourceTableName" => 'question',
            "keyName" => "key_id",
        ));

        $CHECK_QUES_REL = DataRelationship::fromArray(array(
            "className" => Question::class,
            "tableName" => "question",
            "keyName" => "key_id",
            "foreignKeyName" => "checklist_id"
        ));

        return QueryUtil::select('*', 'checklist', Checklist::class)
            ->joinTo( $CHECK_QUES_REL )
            ->joinTo( $QUES_RESP_REL )
            ->where(Field::create('inspection_id', 'response'), '=', $inspectionId)
            ->getAll();

    }

    /**
     * Retrieve all Checklists which are assigned to an Inspection (whether they are used or not)
     *
     * @param int $inspectionId The key_id of the Inspection
     *
     * @return Array of IDs of the Checklists which are assigned to the Inspection
     */
    public function getChecklistsAssignedToInspection( $inspectionId ){
        $rel = DataRelationship::fromArray(array(
            "className" => "Checklist",
            "tableName" => "inspection_checklist",
            "foreignKeyName" => "checklist_id",
            "sourceTableName" => "checklist",
            "keyName" => "key_id",
        ));

        $checklists = QueryUtil::select('key_id', 'checklist', Checklist::class)
            ->joinTo( $rel )
            ->where(Field::create('inspection_id', $rel->getTableName()), '=', $inspectionId)
            ->getAll();

        return array_map(function($c){ return $c->getKey_id(); }, $checklists);
    }
}
