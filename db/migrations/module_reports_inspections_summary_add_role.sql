-- Idempotently add new role for Department Chair
INSERT INTO `role` (name, bit_value)
    SELECT * FROM (SELECT 'Department Chair', 32768) AS tmp
WHERE NOT EXISTS (
    SELECT `name` FROM `role` WHERE `name` = 'Department Chair'
) LIMIT 1;
