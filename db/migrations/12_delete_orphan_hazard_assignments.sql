-- Delete any pi/room/hazard assignment which has been orphaned form the PI's Room assignemts (in principal_investigator_room)
DELETE FROM pihr
USING principal_investigator_hazard_room AS pihr
WHERE pihr.room_id NOT IN (
    SELECT room_id FROM principal_investigator_room WHERE principal_investigator_id = pihr.principal_investigator_id
);
