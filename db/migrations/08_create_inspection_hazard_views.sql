CREATE OR REPLACE VIEW `room_hazards` AS
SELECT
    room.key_id as room_id,
    pi_dept.principal_investigator_id as principal_investigator_id,
    pi_dept.department_id as department_id,
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 1)) AS 'bio_hazards_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10009)) AS 'chem_hazards_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10010)) AS 'rad_hazards_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10016)) AS 'lasers_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10015)) AS 'xrays_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 2)) AS 'recombinant_dna_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10433 OR hazard_id = 10430)) AS 'toxic_gas_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10434)) AS 'corrosive_gas_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10435)) AS 'flammable_gas_present',
    room.key_id in (select room_id from principal_investigator_hazard_room where (hazard_id = 10677 OR hazard_id = 10679)) AS 'hf_present',
    room.key_id in (
        select room_id from principal_investigator_room pir
        where pir.principal_investigator_id = pi_room.principal_investigator_id
        AND (select count(*) from principal_investigator_department pi_dept
             where pi_dept.principal_investigator_id = pi_room.principal_investigator_id
             AND pi_dept.department_id = 2
        )
    ) AS animal_facility

FROM room room
JOIN principal_investigator_room pi_room ON room.key_id = pi_room.room_id
JOIN principal_investigator_department pi_dept ON pi_dept.principal_investigator_id = pi_room.principal_investigator_id;

--

CREATE OR REPLACE VIEW `inspection_hazards` AS
SELECT
    inspection_room.inspection_id as inspection_id,
    SUM(room_hazards.bio_hazards_present) as bio_hazards_present,
    SUM(room_hazards.chem_hazards_present) as chem_hazards_present,
    SUM(room_hazards.rad_hazards_present) as rad_hazards_present,
    SUM(room_hazards.lasers_present) as lasers_present,
    SUM(room_hazards.xrays_present) as xrays_present,
    SUM(room_hazards.recombinant_dna_present) as recombinant_dna_present,
    SUM(room_hazards.toxic_gas_present) as toxic_gas_present,
    SUM(room_hazards.corrosive_gas_present) as corrosive_gas_present,
    SUM(room_hazards.flammable_gas_present) as flammable_gas_present,
    SUM(room_hazards.hf_present) as hf_present,
    SUM(room_hazards.animal_facility) as animal_facility

FROM inspection_room inspection_room
JOIN room_hazards room_hazards ON room_hazards.room_id = inspection_room.room_id

GROUP BY inspection_room.inspection_id;
