--
-- TODO: Need to check principal_investigator_hazard_room AND hazard_room
-- 
select
	pihr.key_id,
    pihr.principal_investigator_id,
    pihr.room_id,
    pihr.hazard_id,

    u.first_name,
    u.last_name,

    building.name as 'building',
    room.name as 'room',

    h.name as 'hazard',
    (SELECT count(*) from hazard_room hr where hr.hazard_id = pihr.hazard_id AND hr.room_id = pihr.room_id ) as in_hazard_room_table

from principal_investigator_hazard_room pihr

join room room on room.key_id = pihr.room_id
join building building on room.building_id = building.key_id
join hazard h on h.key_id = pihr.hazard_id
join principal_investigator pi on pi.key_id = pihr.principal_investigator_id
join erasmus_user u on u.key_id = pi.user_id

where pihr.hazard_id in (
    10880,
    10867,
    10849,
    10800,
    10809,
    10869,
    10945,
    10853,
    10946,
    10451
)

order by u.last_name, u.first_name, building.name, room.name, h.name