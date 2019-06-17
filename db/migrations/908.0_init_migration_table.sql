DROP TABLE IF EXISTS `devops_migration`;

CREATE TABLE IF NOT EXISTS `devops_migration` (
    `version` varchar(12) NOT NULL,
    `script` varchar(128) NOT NULL,
    `date` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`version`)
);
