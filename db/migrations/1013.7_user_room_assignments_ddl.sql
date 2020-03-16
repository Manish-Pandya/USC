CREATE TABLE `user_room_assignment` (
  `key_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_name` varchar(32) NOT NULL,

  PRIMARY KEY (`key_id`),
  UNIQUE KEY `user_room_assignment_key` (`user_id`,`role_name`,`room_id`)
);

ALTER TABLE `user_room_assignment` ADD CONSTRAINT `fk_user_room_assignment_x_room` FOREIGN KEY (`room_id`) REFERENCES `room` (`key_id`);
ALTER TABLE `user_room_assignment` ADD CONSTRAINT `fk_user_room_assignment_x_user` FOREIGN KEY (`user_id`) REFERENCES `erasmus_user` (`key_id`);
