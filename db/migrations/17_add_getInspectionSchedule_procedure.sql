DELIMITER //
CREATE PROCEDURE GetInspectionScheduleForYear(IN ScheduleYear int)
 BEGIN
 SELECT `principal_investigator`.`key_id` AS `pi_key_id`,
	CONCAT (
		`erasmus_user`.`last_name`,
		', ',
		`erasmus_user`.`first_name`
		) AS `pi_name`,
	`building`.`name` AS `building_name`,
	`building`.`key_id` AS `building_key_id`,
	`campus`.`name` AS `campus_name`,
	`campus`.`key_id` AS `campus_key_id`,
	bit_or(`room_hazards`.`bio_hazards_present`) AS `bio_hazards_present`,
    bit_or(`room_hazards`.`chem_hazards_present`) AS `chem_hazards_present`,
    bit_or(`room_hazards`.`rad_hazards_present`) AS `rad_hazards_present`,
    bit_or(`room_hazards`.`lasers_present`) AS `lasers_present`,
    bit_or(`room_hazards`.`xrays_present`) AS `xrays_present`,
    bit_or(`room_hazards`.`recombinant_dna_present`) AS `recombinant_dna_present`,
    bit_or(`room_hazards`.`animal_facility`) AS `animal_facility`,
    bit_or(`room_hazards`.`toxic_gas_present`) AS `toxic_gas_present`,
    bit_or(`room_hazards`.`corrosive_gas_present`) AS `corrosive_gas_present`,
    bit_or(`room_hazards`.`flammable_gas_present`) AS `flammable_gas_present`,
    bit_or(`room_hazards`.`hf_present`) AS `hf_present`,
	year(curdate()) AS `year`,
	NULL AS `inspection_id`,
	NULL AS `is_rad`

FROM `principal_investigator` `principal_investigator`
JOIN `erasmus_user` `erasmus_user` ON `erasmus_user`.`key_id` = `principal_investigator`.`user_id`
JOIN `principal_investigator_room` `principal_investigator_room` ON `principal_investigator_room`.`principal_investigator_id` = `principal_investigator`.`key_id`
JOIN `room` `room` ON `room`.`key_id` = `principal_investigator_room`.`room_id`
JOIN `building` `building` ON `building`.`key_id` = `room`.`building_id`
JOIN `campus` `campus` ON `campus`.`key_id` = `building`.`campus_id`
JOIN `room_hazards` `room_hazards` ON `room_hazards`.`room_id` = `room`.`key_id`

WHERE (
	(`principal_investigator`.`is_active` = 1)
	AND (`room`.`is_active` = 1)
	AND NOT (
		`principal_investigator`.`key_id` IN (
			SELECT `inspection`.`principal_investigator_id`
			FROM `inspection` `inspection`
			JOIN `inspection_room` `inspection_room` ON `inspection`.`key_id` = `inspection_room`.`inspection_id`
			JOIN `room` `room` ON `room`.`key_id` = `inspection_room`.`room_id`
			JOIN `building` `building` ON `building`.`key_id` = `room`.`building_id`
			WHERE `inspection`.`principal_investigator_id` = `principal_investigator`.`key_id`
			AND (coalesce(year(`inspection`.`date_started`), `inspection`.`schedule_year`) = ScheduleYear)
			AND (`inspection`.`is_rad` IS NULL OR `inspection`.`is_rad` = 0)
			GROUP BY `inspection`.`key_id`, `building`.`key_id`
		)
	)
)
GROUP BY `principal_investigator`.`key_id`,
	CONCAT (
		`erasmus_user`.`last_name`,
		', ',
		`erasmus_user`.`first_name`
		),
	`building`.`name`,
	`building`.`key_id`,
	`campus`.`name`,
	`campus`.`key_id`,
	year(curdate()),
	NULL

-- \                        /
--  \ Required Inspections /
--   ----------------------

UNION

--   ----------------------
--  / Existing Inspections \
-- /                        \

SELECT `principal_investigator`.`key_id` AS `pi_key_id`,
	CONCAT (
		`erasmus_user`.`last_name`,
		', ',
		`erasmus_user`.`first_name`
		) AS `pi_name`,
	`building`.`name` AS `building_name`,
	`building`.`key_id` AS `building_key_id`,
	`campus`.`name` AS `campus_name`,
	`campus`.`key_id` AS `campus_key_id`,
	bit_or(`room_hazards`.`bio_hazards_present`) AS `bio_hazards_present`,
    bit_or(`room_hazards`.`chem_hazards_present`) AS `chem_hazards_present`,
    bit_or(`room_hazards`.`rad_hazards_present`) AS `rad_hazards_present`,
    bit_or(`room_hazards`.`lasers_present`) AS `lasers_present`,
    bit_or(`room_hazards`.`xrays_present`) AS `xrays_present`,
    bit_or(`room_hazards`.`recombinant_dna_present`) AS `recombinant_dna_present`,
    bit_or(`room_hazards`.`animal_facility`) AS `animal_facility`,
    bit_or(`room_hazards`.`toxic_gas_present`) AS `toxic_gas_present`,
    bit_or(`room_hazards`.`corrosive_gas_present`) AS `corrosive_gas_present`,
    bit_or(`room_hazards`.`flammable_gas_present`) AS `flammable_gas_present`,
    bit_or(`room_hazards`.`hf_present`) AS `hf_present`,
	coalesce(year(`inspection`.`date_started`), `inspection`.`schedule_year`) AS `year`,
	`inspection`.`key_id` AS `inspection_id`,
	`inspection`.`is_rad` AS `is_rad`

FROM `principal_investigator` `principal_investigator`
JOIN `erasmus_user` `erasmus_user` ON `erasmus_user`.`key_id` = `principal_investigator`.`user_id`
JOIN `inspection` `inspection` ON `inspection`.`principal_investigator_id` = `principal_investigator`.`key_id`
JOIN `inspection_room` `inspection_room` ON `inspection_room`.`inspection_id` = `inspection`.`key_id`
JOIN `room` `room` ON `room`.`key_id` = `inspection_room`.`room_id`
JOIN `building` `building` ON `building`.`key_id` = `room`.`building_id`
JOIN `campus` `campus` ON `campus`.`key_id` = `building`.`campus_id`
JOIN `room_hazards` `room_hazards` ON `room_hazards`.`room_id` = `room`.`key_id`

WHERE coalesce(year(`inspection`.`date_started`), `inspection`.`schedule_year`) = ScheduleYear

GROUP BY `principal_investigator`.`key_id`,
	CONCAT (
		`erasmus_user`.`last_name`,
		', ',
		`erasmus_user`.`first_name`
		),
	`building`.`name`,
	`building`.`key_id`,
	`campus`.`name`,
	`campus`.`key_id`,
	coalesce(year(`inspection`.`date_started`), `inspection`.`schedule_year`),
	`inspection_room`.`inspection_id`
ORDER BY campus_name,
	building_name,
	pi_name
;
 END //
DELIMITER ;
