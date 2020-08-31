-- Delete any assignments of the Biosafety Officer role
DELETE FROM `user_role` WHERE `role_id` = (SELECT `key_id` FROM `role` WHERE `name` = 'Biosafety Officer');

-- Delete the 'Biosafety Officer' role
DELETE FROM `role` WHERE `name` = 'Biosafety Officer';
