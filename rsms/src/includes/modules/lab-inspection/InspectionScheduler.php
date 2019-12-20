<?php

class InspectionScheduler {
    private static $INSTANCE;

    public static function get(){
        if( !self::$INSTANCE ){
            self::$INSTANCE = new InspectionScheduler();
        }

        return self::$INSTANCE;
    }

    private function __construct(){
        $this->inspectionDao = new InspectionDAO();
        $this->piDao = new PrincipalInvestigatorDAO();
    }

    public function getInspectionSchedule(int $year ){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        $inspectable_room_types = $this->getInspectableRoomTypes();

        $LOG->info("Construct $year Inspection Schedule");

        $schedule = [];
        $mixed_inspection_ids = [];
        foreach( $inspectable_room_types as $room_type){
            /** Array containing expected/existing inspection items for this roomtype */
            $typed_schedules = $this->getTypedInspectionSchedule( $year, $room_type );
            $LOG->debug("Collected " . count($typed_schedules) . " inspections for " . $room_type->getName());

            // Merge typed inspections into the schedule array
            foreach($typed_schedules as $insp){
                // There should be one entry per PI, per-Building
                // However, the addition of RoomType into the model presents
                //   the possibility that one inspection is matched by multiple per-type queries.
                // This will de-duplicate items in the list
                $hash = $this->keyInspection($insp);

                // If an inspection references mixed room types (inspected_room_type == null),
                //   ensure its hash is not already present in our list
                if( $insp->getInspected_room_type() == null ){
                    if( in_array($hash, $mixed_inspection_ids) ){
                        // ignore this duplicate entry
                        $LOG->trace("$insp is already present in schedule (key: $hash)");
                        continue;
                    }

                    $mixed_inspection_ids[] = $hash;
                }

                // Add to the schedule
                $schedule[] = $insp;
            }
        }

        // Populate room data in each item
        $this->populateScheduleRooms($schedule);
        
        $LOG->info('Retrieved ' . count($schedule) . " inspections for $year schedule");
        return $schedule;
    }

    /**
     * Generates a hash value for an Inspection Schedule. The hash consists
     * of Inspection ID, Principal Investigator ID, and Building ID
     */
    protected function keyInspection( InspectionScheduleDTO &$inspection ){
        return implode(':', [
            $inspection->getInspection_id(),
            $inspection->getPi_key_id(),
            $inspection->getBuilding_key_id()
        ]);
    }

    /**
     * Collect Expected and Existing Inspections for a given Year and Room Type
     */
    protected function getTypedInspectionSchedule( int $year, RoomType $room_type ){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        // Init list of departments to which RoomType is restricted
        // If RoomType is not restricted to any departments,
        //   Insert a dummy department to represent 'unrestricted'
        $departments = $room_type->getRestrictedToDepartments();
        if( empty($departments) ){
            $d = new Department();
            $d->setKey_id(NULL);
            $d->setName('All Departments');
            $departments[] = $d;
        }

        /** Array containing all scheudled inspections for this year/RoomType */
        $schedule = [];

        // Collect typed schedule for each Depratment
        foreach( $departments as $dept ){
            $LOG->debug("Collecting " . $room_type->getName() . " schedule for " . $dept->getName());
            $dept_schedule = $this->inspectionDao->getNeededInspectionsByYear(
                $year,
                $room_type->getName(),
                $dept->getKey_id()
            );

            $LOG->trace("Collected " . count($dept_schedule) . " inspections for " . $room_type->getName() . " in " . $dept->getName());
            $schedule = array_merge($schedule, $dept_schedule);
        }

        return $schedule;
    }

    protected function getInspectableRoomTypes(){
        return array_filter(
            RoomType::getAll(),
            function($type) {
                return $type->isInspectable() == true;
            }
        );
    }

    protected function populateScheduleRooms( Array &$inspections ){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );
        $LOG->debug("Populating rooms for scheudle");

        foreach ($inspections as &$is){
            $LOG->trace("Processing $is...");

            if ($is->getInspection_id() !== null){
                // LOAD INSPECTION
                $inspection = $this->inspectionDao->getById($is->getInspection_id());

                // GET LIST OF INSPECTION'S ROOMS, AND FILTER THEM
                //  SO THAT ONLY ROOMS OF THIS INSPECTION'S BUILDING
                //  ARE PRESENT
                $filteredRooms = array();
                $rooms = $inspection->getRooms();
                foreach( $rooms as $room ){
                    if( $room->getBuilding_id() == $is->getBuilding_key_id() ){
                        array_push($filteredRooms, $room);
                    }
                }
                $is->setInspection_rooms( DtoFactory::buildDtos($filteredRooms, 'DtoFactory::roomToDto') );
                $is->setInspections($inspection);
            }

            // Now get the PI's Rooms which are in the Inspection's Building
            // Filter list of rooms by the schedule's RoomType.
            //   If the type is null (indicating mixed), then do not filter the rooms by type
            $inspectedRoomType = $is->getInspected_room_type();
            $rooms = $this->piDao->getRoomsInBuilding($is->getPi_key_id(), $is->getBuilding_key_id());

            if( $inspectedRoomType != NULL ){
                // Filter rooms to include only those which match the inspection's Room Type
                $rooms = array_filter( $rooms, function($room) use ($inspectedRoomType){
                    return $room->getRoom_type() == $inspectedRoomType;
                });
            }
            // Else this is a mixed inspection; do not filter rooms by type

            $is->setBuilding_rooms( DtoFactory::buildDtos($rooms, 'DtoFactory::roomToDto') );
        }

        return;
    }
}

?>