-- Add Department/RoomType mapping table
CREATE TABLE `department_inspect_room_type` (
    `department_id` int(11),
    `inspect_room_type` varchar(24)
);

ALTER TABLE `department_inspect_room_type` ADD CONSTRAINT `fk_dept_room_type_x_dept`
    FOREIGN KEY (`department_id`) REFERENCES `department` (`key_id`);

-- Map Animal Facility rooms to be restricted to DLAR department
INSERT INTO `department_inspect_room_type` (`department_id`, `inspect_room_type`)
    VALUES (
        (SELECT `key_id` from `department` WHERE `name` LIKE '%DLAR%' LIMIT 1),
        'ANIMAL_FACILITY'
    );
