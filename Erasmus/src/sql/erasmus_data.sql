--
--  ERASMUS database sample data script
--
--  WARNING: Running this script will delete all data from database and replace
--  	with sample data.
--
--

USE erasmus;

SET foreign_key_checks = 0;

-- Empty tables
TRUNCATE TABLE erasmus_user;
TRUNCATE TABLE building;
TRUNCATE TABLE room;
TRUNCATE TABLE hazard;
TRUNCATE TABLE principal_investigator;
TRUNCATE TABLE inspector;
TRUNCATE TABLE department;
TRUNCATE TABLE principal_investigator_department;
-- TODO: Add Tables

-- Reset counters
ALTER TABLE erasmus_user AUTO_INCREMENT = 1;
ALTER TABLE building AUTO_INCREMENT = 1;
ALTER TABLE room AUTO_INCREMENT = 1;
ALTER TABLE hazard AUTO_INCREMENT = 1;
ALTER TABLE principal_investigator AUTO_INCREMENT = 1;
ALTER TABLE inspector AUTO_INCREMENT = 1;
ALTER TABLE department AUTO_INCREMENT = 1;
ALTER TABLE principal_investigator_department AUTO_INCREMENT = 1;
-- TODO: Add Tables

SET foreign_key_checks = 1;

-- Create users
INSERT INTO erasmus_user (
	date_created,
	created_user_id,
	last_modified_user_id,
	username,
	name,
	email
)
VALUES
	(null, 1, 1, 'admin', 'Admin', 'mmartin+admin@graysail.com'),
	(null, 1, 1, 'mmartin', 'Mitch Martin', 'mmartin@graysail.com'),
	(null, 1, 1, 'pi1', 'PI Number 1', 'mmartin+pi1@graysail.com'),
	(null, 1, 1, 'inpectorgadget', 'Inspector Gadget', 'mmartin+inspectorgadget@graysail.com')
;

-- Create Buildings
INSERT INTO building (
	date_created,
	name,
	created_user_id,
	last_modified_user_id
)
VALUES
	(null, 'Building 1', 1, 1),
	(null, 'Building 2', 1, 1),
	(null, 'Building 3', 1, 1)
;

-- Create Rooms for building 1
INSERT INTO room (
	date_created,
	created_user_id,
	last_modified_user_id,
	name,
	building_id
)
VALUES
	(null, 1, 1, '101', 1),
	(null, 1, 1, '102', 1),
	(null, 1, 1, '103', 1)
;

-- Create Rooms for building 2
INSERT INTO room (
	date_created,
	created_user_id,
	last_modified_user_id,
	name,
	building_id
)
VALUES
	(null, 1, 1, '101', 2),
	(null, 1, 1, '102', 2),
	(null, 1, 1, '103', 2)
;

-- Create Rooms for building 3
INSERT INTO room (
	date_created,
	created_user_id,
	last_modified_user_id,
	name,
	building_id
)
VALUES
	(null, 1, 1, '101', 3),
	(null, 1, 1, '102', 3),
	(null, 1, 1, '103', 3)
;

-- Create Departments
INSERT INTO department (
	date_created,
	created_user_id,
	last_modified_user_id,
	name
)
VALUES
	(null, 1, 1, 'Department of Research Safety and Compliance')
;

-- Create PrincipalInvestigators
INSERT INTO principal_investigator (
	date_created,
	created_user_id,
	last_modified_user_id,
	user_id
)
VALUES
	(null, 1, 1, 3)
;

-- Assign Departments to PrincipalInvestigators
INSERT INTO principal_investigator_department (
	department_id,
	principal_investigator_id
)
VALUES
	(1, 1)
;

-- Assign Rooms to PrincipalInvestigators
INSERT INTO principal_investigator_room (
	room_id,
	principal_investigator_id
)
VALUES
	(1, 1),
	(2, 1),
	(3, 1)
;

-- Create Inspectors
INSERT INTO inspector (
	date_created,
	created_user_id,
	last_modified_user_id,
	user_id
)
VALUES
	(null, 1, 1, 4)
;

-- Create Hazards
INSERT INTO hazard (date_created, created_user_id, last_modified_user_id, parent_hazard_id, name)
	VALUES
		(null, 1, 1, null, 'Biological Materials'),
		(null, 1, 1, 1, 'Recombinant DNA'),
		(null, 1, 1, 2, 'Viral Vectors'),
		(null, 1, 1, 3, 'Adeno-associated Virus (AAV)'),
		(null, 1, 1, 3, 'Adenovirus'),
		(null, 1, 1, 3, 'Baculovirus'),
		(null, 1, 1, 3, 'Epstein-Barr Virus (EBV)'),
		(null, 1, 1, 3, 'Herpes Simplex Virus (HSV)'),
		(null, 1, 1, 3, 'Poxvirus / Vaccinia'),
		(null, 1, 1, 3, 'Retrovirus / Lentivirus (EIAV)'),
		(null, 1, 1, 3, 'Retrovirus / Lentivirus (FIV)'),
		(null, 1, 1, 3, 'Retrovirus / Lentivirus (HIV)'),
		(null, 1, 1, 3, 'Retrovirus / Lentivirus (SIV)'),
		(null, 1, 1, 3, 'Retrovirus / MMLV (Amphotropic or Pseudotyped)'),
		(null, 1, 1, 3, 'Retrovirus / MMLV (Ecotropic)'),
		(null, 1, 1, 1, 'Select AGENTS and Toxins'),	-- 16
		(null, 1, 1, 16, 'HHS Select Agents and Toxins'),	-- 17
		(null, 1, 1, 17, 'Abrin'),
		(null, 1, 1, 17, 'Botulinum neurotoxins'),
		(null, 1, 1, 17, 'Botulinum neurotoxin producing species of Clostrkey_idium'),
		(null, 1, 1, 17, 'Cercopithecine herpesvirus 1 (Herpes B virus)'),
		(null, 1, 1, 17, 'Clostrkey_idium perfringens epsilon toxin'),
		(null, 1, 1, 17, 'Cocckey_idiokey_ides posadasii/Cocckey_idiokey_ides immitis'),
		(null, 1, 1, 17, 'Conotoxins'),
		(null, 1, 1, 17, 'Coxiella burnetii'),
		(null, 1, 1, 17, 'Crimean-Congo haemorrhagic fever virus'),
		(null, 1, 1, 17, 'Diacetoxyscirpenol'),
		(null, 1, 1, 17, 'Eastern Equine Encephalitis virus'),
		(null, 1, 1, 17, 'Ebola virus'),
		(null, 1, 1, 17, 'Francisella tularensis'),
		(null, 1, 1, 17, 'Lassa fever virus'),
		(null, 1, 1, 17, 'Marburg virus'),
		(null, 1, 1, 17, 'Monkeypox virus'),
		(null, 1, 1, 17, 'Reconstructed 1918 Influenza virus'),
		(null, 1, 1, 17, 'Ricin'),
		(null, 1, 1, 17, 'Rickettsia prowazekii'),
		(null, 1, 1, 17, 'Rickettsia rickettsii'),
		(null, 1, 1, 17, 'Saxitoxin'),
		(null, 1, 1, 17, 'Shiga-like ribosome inactivating proteins'),
		(null, 1, 1, 17, 'Shigatoxin'),
		(null, 1, 1, 17, 'South American Haemorrhagic Fever viruses'),	-- 41
		(null, 1, 1, 41, 'Flexal'),
		(null, 1, 1, 41, 'Guanarito'),
		(null, 1, 1, 41, 'Junin'),
		(null, 1, 1, 41, 'Machupo'),
		(null, 1, 1, 41, 'Sabia'),
		(null, 1, 1, 17, 'Staphylococcal enterotoxins'),
		(null, 1, 1, 17, 'T-2 toxin'),
		(null, 1, 1, 17, 'Tetrodotoxin'),
		(null, 1, 1, 17, 'Tick-borne encephalitis complex (flavi) viruses'),	-- 45
		(null, 1, 1, 45, 'Central European Tick-borne encephalitis'),
		(null, 1, 1, 45, 'Far Eastern Tick-borne encephalitis'),
		(null, 1, 1, 45, 'Kyasanur Forest disease'),
		(null, 1, 1, 45, 'Omsk Hemorrhagic Fever'),
		(null, 1, 1, 45, 'Russian Spring and Summer encephalitis'),
		(null, 1, 1, 17, 'Variola major virus (Smallpox virus)'),
		(null, 1, 1, 17, 'Variola minor virus (Alastrim)'),
		(null, 1, 1, 17, 'Yersinia pestis'),
		(null, 1, 1, 16, 'OVERLAP SELECT AGENTS AND TOXINS'),	-- 59
		(null, 1, 1, 59, 'Bacillus anthracis'),
		(null, 1, 1, 59, 'Brucella abortus'),
		(null, 1, 1, 59, 'Brucella melitensis'),
		(null, 1, 1, 59, 'Brucella suis'),
		(null, 1, 1, 59, 'Burkholderia mallei (formerly Pseudomonas mallei)'),
		(null, 1, 1, 59, 'Burkholderia pseudomallei'),
		(null, 1, 1, 59, 'Hendra virus'),
		(null, 1, 1, 59, 'Nipah virus'),
		(null, 1, 1, 59, 'Rift Valley fever virus'),
		(null, 1, 1, 59, 'Venezuelan Equine Encephalitis virus'),
		(null, 1, 1, 16, 'USDA VETERINARY SERVICES (VS) SELECT AGENTS'),	-- 70
		(null, 1, 1, 70, 'African horse sickness virus'),
		(null, 1, 1, 70, 'African swine fever virus'),
		(null, 1, 1, 70, 'Akabane virus'),
		(null, 1, 1, 70, 'Avian influenza virus (highly pathogenic)'),
		(null, 1, 1, 70, 'Bluetongue virus (exotic)'),
		(null, 1, 1, 70, 'Bovine spongiform encephalopathy agent'),
		(null, 1, 1, 70, 'Camel pox virus'),
		(null, 1, 1, 70, 'Classical swine fever virus'),
		(null, 1, 1, 70, 'Ehrlichia ruminantium (Heartwater)'),
		(null, 1, 1, 70, 'Foot-and-mouth disease virus'),
		(null, 1, 1, 70, 'Goat pox virus'),
		(null, 1, 1, 70, 'Japanese encephalitis virus'),
		(null, 1, 1, 70, 'Lumpy skin disease virus'),
		(null, 1, 1, 70, 'Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)'),
		(null, 1, 1, 70, 'Menangle virus'),
		(null, 1, 1, 70, 'Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)'),
		(null, 1, 1, 70, 'Mycoplasma mycokey_ides subspecies mycokey_ides small colony (Mmm SC) (contagious bovine pleuropneumonia)'),
		(null, 1, 1, 70, 'Peste des petits ruminants virus'),
		(null, 1, 1, 70, 'Rinderpest virus'),
		(null, 1, 1, 70, 'Sheep pox virus'),
		(null, 1, 1, 70, 'Swine vesicular disease virus'),
		(null, 1, 1, 70, 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3'),
		(null, 1, 1, 70, 'Virulent Newcastle disease virus 1'),
		(null, 1, 1, 16, 'USDA PPQ SELECT AGENTS AND TOXINS'),	-- 94
		(null, 1, 1, 94, 'Peronosclerospora philippinensis (Peronosclerospora sacchari)'),
		(null, 1, 1, 94, 'Phoma glycinicola (formerly Pyrenochaeta glycines)'),
		(null, 1, 1, 94, 'Ralstonia solanacearum race 3, biovar 2'),
		(null, 1, 1, 94, 'Rathayibacter toxicus'),
		(null, 1, 1, 94, 'Sclerophthora rayssiae var zeae'),
		(null, 1, 1, 94, 'Synchytrium endobioticum'),
		(null, 1, 1, 94, 'Xanthomonas oryzae'),
		(null, 1, 1, 94, 'Xylella fastkey_idiosa (citrus variegated chlorosis strain)'),
		(null, 1, 1, 1, 'Human-derived Materials'),	-- 103
		(null, 1, 1, 103, 'Blood'),
		(null, 1, 1, 103, 'Flukey_ids'),
		(null, 1, 1, 103, 'Cells'),
		(null, 1, 1, 103, 'Cell line'),
		(null, 1, 1, 103, 'Other tissue'),
		(null, 1, 1, 1, 'Biosafety Level 1 (BSL-1)'),
		(null, 1, 1, 1, 'Biosafety Level 2 (BSL-2)'),
		(null, 1, 1, 1, 'Biosafety Level 2+ (BSL-2+)'),
		(null, 1, 1, 1, 'Biosafety Level 3 (BSL-3)'),
		(null, 1, 1, 1, 'Animal Biosafety Level 1 (ABSL-1)'),
		(null, 1, 1, 1, 'Animal Biosafety Level 2 (ABSL-2)'),
		(null, 1, 1, 1, 'Animal Biosafety Level 2+ (ABSL-2+)'),
		(null, 1, 1, 1, 'Animal Biosafety Level 3 (ABSL-3)'),
		(null, 1, 1, 1, 'Biosafety Level 1 - Plants (BL1-P)'),
		(null, 1, 1, 1, 'Biosafety Level 2 - Plants (BL2-P)'),
		(null, 1, 1, 1, 'Biosafety Level 3 - Plants (BL3-P)')
;
