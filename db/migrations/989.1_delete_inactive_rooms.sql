-- Delete Inactive Rooms
    select '*** Deleting Inactive Rooms (and data related to them) ***' AS '';

    -- First delete data related to inactive rooms
    DELETE FROM principal_investigator_room WHERE room_id IN (SELECT key_id FROM room WHERE is_active = 0);
    select concat ("Deleted ", row_count(), " principal_investigator_room assignments to Inactive rooms") as '';

    DELETE FROM hazard_room WHERE room_id IN (SELECT key_id FROM room WHERE is_active = 0);
    select concat ("Deleted ", row_count(), " hazard_room assignments to Inactive rooms") as '';

    DELETE FROM principal_investigator_hazard_room WHERE room_id IN (SELECT key_id FROM room WHERE is_active = 0);
    select concat ("Deleted ", row_count(), " principal_investigator_hazard_room assignments to Inactive Rooms") as '';

    DELETE FROM inspection_room WHERE room_id IN (SELECT key_id FROM room WHERE is_active = 0);
    select concat ("Deleted ", row_count(), " inspection_room mappings to Inactive rooms") as '';

    DELETE FROM deficiency_selection_room WHERE room_id IN (SELECT key_id FROM room WHERE is_active = 0);
    select concat ("Deleted ", row_count(), " deficiency_selection_room mappings to Inactive rooms") as '';

    DELETE FROM supplemental_deficiency_room WHERE room_id IN (SELECT key_id FROM room WHERE is_active = 0);
    select concat ("Deleted ", row_count(), " supplemental_deficiency_room mappings to Inactive rooms") as '';

    -- What about specific referenced inspections? This is deleting partial data; should we remove all of them?
    select '*** Deleting 7 Inspections (106, 111, 285, 330, 305, 306, 438) ***' AS '';
    -- Pending Bryan's approval; YES we should delete these old inspections
        DELETE corrective_action
            FROM inspection inspection
            JOIN inspection_room inspection_room ON inspection_room.inspection_id = inspection.key_id
            JOIN response response ON response.inspection_id = inspection.key_id
            JOIN deficiency_selection deficiency_selection ON deficiency_selection.response_id = response.key_id
            JOIN corrective_action corrective_action ON corrective_action.deficiency_selection_id = deficiency_selection.key_id
            WHERE inspection.key_id IN (106, 111, 285, 330, 305, 306, 438);

        select concat ("Deleted ", row_count(), " corrective_action entries related to 7 inspections of Inactive rooms") as '';

        DELETE deficiency_selection
            FROM inspection inspection
            JOIN inspection_room inspection_room ON inspection_room.inspection_id = inspection.key_id
            JOIN response response ON response.inspection_id = inspection.key_id
            JOIN deficiency_selection deficiency_selection ON deficiency_selection.response_id = response.key_id
            WHERE inspection.key_id IN (106, 111, 285, 330, 305, 306, 438);

        select concat ("Deleted ", row_count(), " deficiency_selection entries to 7 inspections of Inactive rooms") as '';
    
        DELETE response
            FROM inspection inspection
            JOIN inspection_room inspection_room ON inspection_room.inspection_id = inspection.key_id
            JOIN response response ON response.inspection_id = inspection.key_id
            WHERE inspection.key_id IN (106, 111, 285, 330, 305, 306, 438);

        select concat ("Deleted ", row_count(), " response entries to 7 inspections of Inactive rooms") as '';
    
        DELETE inspection_room
            FROM inspection inspection
            JOIN inspection_room inspection_room ON inspection_room.inspection_id = inspection.key_id
            WHERE inspection.key_id IN (106, 111, 285, 330, 305, 306, 438);

        select concat ("Deleted ", row_count(), " inspection_room mappings to 7 inspections of Inactive rooms") as '';
    
        DELETE inspection
            FROM inspection inspection
            WHERE inspection.key_id IN (106, 111, 285, 330, 305, 306, 438);

        select concat ("Deleted ", row_count(), " inspection entries of 7 inspections of Inactive rooms") as '';

-- Finally, delete the inactive rooms
    DELETE FROM room WHERE is_active = 0;
    select concat ("Deleted ", row_count(), " rows from room") as '';

select '*** Completed deletion of Inactive Rooms ***' AS '';

-- Additional request: Delete 3 PIs
    select '*** Deleting 3 Inactive PIs: Daniel Freeman (#923), George Handy (#933), Leonard Gardner (#926) ***' AS '';

    -- Daniel Freeman, 923
    -- George Handy, 933
    -- Leonard Gardner, 926
    DELETE principal_investigator_department
        FROM erasmus_user erasmus_user
        JOIN principal_investigator principal_investigator ON principal_investigator.user_id = erasmus_user.key_id
        JOIN principal_investigator_department principal_investigator_department ON principal_investigator_department.principal_investigator_id = principal_investigator.key_id
        WHERE erasmus_user.key_id IN (923, 933, 926);

    select concat ("Deleted ", row_count(), " principal_investigator_department mappings from 3 PIs") as '';

    DELETE principal_investigator
        FROM erasmus_user erasmus_user
        JOIN principal_investigator principal_investigator ON principal_investigator.user_id = erasmus_user.key_id
        WHERE erasmus_user.key_id IN (923, 933, 926);

    select concat ("Deleted ", row_count(), " principal_investigator entries of 3 PIs") as '';

    DELETE erasmus_user
        FROM erasmus_user erasmus_user
        WHERE erasmus_user.key_id IN (923, 933, 926);

    select concat ("Deleted ", row_count(), " erasmus_user entries of 3 PIs") as '';
