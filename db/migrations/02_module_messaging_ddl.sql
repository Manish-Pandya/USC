DROP TABLE IF EXISTS message_queue;
DROP TABLE IF EXISTS message_template;
DROP TABLE IF EXISTS email_queue;

-- Create table for the Message Queue (Messages)
CREATE TABLE `message_queue` (
    `key_id` int(11) NOT NULL AUTO_INCREMENT,
    `is_active` tinyint(1) DEFAULT '1',
    `date_created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `created_user_id` int(11) DEFAULT NULL,
    `date_last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_modified_user_id` int(11) DEFAULT NULL,

    `module` varchar(16),
    `message_type` varchar(64),

    `context_descriptor` TEXT,
    `sent_date` timestamp NULL DEFAULT NULL,
    `send_on` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`key_id`)
);

-- Create table for Templates
CREATE TABLE `message_template` (
    `key_id` int(11) NOT NULL AUTO_INCREMENT,
    `is_active` tinyint(1) DEFAULT '1',
    `date_created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `created_user_id` int(11) DEFAULT NULL,
    `date_last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_modified_user_id` int(11) DEFAULT NULL,

    `module` varchar(16),
    `message_type` varchar(64),

    `title` varchar(128),
    `subject` varchar(256),
    `corpus` TEXT,

    PRIMARY KEY (`key_id`)
);

-- Create table for Email Queue
CREATE TABLE `email_queue` (
    `key_id` int(11) NOT NULL AUTO_INCREMENT,
    `is_active` tinyint(1) DEFAULT '1',
    `date_created` timestamp NULL DEFAULT '0000-00-00 00:00:00',
    `created_user_id` int(11) DEFAULT NULL,
    `date_last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_modified_user_id` int(11) DEFAULT NULL,

    `message_id` int(11),
    `template_id` int(11),

    `recipients` varchar(256),
    `cc_recipients` varchar(256),
    `send_from` varchar(64),
    `subject` varchar(256),
    `body` TEXT,

    `sent_date` timestamp NULL DEFAULT NULL,

    PRIMARY KEY (`key_id`)
);