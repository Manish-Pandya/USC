-- Update PSC 718 as a Teaching Lab
UPDATE room
JOIN building ON building.key_id = room.building_id
SET room.room_type = 'ANIMAL_FACILITY'
    WHERE room.name = '718'
      AND building.name = 'Jones Physical Science Center (PSC)';

SELECT concat ("Updated ", row_count(), " rooms as TEACHING_LAB type") as '';