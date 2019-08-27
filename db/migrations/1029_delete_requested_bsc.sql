-- Delete EquipmentInspections related to requested BSCs
DELETE FROM equipment_inspection WHERE equipment_class='BioSafetyCabinet' AND equipment_id IN (
    SELECT key_id FROM biosafety_cabinet WHERE serial_number IN ('14595-930', 'SG20452V')
);
SELECT CONCAT ("Deleted ", row_count(), " equipment_inspection entities related to BSCs '14595-930' and 'SG20452V'") AS '';

-- Delete requested BSCs
DELETE FROM biosafety_cabinet WHERE serial_number IN ('14595-930', 'SG20452V');
SELECT CONCAT ("Deleted ", row_count(), " biosafety_cabinet entities representing BSCs '14595-930' and 'SG20452V'") AS '';
