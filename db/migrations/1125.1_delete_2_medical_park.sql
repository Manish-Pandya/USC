-- DELETE orphan inspection_room entries
DELETE FROM inspection_room
WHERE inspection_id not in (
    select key_id from inspection
);
SELECT concat ("Deleted ", row_count(), " orphan inspection_room entries") as '';

-- DELETE 2 Med building & room
DELETE hazard_room
FROM hazard_room
JOIN room ON room.key_id = hazard_room.room_id
JOIN building ON building.key_id = room.building_id
WHERE building.name = '2 Medical Park';
SELECT concat ("Deleted ", row_count(), " hazard_room entries for '2 Medical Park' room(s)") as '';

DELETE room
FROM room
    JOIN building ON building.key_id = room.building_id
WHERE building.name = '2 Medical Park';
SELECT concat ("Deleted ", row_count(), " room entries for '2 Medical Park'") as '';

DELETE building
FROM building
WHERE building.name = '2 Medical Park';
SELECT concat ("Deleted ", row_count(), " building entries for '2 Medical Park'") as '';
