-- Idempotently add new role for Teaching Lab Contact
INSERT INTO `role` (name, bit_value)
    SELECT * FROM (SELECT 'Teaching Lab Contact', 262144) AS tmp
WHERE NOT EXISTS (
    SELECT `name` FROM `role` WHERE `name` = 'Teaching Lab Contact'
) LIMIT 1;
