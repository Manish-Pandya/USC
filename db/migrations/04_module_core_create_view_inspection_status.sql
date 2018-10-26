CREATE OR REPLACE VIEW `inspection_status` AS SELECT
	insp.key_id AS inspection_id,
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
	END) AS inspection_status

FROM inspection insp;
