<?php

class LabInspectionSummaryReportDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new Inspection());
    }

    public function getInspections($year, $department_id) {
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
            insp.date_started AS inspection_started,
            insp.date_closed AS closed,
            insp.notification_date AS notification_date,
            insp.cap_submitted_date AS cap_submitted_date,
            insp.cap_submitter_id AS cap_submitter_id,

            -- Calculate inspection status
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

            piuser.name AS principal_investigator_name,
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
            $LOG->error("Error querying inspection summary (y=$year, d=$department_id): " . $error);

			$result = new QueryError($error[2]);
        }

        // 'close' the statement
        $stmt = null;
        return $result;
    }
}
?>