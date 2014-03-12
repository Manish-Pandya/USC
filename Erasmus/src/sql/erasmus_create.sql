-- TODO:
--
--	'Requires Serial Number' flag for Hazard?
--	Double-check varchar field lengths
--	Add Foreign keys for mapping tables?
--	User password hash
--	Unique columns

-- NOTES:
--
--	the definitions below for date_created allow the database to automatically set the stamp
--		to the current time IF DATE_CREATED IS SET TO NULL
--	An alternative would be to set up a trigger to set the created date on-update,
--		but that would require a trigger for every table (correct?)
--

-- Create database if we need to
CREATE DATABASE IF NOT EXISTS usc_ehs_rsms;
USE usc_ehs_rsms;

-- Define erasmus_user for holding base User entities
DROP TABLE IF EXISTS erasmus_user;
CREATE TABLE erasmus_user (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	-- user_role => *roles
	username varchar(48) NOT NULL,
	name varchar(90) NOT NULL,
	email varchar(90) NOT NULL,
	supervisor_id int(11),
	--  TODO: **password_hash
	PRIMARY KEY (key_id),
	UNIQUE (username),
	UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to hold User <-> Role mappings
DROP TABLE IF EXISTS user_role;
CREATE TABLE user_role (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	user_id int(11),
	role_id int(11),
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to hold Role entities
DROP TABLE IF EXISTS ROLE;
CREATE TABLE ROLE (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	name varchar(24),
	PRIMARY KEY (key_id),
	UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define building to hold Building entities
DROP TABLE IF EXISTS building;
CREATE TABLE building (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	name varchar(90),
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	PRIMARY KEY (key_id),
	UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to hold Room entities
DROP TABLE IF EXISTS room;
CREATE TABLE room (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	name varchar(90),
	-- map building rooms from this key
	building_id int(11) DEFAULT NULL,
	safety_contact_information varchar(1024) DEFAULT NULL,
	-- 'principal_investigator_room => *principalinvestigators
	-- 'hazard_room => *hazards
	PRIMARY KEY (key_id),
	KEY room_building (building_id),
	KEY fk_room_building (building_id),
	CONSTRAINT fk_room_building FOREIGN KEY (building_id) REFERENCES building (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table linking PrincipalInvestigator entities to their associated Room entities
DROP TABLE IF EXISTS principal_investigator_room;
CREATE TABLE principal_investigator_room (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	room_id int(11),
	principal_investigator_id int(11),
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table structure defining haz entities
DROP TABLE IF EXISTS hazard;
CREATE TABLE hazard (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	name varchar(256),
	-- nullable, for top-level hazards
	parent_hazard_id int(11),
	-- map using parent_hazard_id => *sub_hazards
	-- hazard_checklists => *checklists
	-- 'hazard_room => *rooms
	requires_serial_number boolean NOT NULL DEFAULT 0,
	PRIMARY KEY (key_id),
	KEY parent_hazard (parent_hazard_id),
	KEY fk_parent_hazard (parent_hazard_id),
	CONSTRAINT fk_parent_hazard FOREIGN KEY (parent_hazard_id) REFERENCES hazard (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
	-- TODO: UNIQUE (name)?
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table linking Hazard entities to their associated Room entities
--	This table ALSO defines physical equipment (serial nums, etc)
DROP TABLE IF EXISTS hazard_room;
CREATE TABLE hazard_room (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	room_id int(11) NOT NULL,
	hazard_id int(11) NOT NULL,
	equipment_serial_number varchar(64),
	PRIMARY KEY (key_id),
	CONSTRAINT fk_room FOREIGN KEY (room_id) REFERENCES room (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT fk_hazard FOREIGN KEY (hazard_id) REFERENCES hazard (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table linking Checklist entities to their associated Hazard entities
DROP TABLE IF EXISTS hazard_checklist;
CREATE TABLE hazard_checklist (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	checklist_id int(11) NOT NULL,
	hazard_id int(11) NOT NULL,
	PRIMARY KEY (`key_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Checklist entities
DROP TABLE IF EXISTS checklist;
CREATE TABLE checklist (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	-- hazard_checklist => *hazards
	-- question.checklist_id => *questions
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Question entities
DROP TABLE IF EXISTS question;
CREATE TABLE question (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	checklist_id int(11),
	text varchar(1024),
	order_index int(11),
	standards_and_guidelines varchar(1024),
	root_cause varchar(1024),
	is_mandatory boolean DEFAULT 0,
	-- deficiency.question_id => *deficiencies
	-- deficiency_root_cause.question_id => *deficiency_root_causes
	-- recommendation.question_id => *recommendations
	-- observation.question_id => *observations
	PRIMARY KEY (key_id),
	CONSTRAINT fk_question_checklist FOREIGN KEY (checklist_id) REFERENCES checklist (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Deficiency entiteis
DROP TABLE IF EXISTS deficiency;
CREATE TABLE deficiency (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	question_id int(11) NOT NULL,
	text varchar(1024),
	PRIMARY KEY (key_id),
	CONSTRAINT fk_deficiency_question FOREIGN KEY (question_id) REFERENCES question (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for DeficiencyRootCause entities
DROP TABLE IF EXISTS deficiency_root_cause;
CREATE TABLE deficiency_root_cause (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	created_user_id int(11) NOT NULL,
	last_modified_user_id int(11) NOT NULL,
	question_id int(11),
	text varchar(1024),
	PRIMARY KEY (key_id),
	CONSTRAINT fk_deficiency_root_cause_question FOREIGN KEY (question_id) REFERENCES question (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Define table for Response entities
DROP TABLE IF EXISTS response;
CREATE TABLE response (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	inspection_id int(11) NOT NULL,
	question_id int(11) NOT NULL,
	answer varchar(16),
	-- deficiency_selection.response_id => *deficiency_selections
	-- response_recommendation => *recommendations
	-- response_observation => *observations?
	PRIMARY KEY (key_id),
	CONSTRAINT fk_response_question FOREIGN KEY (question_id) REFERENCES question (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map Response entities to Recommendation entiteis
DROP TABLE IF EXISTS response_recommendation;
CREATE TABLE response_recommendation (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	response_id int(11) NOT NULL,
	recommendation_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map Response entities to Observation entiteis
DROP TABLE IF EXISTS response_observation;
CREATE TABLE response_observation (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	response_id int(11) NOT NULL,
	observation_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for DeficiencySelection entities
DROP TABLE IF EXISTS deficiency_selection;
CREATE TABLE deficiency_selection (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	response_id int(11),
	deficiency_id int(11),
	-- deficiency_selection_root_cause => *deficiency_root_causes
	-- deficiency_selection_corrective_action => *corrective_actions
	PRIMARY KEY (key_id),
	CONSTRAINT fk_deficiency_selection_response FOREIGN KEY (response_id) REFERENCES response (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION,
	
	CONSTRAINT fk_deficiency_selection_deficiency FOREIGN KEY (deficiency_id) REFERENCES deficiency (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for CorrectiveAction entities
DROP TABLE IF EXISTS corrective_action;
CREATE TABLE corrective_action (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	deficiency_selection_id int(11) NOT NULL,
	text varchar(1024),
	PRIMARY KEY (key_id),
	CONSTRAINT fk_deficiency_selection FOREIGN KEY (deficiency_selection_id) REFERENCES deficiency_selection (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map DeficiencySelection entities with their associated DeficiencyRootCause entities
DROP TABLE IF EXISTS deficiency_selection_root_cause;
CREATE TABLE deficiency_selection_root_cause (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	deficiency_selection_id int(11) NOT NULL,
	deficiency_root_cause_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map DeficiencySelection entities with their associated Room entities
DROP TABLE IF EXISTS deficiency_selection_room;
CREATE TABLE deficiency_selection_room (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	deficiency_selection_id int(11) NOT NULL,
	room_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map DeficiencySelection entities with their associated CorrectiveAction entities
DROP TABLE IF EXISTS deficiency_selection_corrective_action;
CREATE TABLE deficiency_selection_corrective_action (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	deficiency_selection_id int(11) NOT NULL,
	deficiency_corrective_action_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Department entities
DROP TABLE IF EXISTS department;
CREATE TABLE department (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	name varchar(90),
	-- principal_investigator_department => *principalInvestigators
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map Department entities to associated PrincipalInvestigator entities
DROP TABLE IF EXISTS principal_investigator_department;
CREATE TABLE principal_investigator_department (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	department_id int(11) NOT NULL,
	principal_investigator_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map Inspection entities to their Inspector entities
DROP TABLE IF EXISTS inspection_inspector;
CREATE TABLE inspection_inspector (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	inspection_id int(11) NOT NULL,
	inspector_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Inspector entities
DROP TABLE IF EXISTS inspector;
CREATE TABLE inspector (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	user_id int(11) NOT NULL,
	-- 'inspection_inspector => *inspections
	PRIMARY KEY (key_id),
	CONSTRAINT fk_inspector_user FOREIGN KEY (user_id) REFERENCES erasmus_user (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Observation entities
DROP TABLE IF EXISTS observation;
CREATE TABLE observation (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	question_id int(11) NOT NULL,
	text varchar(1024),
	PRIMARY KEY (key_id),
	CONSTRAINT fk_observation_question FOREIGN KEY (question_id) REFERENCES question (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Supplemental Observation entities
DROP TABLE IF EXISTS supplemental_observation;
CREATE TABLE supplemental_observation (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	response_id int(11) NOT NULL,
	text varchar(1024),
	PRIMARY KEY (key_id)	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Supplemental Recommendation entities
DROP TABLE IF EXISTS supplemental_recommendation;
CREATE TABLE supplemental_recommendation (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	response_id int(11) NOT NULL,
	text varchar(1024),
	PRIMARY KEY (key_id)	
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for PrincipalInvestigator entities
DROP TABLE IF EXISTS principal_investigator;
CREATE TABLE principal_investigator (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	user_id int(11),
	-- principal_investigator_department => *departments
	-- principal_investigator_room => *rooms
	-- pi_lab_personnel => *lab_personnel
	PRIMARY KEY (key_id),
	CONSTRAINT fk_pi_user FOREIGN KEY (user_id) REFERENCES erasmus_user (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map PrincipalInvestigator entities with their associated LabPersonnel entities
DROP TABLE IF EXISTS pi_lab_personnel;
CREATE TABLE pi_lab_personnel (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	user_id int(11) NOT NULL,
	principal_investigator_id int(11) NOT NULL,
	PRIMARY KEY (key_id),
	CONSTRAINT fk_lab_personnel_user FOREIGN KEY (user_id) REFERENCES erasmus_user (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Inspection entities
DROP TABLE IF EXISTS inspection;
CREATE TABLE inspection (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	principal_investigator_id int(11) NOT NULL,
	-- 'inspection_inspector => *inspectors
	-- 'inspection_response => *responses
	date_started datetime,
	date_closed datetime,
	PRIMARY KEY (key_id),
	CONSTRAINT fk_inspection_pi FOREIGN KEY (principal_investigator_id) REFERENCES principal_investigator (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map Inspection entities with their associated Response entities
DROP TABLE IF EXISTS inspection_response;
CREATE TABLE inspection_response (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	inspection_id int(11) NOT NULL,
	response_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table to map Inspection entities with their associated Room entities
DROP TABLE IF EXISTS inspection_room;
CREATE TABLE inspection_room (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	inspection_id int(11) NOT NULL,
	room_id int(11) NOT NULL,
	PRIMARY KEY (key_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Define table for Recommendation entities
DROP TABLE IF EXISTS recommendation;
CREATE TABLE recommendation (
	key_id int(11) NOT NULL AUTO_INCREMENT,
	is_active boolean NOT NULL DEFAULT 1,
	date_created TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
	date_last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	last_modified_user_id int(11) NOT NULL,
	question_id int(11) NOT NULL,
	text varchar(1024),
	PRIMARY KEY (key_id),
	CONSTRAINT fk_recommendation_question FOREIGN KEY (question_id) REFERENCES question (key_id) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;