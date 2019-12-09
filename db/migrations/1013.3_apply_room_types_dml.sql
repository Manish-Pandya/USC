-- Set all Rooms as research labs
UPDATE `room` SET `room_type` = 'RESEARCH_LAB'
    WHERE `room_type` IS NULL;
select concat ("Updated ", row_count(), " room as RESEARCH_LAB type") as '';
