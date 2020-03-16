DELETE room
FROM room
    JOIN building ON building.key_id = room.building_id
WHERE building.name = 'Science Building (Aiken)'
  AND room.name = '103';
SELECT concat ("Deleted ", row_count(), " room entries for Room '103' in building 'Science Building (Aiken)'") as '';
