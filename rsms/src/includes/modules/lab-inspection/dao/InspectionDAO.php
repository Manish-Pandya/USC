<?php
class InspectionDAO extends GenericDAO {

    private static $STATUS_CACHE;

    public function __construct(){
        parent::__construct(new Inspection());
        if( !isset(self::$STATUS_CACHE)){
            self::$STATUS_CACHE = new AppCache('Inspection Status');
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

		//Prepare to query all from the table
        $sql = "select `a`.`key_id` AS `pi_key_id`,
                concat(`b`.`last_name`,', ',`b`.`first_name`) AS `pi_name`,
                `d`.`name` AS `building_name`,
                `d`.`key_id` AS `building_key_id`,
                `e`.`name` AS `campus_name`,
                `e`.`key_id` AS `campus_key_id`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 1)) AS `bio_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 10009)) AS `chem_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 10010)) AS `rad_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10016)) AS `lasers_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10015)) AS `xrays_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 2)) AS `recombinant_dna_present`,
                bit_or(
                    c.key_id in(
                    select room_id from principal_investigator_room q
				    where q.principal_investigator_id = a.key_id
                    AND q.principal_investigator_id in
                    (select principal_investigator_id from principal_investigator_department where department_id = 2)
			    )) AS `animal_facility`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN (10430, 10433))) AS `toxic_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10434)) AS `corrosive_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10435)) AS `flammable_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN(10429,10949))) AS `hf_present`,

                year(curdate()) AS `year`,
                NULL AS `inspection_id` from (((((`principal_investigator` `a` join `erasmus_user` `b`) join `room` `c`) join `building` `d`) join `campus` `e`) join `principal_investigator_room` `f`)
                where ((`a`.`is_active` = 1) and (`c`.`is_active` = 1) and (`b`.`key_id` = `a`.`user_id`) and (`f`.`principal_investigator_id` = `a`.`key_id`) and (`f`.`room_id` = `c`.`key_id`) and (`c`.`building_id` = `d`.`key_id`) and (`d`.`campus_id` = `e`.`key_id`) and

                (not(`a`.`key_id` in
                (select `inspection`.`principal_investigator_id`
                from `inspection`
                where
                `d`.`key_id` IN (
					select `building_id` from `room`
                    where `room`.key_id IN (
                    select `room_id` from inspection_room where inspection_id IN(
                    select key_id from inspection where key_id IN (
						select key_id from inspection where principal_investigator_id = a.key_id
                    )
                    AND
					(coalesce(year(`inspection`.`date_started`),
					`inspection`.`schedule_year`) = ?)) AND (is_rad IS NULL OR is_rad = 0)
                    )
                ))

                ))) group by `a`.`key_id`,concat(`b`.`last_name`,', ',`b`.`first_name`),`d`.`name`,`d`.`key_id`,`e`.`name`,`e`.`key_id`,year(curdate()),

				NULL union select `a`.`key_id` AS `pi_key_id`,
                concat(`b`.`last_name`,', ',`b`.`first_name`) AS `pi_name`,
                `d`.`name` AS `building_name`,
                `d`.`key_id` AS `building_key_id`,
                `e`.`name` AS `campus_name`,`e`.`key_id` AS `campus_key_id`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 1)) AS `bio_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 10009)) AS `chem_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 10010)) AS `rad_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10016)) AS `lasers_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10015)) AS `xrays_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 2)) AS `recombinant_dna_present`,
                bit_or(
                    c.key_id in(
                    select room_id from principal_investigator_room q
				    where q.principal_investigator_id = a.key_id
                    AND q.principal_investigator_id in
                    (select principal_investigator_id from principal_investigator_department where department_id = 2)
			    )) AS `animal_facility`,

                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN (10430, 10433))) AS `toxic_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10434)) AS `corrosive_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10435)) AS `flammable_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN(10429,10949))) AS `hf_present`,


                coalesce(year(`g`.`date_started`),`g`.`schedule_year`) AS `year`,`g`.`key_id`
                AS `inspection_id`
                from ((((((`principal_investigator` `a` join `erasmus_user` `b`) join `room` `c`) join `building` `d`) join `campus` `e`) join `inspection_room` `f`) join `inspection` `g`)
                where ((`a`.`key_id` = `g`.`principal_investigator_id`) and (`b`.`key_id` = `a`.`user_id`) and (`g`.`key_id` = `f`.`inspection_id`) and (`c`.`key_id` = `f`.`room_id`)
                and (`c`.`building_id` = `d`.`key_id`) and (`d`.`campus_id` = `e`.`key_id`) and (coalesce(year(`g`.`date_started`),
                `g`.`schedule_year`) = ?) )
                group by `a`.`key_id`,concat(`b`.`last_name`,', ',`b`.`first_name`),`d`.`name`,`d`.`key_id`,`e`.`name`,`e`.`key_id`,coalesce(year(`g`.`date_started`),`g`.`schedule_year`),`f`.`inspection_id` ORDER BY campus_name, building_name, pi_name";
        $stmt = DBConnection::prepareStatement($sql);
		$stmt->bindParam(1,$year,PDO::PARAM_STR);
        $stmt->bindParam(2,$year,PDO::PARAM_STR);

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
}