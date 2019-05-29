
select
    hr.key_id,
    hr.room_id,
    hr.hazard_id,
    building.name as 'building',
    room.name as 'room',
    hazard.name as 'hazard'
from hazard_room hr
join room room on hr.room_id = room.key_id
join building building on room.building_id = building.key_id
join hazard hazard on hr.hazard_id = hazard.key_id

where hr.hazard_id in (
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