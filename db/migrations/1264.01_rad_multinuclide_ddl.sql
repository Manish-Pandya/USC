-- Create parcel_authorization table
CREATE TABLE `parcel_authorization` (
    `key_id` int(11) NOT NULL AUTO_INCREMENT,
    `is_active` tinyint(1) DEFAULT '1',
    `date_created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `created_user_id` int(11) DEFAULT NULL,
    `date_last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_modified_user_id` int(11) DEFAULT NULL,

    `parcel_id` int(11) NOT NULL,
    `authorization_id` int(11) NOT NULL,
    `percentage` FLOAT NOT NULL DEFAULT '100',

    PRIMARY KEY (`key_id`)
);

-- constrain related tables
ALTER TABLE `parcel_authorization` ADD CONSTRAINT `fk_parcel_auth_x_parcel`
    FOREIGN KEY (`parcel_id`) REFERENCES `parcel` (`key_id`);

ALTER TABLE `parcel_authorization` ADD CONSTRAINT `fk_parcel_auth_x_authorization`
    FOREIGN KEY (`authorization_id`) REFERENCES `authorization` (`key_id`);
