-- Delete all PIHR entries for top-level ('category') Hazards
-- Category hazards are children of the ROOT hazard, which is key_id=10000
DELETE FROM principal_investigator_hazard_room
WHERE hazard_id IN (
    SELECT key_id FROM hazard WHERE parent_hazard_id = 10000
);
