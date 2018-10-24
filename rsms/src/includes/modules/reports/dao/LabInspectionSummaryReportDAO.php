<?php

class LabInspectionSummaryReportDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new Inspection());
    }

    public function getInspectionsReport($year, $department_id) {
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Prepare predicates to constraint (optionally) on Year and/or Department
        $predicates = array();

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

            -- Calculate inspection status (Logically copied from Inspection#getStatus())
            (CASE
                WHEN insp.date_closed IS NOT NULL THEN 'CLOSED OUT'
                WHEN insp.cap_submitted_date IS NOT NULL THEN 'SUBMITTED CAP'
                WHEN insp.notification_date IS NOT NULL THEN
                    CASE
                        -- 'when there are no deficiencies, 'CLOSED OUT'
                        WHEN (SELECT count(*) = 0 FROM deficiency_selection WHERE response_id in (SELECT key_id FROM `response` WHERE inspection_id = insp.key_id)) THEN 'CLOSED OUT'
                        -- 'when we are past 14 days after the notification_date, 'OVERDUE CAP'
                        WHEN DATE_ADD(insp.notification_date, INTERVAL 14 DAY) < CURDATE() THEN 'OVERDUE CAP'
                        ELSE 'INCOMPLETE CAP'
                    END
                WHEN insp.date_started IS NOT NULL THEN 'INCOMPLETE INSPECTION'
                WHEN insp.schedule_month IS NOT NULL THEN
                    CASE
                        -- 'when we are within 30 days of the scheduled date, inspection is pending'
                        WHEN DATE_ADD(STR_TO_DATE(CONCAT_WS('/', insp.schedule_year, insp.schedule_month, '01'), '%Y/%m/%d'), INTERVAL 30 DAY) > CURDATE() THEN
                            CASE
                                -- when inspectors are assigned, 'SCHEDULED'
                                WHEN (SELECT count(*) > 0 FROM inspection_inspector WHERE inspection_id = insp.key_id) THEN 'SCHEDULED'
                                ELSE 'NOT ASSIGNED'
                            END
                        ELSE 'OVERDUE INSPECTION'
                    END
                ELSE 'NOT SCHEDULED'
            END) AS inspection_status,

            -- Inspection details
            (SELECT count(*) FROM response resp WHERE resp.inspection_id = insp.key_id) as items_inspected,
            (SELECT count(*) FROM response resp WHERE resp.inspection_id = insp.key_id AND resp.answer != 'no') as items_compliant,

            (COALESCE(piuser.name, CONCAT_WS(', ', piuser.last_name, piuser.first_name))) AS principal_investigator_name,
            dept.key_id AS department_id,
            dept.name AS department_name

        FROM inspection insp

        JOIN principal_investigator_department pi_d
            ON pi_d.principal_investigator_id = insp.principal_investigator_id

        JOIN department dept
            ON dept.key_id = pi_d.department_id

        JOIN principal_investigator pi 
            ON pi.key_id = insp.principal_investigator_id

        JOIN erasmus_user piuser
            ON piuser.key_id = pi.user_id

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

    public function getDepartmentDetails($department_id = NULL){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Prepare predicates to constrain (optionally) Department
        $predicates = array();

        if( $department_id != NULL ){
            $predicates[] = "dept.key_id = :department_id";
        }

        $whereClause = '';
        if( count($predicates) > 0 ){
            $whereClause = 'WHERE ' . implode(' AND ', $predicates);
        }

        $sql = "SELECT
                dept.key_id AS key_id,
                dept.name AS name,
                COALESCE(dept.specialty_lab, false) AS specialty_lab,
                chair.key_id AS chair_id,
                chair.first_name AS chair_first_name,
                chair.last_name AS chair_last_name,
                (COALESCE(chair.name, CONCAT_WS(', ', chair.last_name, chair.first_name))) AS chair_name,
                chair.email AS chair_email

            FROM department dept

            -- Join to department chair users
            LEFT OUTER JOIN (
                SELECT
                    user.`key_id`,
                    user.`username`,
                    user.`first_name`,
                    user.`last_name`,
                    user.`name`,
                    user.`primary_department_id`,
                    user.`email`
                FROM erasmus_user user WHERE user.key_id IN (
                    SELECT ur.user_id FROM user_role ur WHERE ur.role_id = (
                        SELECT r.key_id FROM `role` r WHERE r.name = 'Department Chair'
                    )
                )
            ) chair
                ON chair.primary_department_id = dept.key_id

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
            $years = $this->getAvailableInspectionsForDepartment($info->getKey_id());
            $info->setAvailableInspectionYears($years);
        }

        return $result;
    }

    public function getAvailableInspectionsForDepartment($department_id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $sql = "SELECT DISTINCT schedule_year AS year FROM inspection
            WHERE principal_investigator_id IN
                ( SELECT principal_investigator_id FROM principal_investigator_department WHERE department_id = :department_id )
            AND CAST(schedule_year AS UNSIGNED) > 2016
            ORDER BY schedule_year DESC";

        // Prepare statement
        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue(':department_id', $department_id, PDO::PARAM_INT);

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