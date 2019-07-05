CREATE OR REPLACE VIEW `latest_equipment_inspection_date_subq` AS
SELECT
    equipment_id,
    GREATEST(
        COALESCE(MAX(certification_date), 0),
        COALESCE(MAX(due_date), 0)
    ) as greatest_date
FROM equipment_inspection
GROUP BY equipment_id;

-- Get the most recent inpsection for each equipment
CREATE OR REPLACE VIEW `latest_equipment_inspection` AS
SELECT
    insp.*
FROM equipment_inspection insp
INNER JOIN latest_equipment_inspection_date_subq latest
    ON insp.equipment_id = latest.equipment_id
    AND (
        insp.certification_date = latest.greatest_date
        OR insp.due_date = latest.greatest_date
    );
