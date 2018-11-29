CREATE TABLE `inspection_personnel` (
    `inspection_id` int(11) NOT NULL,
    `personnel_id` int(11) NOT NULL
);

ALTER TABLE `inspection_personnel` ADD CONSTRAINT `fk_inspection_x_personnel`
    FOREIGN KEY (`personnel_id`) REFERENCES `erasmus_user` (`key_id`);

ALTER TABLE `inspection_personnel` ADD CONSTRAINT `fk_personnel_x_inspection`
    FOREIGN KEY (`inspection_id`) REFERENCES `inspection` (`key_id`);