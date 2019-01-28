CREATE OR REPLACE VIEW `room_hazards` AS
SELECT
    room.key_id as room_id,
    pi_dept.principal_investigator_id as principal_investigator_id,
    pi_dept.department_id as department_id,

    -- top-level hazards
    room.key_id in (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 1) AS 'bio_hazards_present',
    room.key_id in (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 10009) AS 'chem_hazards_present',
    room.key_id in (select room_id from principal_investigator_hazard_room pihr JOIN hazard hazard ON hazard.key_id = pihr.hazard_id where hazard.parent_hazard_id = 10010) AS 'rad_hazards_present',

    -- special-case children
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10016)) AS 'lasers_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10015)) AS 'xrays_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 2)) AS 'recombinant_dna_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10433 OR hazard_id = 10430)) AS 'toxic_gas_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10434)) AS 'corrosive_gas_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10435)) AS 'flammable_gas_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10429 OR hazard_id = 10949)) AS 'hf_present',
    room.key_id in (
        select room_id from principal_investigator_room pir
        where pir.principal_investigator_id = pi_room.principal_investigator_id
        AND (select count(*) from principal_investigator_department pi_dept
             where pi_dept.principal_investigator_id = pi_room.principal_investigator_id
             AND pi_dept.department_id = 2
        ) > 0
    ) AS 'animal_facility'

FROM room room
JOIN principal_investigator_room pi_room ON room.key_id = pi_room.room_id
JOIN principal_investigator_department pi_dept ON pi_dept.principal_investigator_id = pi_room.principal_investigator_id;
