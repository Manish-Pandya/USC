-- Increase length of Role.name column
ALTER TABLE `role` CHANGE `name` `name` VARCHAR(32) NULL DEFAULT NULL;

-- Idempotently add new role for Department Safety Coordinator
INSERT INTO `role` (name, bit_value)
    SELECT * FROM (SELECT 'Department Safety Coordinator', 131072) AS tmp
WHERE NOT EXISTS (
    SELECT `name` FROM `role` WHERE `name` = 'Department Safety Coordinator'
) LIMIT 1;
