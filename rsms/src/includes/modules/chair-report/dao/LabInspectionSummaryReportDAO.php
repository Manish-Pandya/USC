<?php

class LabInspectionSummaryReportDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new Inspection());
    }

    public function getInspectionsReport($year, $department_id) {
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Prepare predicates to constraint (optionally) on Year and/or Department
        $predicates = array(
            // RSMS-743: Always exclude 'NOT SCHEDULED' inspections
            "insp_status.inspection_status != 'NOT SCHEDULED'"
        );

        if( $year != NULL ){
            $predicates[] = "insp.schedule_year = :year";
        }

        if( $department_id != NULL ){
            $predicates[] = "dept.key_id = :department_id";
        }

        $whereClause = '';
        if( count($predicates) > 0 ){
            $whereClause = 'WHERE ' . implode(' AND ', $predicates);
        }

        // Prepare SQL
        // TODO: Externalize 'Pending' constant
        $sql = "SELECT 
            insp.key_id AS inspection_id,
            insp.principal_investigator_id AS principal_investigator_id,
            insp.schedule_year AS schedule_year,
            insp.schedule_month AS schedule_month,
            insp.date_started AS started_date,
            insp.date_closed AS closed_date,
            insp.notification_date AS notification_date,
            insp.cap_submitted_date AS cap_submitted_date,
            insp.cap_submitter_id AS cap_submitter_id,
            insp_status.inspection_status AS inspection_status,

            insp.is_rad AS is_rad,
            inspection_hazards.bio_hazards_present as bio_hazards_present,
            inspection_hazards.chem_hazards_present as chem_hazards_present,
            inspection_hazards.rad_hazards_present as rad_hazards_present,
            inspection_hazards.lasers_present as lasers_present,
            inspection_hazards.xrays_present as xrays_present,
            inspection_hazards.recombinant_dna_present as recombinant_dna_present,
            inspection_hazards.toxic_gas_present as toxic_gas_present,
            inspection_hazards.corrosive_gas_present as corrosive_gas_present,
            inspection_hazards.flammable_gas_present as flammable_gas_present,
            inspection_hazards.hf_present as hf_present,
            inspection_hazards.animal_facility as animal_facility,

            -- Inspection details
            (SELECT count(*) FROM response resp WHERE resp.inspection_id = insp.key_id) as items_inspected,
            (SELECT count(*) FROM response resp WHERE resp.inspection_id = insp.key_id AND resp.answer != 'no') as items_compliant,
            (
                SELECT
                    sum(CASE cap.status WHEN 'Pending' THEN 1 ELSE 0 END)
                FROM response response
                LEFT OUTER JOIN deficiency_selection defsel ON defsel.response_id = response.key_id
                LEFT OUTER JOIN supplemental_deficiency supdef ON supdef.response_id = response.key_id
                JOIN corrective_action cap ON (
                    cap.deficiency_selection_id IS NOT NULL AND cap.deficiency_selection_id = defsel.key_id
                    OR
                    cap.supplemental_deficiency_id IS NOT NULL AND cap.supplemental_deficiency_id = supdef.key_id
                )

                WHERE response.inspection_id = insp.key_id

                GROUP BY response.inspection_id
            ) AS pending_caps,

            (COALESCE(piuser.name, CONCAT_WS(', ', piuser.last_name, piuser.first_name))) AS principal_investigator_name,
            dept.key_id AS department_id,
            dept.name AS department_name

        FROM inspection insp

        JOIN inspection_status insp_status
            ON insp_status.inspection_id = insp.key_id

        JOIN principal_investigator_department pi_d
            ON pi_d.principal_investigator_id = insp.principal_investigator_id

        JOIN department dept
            ON dept.key_id = pi_d.department_id

        JOIN principal_investigator pi 
            ON pi.key_id = insp.principal_investigator_id

        JOIN erasmus_user piuser
            ON piuser.key_id = pi.user_id

        JOIN inspection_hazards inspection_hazards
            ON inspection_hazards.inspection_id = insp.key_id

        -- add predicates as necessary
        $whereClause

        ORDER BY
            insp.schedule_year DESC,
            insp.schedule_month DESC,
            insp.principal_investigator_id DESC
        ";

        // Prepare statement
        $stmt = DBConnection::prepareStatement($sql);
        if( $year != NULL ){
            $stmt->bindValue(':year', $year, PDO::PARAM_STR);
        }

        if( $department_id != NULL ){
            $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        }

        // Execute the statement
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

		if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, "InspectionSummaryDto");
        }
        else {
            $error = $stmt->errorInfo();
            $LOG->error("Error querying inspection summary (y=$year, d=$department_id): " . $error[2]);

			$result = new QueryError($error[2]);
        }

        // 'close' the statement
        $stmt = null;
        return $result;
    }

    public function getDepartmentDetails($department_id, $min_year){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Prepare predicates to constrain (optionally) Department
        $predicates = array(
            'dept.is_active = true'
        );

        if( $department_id != NULL ){
            $predicates[] = "dept.key_id = :department_id";
        }

        $whereClause = '';
        if( count($predicates) > 0 ){
            $whereClause = 'WHERE ' . implode(' AND ', $predicates);
        }

        $rolename_chair = 'Department Chair';
        $rolename_coord = 'Department Safety Coordinator';

        $sql = "SELECT
                dept.key_id AS key_id,
                dept.is_active AS is_active,
                dept.name AS name,
                COALESCE(dept.specialty_lab, false) AS specialty_lab,

                chair.key_id AS chair_id,
                chair.first_name AS chair_first_name,
                chair.last_name AS chair_last_name,
                (COALESCE(chair.name, CONCAT_WS(', ', chair.last_name, chair.first_name))) AS chair_name,
                chair.email AS chair_email,

                coordinator.key_id AS coordinator_id,
                coordinator.first_name AS coordinator_first_name,
                coordinator.last_name AS coordinator_last_name,
                (COALESCE(coordinator.name, CONCAT_WS(', ', coordinator.last_name, coordinator.first_name))) AS coordinator_name,
                coordinator.email AS coordinator_email

            FROM department dept

            -- Join to department chair users
            LEFT OUTER JOIN (
                SELECT
                    user.`key_id`,
                    user.`username`,
                    user.`first_name`,
                    user.`last_name`,
                    user.`name`,
                    COALESCE (
                        user.`primary_department_id`,
                        (SELECT department_id FROM principal_investigator_department pi_dept WHERE pi_dept.principal_investigator_id = pi.key_id LIMIT 1),
                        NULL
                    ) as `primary_department_id`,
                    user.`email`
                FROM erasmus_user user
                JOIN user_role ur ON ur.user_id = user.key_id
                JOIN `role` r ON r.name = '$rolename_chair' AND r.key_id = ur.role_id
                LEFT OUTER JOIN principal_investigator pi ON pi.user_id = user.key_id
            ) chair
                ON chair.primary_department_id = dept.key_id

            -- Join to department coordinator users
            LEFT OUTER JOIN (
                SELECT
                    user.`key_id`,
                    user.`username`,
                    user.`first_name`,
                    user.`last_name`,
                    user.`name`,
                    COALESCE (
                        user.`primary_department_id`,
                        (SELECT department_id FROM principal_investigator_department pi_dept WHERE pi_dept.principal_investigator_id = pi.key_id LIMIT 1),
                        NULL
                    ) as `primary_department_id`,
                    user.`email`
                FROM erasmus_user user
                JOIN user_role ur ON ur.user_id = user.key_id
                JOIN `role` r ON r.name = '$rolename_coord' AND r.key_id = ur.role_id
                LEFT OUTER JOIN principal_investigator pi ON pi.user_id = user.key_id
            ) coordinator
                ON coordinator.primary_department_id = dept.key_id

            $whereClause
        ";

        // Prepare statement
        $stmt = DBConnection::prepareStatement($sql);
        if( $department_id != NULL ){
            $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        }

        // Execute the statement
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

		if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDetailDto");
        }
        else {
            $error = $stmt->errorInfo();
            $LOG->error("Error querying department details (d=$department_id): " . $error[2]);

			$result = new QueryError($error[2]);
        }

        // 'close' the statement
        $stmt = null;

        // Insert available inspection years to each dept
        foreach($result as $info){
            $years = $this->getAvailableInspectionsForDepartment($info->getKey_id(), $min_year);
            $info->setAvailableInspectionYears($years);

            $campuses = $this->getCampusesForDepartment($info->getKey_id());
            $info->setCampuses($campuses);
        }

        return $result;
    }

    public function getCampusesForDepartment($department_id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        //dept -> pi -> room -> building -> campus
        $sql = "SELECT
            campus.key_id,
            campus.name
        FROM principal_investigator_department pi_dept

        JOIN principal_investigator_room pi_room
            ON (pi_room.principal_investigator_id = pi_dept.principal_investigator_id)

        JOIN room room
            ON room.key_id = pi_room.room_id

        JOIN building building
            ON building.key_id = room.building_id

        JOIN campus campus
            ON campus.key_id = building.campus_id

        WHERE pi_dept.department_id = :department_id
        GROUP BY campus.key_id
        ORDER BY campus.name";

        // Prepare statement
        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);

        // Execute the statement
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

		if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, 'Campus');
        }
        else {
            $error = $stmt->errorInfo();
            $LOG->error("Error querying department campuses (d=$department_id): " . $error[2]);

			$result = new QueryError($error[2]);
        }

        // 'close' the statement
        $stmt = null;
        return $result;
    }

    public function getAvailableInspectionsForDepartment($department_id, $min_year){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $sql = "SELECT DISTINCT schedule_year AS year FROM inspection
            WHERE principal_investigator_id IN
                ( SELECT principal_investigator_id FROM principal_investigator_department WHERE department_id = :department_id )
            AND CAST(schedule_year AS UNSIGNED) >= :min_year
            ORDER BY schedule_year DESC";

        // Prepare statement
        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->bindValue(':min_year', $min_year, PDO::PARAM_INT);

        // Execute the statement
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

		if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        else {
            $error = $stmt->errorInfo();
            $LOG->error("Error querying department inspection years (d=$department_id): " . $error[2]);

			$result = new QueryError($error[2]);
        }

        // 'close' the statement
        $stmt = null;
        return $result;
    }
}
?>