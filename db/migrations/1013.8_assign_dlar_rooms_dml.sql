-- Update all of Shane Barlow's rooms as Animal Facilities
UPDATE room
JOIN principal_investigator_room pir
    ON room.key_id = pir.room_id
JOIN principal_investigator pi
    ON pi.key_id = pir.principal_investigator_id
JOIN erasmus_user u
    ON u.key_id = pi.user_id
SET room.room_type = 'ANIMAL_FACILITY'
    WHERE u.username = 'BARLOWS';

SELECT concat ("Updated ", row_count(), " rooms as ANIMAL_FACILITY type") as '';