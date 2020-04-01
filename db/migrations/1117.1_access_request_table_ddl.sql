CREATE TABLE `user_access_request` (
    `key_id` int(11) NOT NULL AUTO_INCREMENT,
    `is_active` tinyint(1) DEFAULT '1',
    `date_created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `created_user_id` int(11) DEFAULT NULL,
    `date_last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_modified_user_id` int(11) DEFAULT NULL,

    `network_username` varchar(32) NOT NULL,
    `principal_investigator_id` int(11) NOT NULL,
    `status` varchar(8) NOT NULL,
    PRIMARY KEY (`key_id`)
);
