--
--  ERASMUS database sample data script
--
--  WARNING: Running this script will delete all data from database and replace
--  	with sample data.
--
--

USE usc_ehs_rsms;

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
TRUNCATE TABLE hazard_room;
TRUNCATE TABLE checklist;
TRUNCATE TABLE question;
TRUNCATE TABLE deficiency;
TRUNCATE TABLE deficiency_root_cause;
TRUNCATE TABLE recommendation;
TRUNCATE TABLE observation;
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
ALTER TABLE hazard_room AUTO_INCREMENT = 1;
ALTER TABLE checklist AUTO_INCREMENT = 1;
ALTER TABLE question AUTO_INCREMENT = 1;
ALTER TABLE deficiency AUTO_INCREMENT = 1;
ALTER TABLE deficiency_root_cause AUTO_INCREMENT = 1;
ALTER TABLE recommendation AUTO_INCREMENT = 1;
ALTER TABLE observation AUTO_INCREMENT = 1;
-- TODO: Add Tables

SET foreign_key_checks = 1;

-- Create users
INSERT INTO erasmus_user (
	date_created,
	last_modified_user_id,
	username,
	name,
	email
)
VALUES
	(null, 1, 'admin', 'Admin', 'mmartin+admin@graysail.com'),
	(null, 1, 'mmartin', 'Mitch Martin', 'mmartin@graysail.com'),
	(null, 1, 'pi1', 'PI Number 1', 'mmartin+pi1@graysail.com'),
	(null, 1, 'inpectorgadget', 'Inspector Gadget', 'mmartin+inspectorgadget@graysail.com')
;


-- Create Buildings
INSERT INTO building (
	date_created,
	name,
	last_modified_user_id
)
VALUES
	(null, 'Building 1', 1),
	(null, 'Building 2', 1),
	(null, 'Building 3', 1)
;

-- Create Rooms for building 1
INSERT INTO room (
	date_created,
	last_modified_user_id,
	name,
	building_id
)
VALUES
	(null,1, '101', 1),
	(null,1, '102', 1),
	(null,1, '103', 1)
;

-- Create Rooms for building 2
INSERT INTO room (
	date_created,
	last_modified_user_id,
	name,
	building_id
)
VALUES
	(null,1, '101', 2),
	(null,1, '102', 2),
	(null,1, '103', 2)
;

-- Create Rooms for building 3
INSERT INTO room (
	date_created,
	last_modified_user_id,
	name,
	building_id
)
VALUES
	(null, 1, '101', 3),
	(null, 1, '102', 3),
	(null, 1, '103', 3)
;

-- Create Departments
INSERT INTO department (
	date_created,
	last_modified_user_id,
	name
)
VALUES
	(null, 1, 'Department of Research Safety and Compliance')
;

-- Create PrincipalInvestigators
INSERT INTO principal_investigator (
	date_created,
	last_modified_user_id,
	user_id
)
VALUES
	(null, 1,3)
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
	last_modified_user_id,
	user_id
)
VALUES
	(null, 1, 4)
;

-- Create Hazards
INSERT INTO hazard (
	date_created,
	last_modified_user_id,
	parent_hazard_id,
	name,
	requires_serial_number
)
VALUES
	(null,1, null, 'Biological Materials', 0),
	(null,1, 1, 'Recombinant DNA', 1),
	(null,1, 2, 'Viral Vectors', 1),
	(null,1, 3, 'Adeno-associated Virus (AAV)', 0),
	(null,1, 3, 'Adenovirus', 0),
	(null,1, 3, 'Baculovirus', 0),
	(null,1, 3, 'Epstein-Barr Virus (EBV)', 0),
	(null,1, 3, 'Herpes Simplex Virus (HSV)', 0),
	(null,1, 3, 'Poxvirus / Vaccinia', 0),
	(null,1, 3, 'Retrovirus / Lentivirus (EIAV)', 0),
	(null,1, 3, 'Retrovirus / Lentivirus (FIV)', 0),
	(null,1, 3, 'Retrovirus / Lentivirus (HIV)', 0),
	(null,1, 3, 'Retrovirus / Lentivirus (SIV)', 0),
	(null,1, 3, 'Retrovirus / MMLV (Amphotropic or Pseudotyped)', 0),
	(null,1, 3, 'Retrovirus / MMLV (Ecotropic)', 0),
	(null,1, 1, 'Select AGENTS and Toxins', 0),	-- 16
	(null,1, 16, 'HHS Select Agents and Toxins', 0),	-- 17
	(null,1, 17, 'Abrin', 0),
	(null,1, 17, 'Botulinum neurotoxins', 0),
	(null, 1, 17, 'Botulinum neurotoxin producing species of Clostrkey_idium', 0),
	(null, 1, 17, 'Cercopithecine herpesvirus 1 (Herpes B virus)', 0),
	(null,1, 17, 'Clostrkey_idium perfringens epsilon toxin', 0),
	(null,1, 17, 'Cocckey_idiokey_ides posadasii/Cocckey_idiokey_ides immitis', 0),
	(null,1, 17, 'Conotoxins', 0),
	(null,1, 17, 'Coxiella burnetii', 0),
	(null,1, 17, 'Crimean-Congo haemorrhagic fever virus', 0),
	(null,1, 17, 'Diacetoxyscirpenol', 0),
	(null,1, 17, 'Eastern Equine Encephalitis virus', 0),
	(null,1, 17, 'Ebola virus', 0),
	(null,1, 17, 'Francisella tularensis', 0),
	(null,1, 17, 'Lassa fever virus', 0),
	(null, 1, 17, 'Marburg virus', 0),
	(null,1, 17, 'Monkeypox virus', 0),
	(null,1, 17, 'Reconstructed 1918 Influenza virus', 0),
	(null,1, 17, 'Ricin', 0),
	(null, 1, 17, 'Rickettsia prowazekii', 0),
	(null, 1, 17, 'Rickettsia rickettsii', 0),
	(null, 1, 17, 'Saxitoxin', 0),
	(null, 1, 17, 'Shiga-like ribosome inactivating proteins', 0),
	(null, 1, 17, 'Shigatoxin', 0),
	(null, 1, 17, 'South American Haemorrhagic Fever viruses', 0),	-- 41
	(null, 1, 41, 'Flexal', 0),
	(null, 1, 41, 'Guanarito', 0),
	(null, 1, 41, 'Junin', 0),
	(null, 1, 41, 'Machupo', 0),
	(null, 1, 41, 'Sabia', 0),
	(null, 1, 17, 'Staphylococcal enterotoxins', 0),
	(null, 1, 17, 'T-2 toxin', 0),
	(null, 1, 17, 'Tetrodotoxin', 0),
	(null, 1, 17, 'Tick-borne encephalitis complex (flavi) viruses', 0),	-- 45
	(null, 1, 45, 'Central European Tick-borne encephalitis', 0),
	(null, 1, 45, 'Far Eastern Tick-borne encephalitis', 0),
	(null, 1, 45, 'Kyasanur Forest disease', 0),
	(null, 1, 45, 'Omsk Hemorrhagic Fever', 0),
	(null, 1, 45, 'Russian Spring and Summer encephalitis', 0),
	(null, 1, 17, 'Variola major virus (Smallpox virus)', 0),
	(null, 1, 17, 'Variola minor virus (Alastrim)', 0),
	(null, 1, 17, 'Yersinia pestis', 0),
	(null, 1, 16, 'OVERLAP SELECT AGENTS AND TOXINS', 0),	-- 59
	(null, 1, 59, 'Bacillus anthracis', 0),
	(null, 1, 59, 'Brucella abortus', 0),
	(null, 1, 59, 'Brucella melitensis', 0),
	(null, 1, 59, 'Brucella suis', 0),
	(null, 1, 59, 'Burkholderia mallei (formerly Pseudomonas mallei)', 0),
	(null, 1, 59, 'Burkholderia pseudomallei', 0),
	(null, 1, 59, 'Hendra virus', 0),
	(null, 1, 59, 'Nipah virus', 0),
	(null, 1, 59, 'Rift Valley fever virus', 0),
	(null, 1, 59, 'Venezuelan Equine Encephalitis virus', 0),
	(null, 1, 16, 'USDA VETERINARY SERVICES (VS) SELECT AGENTS', 0),	-- 70
	(null, 1, 70, 'African horse sickness virus', 0),
	(null, 1, 70, 'African swine fever virus', 0),
	(null, 1, 70, 'Akabane virus', 0),
	(null, 1, 70, 'Avian influenza virus (highly pathogenic)', 0),
	(null, 1, 70, 'Bluetongue virus (exotic)', 0),
	(null, 1, 70, 'Bovine spongiform encephalopathy agent', 0),
	(null, 1, 70, 'Camel pox virus', 0),
	(null, 1, 70, 'Classical swine fever virus', 0),
	(null, 1, 70, 'Ehrlichia ruminantium (Heartwater)', 0),
	(null, 1, 70, 'Foot-and-mouth disease virus', 0),
	(null, 1, 70, 'Goat pox virus', 0),
	(null, 1, 70, 'Japanese encephalitis virus', 0),
	(null, 1, 70, 'Lumpy skin disease virus', 0),
	(null, 1, 70, 'Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)', 0),
	(null, 1, 70, 'Menangle virus', 0),
	(null, 1, 70, 'Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)', 0),
	(null, 1, 70, 'Mycoplasma mycokey_ides subspecies mycokey_ides small colony (Mmm SC) (contagious bovine pleuropneumonia)', 0),
	(null, 1, 70, 'Peste des petits ruminants virus', 0),
	(null, 1, 70, 'Rinderpest virus', 0),
	(null, 1, 70, 'Sheep pox virus', 0),
	(null, 1, 70, 'Swine vesicular disease virus', 0),
	(null, 1, 70, 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3', 0),
	(null, 1, 70, 'Virulent Newcastle disease virus 1', 0),
	(null, 1, 16, 'USDA PPQ SELECT AGENTS AND TOXINS', 0),	-- 94
	(null, 1, 94, 'Peronosclerospora philippinensis (Peronosclerospora sacchari)', 0),
	(null, 1, 94, 'Phoma glycinicola (formerly Pyrenochaeta glycines)', 0),
	(null, 1, 94, 'Ralstonia solanacearum race 3, biovar 2', 0),
	(null, 1, 94, 'Rathayibacter toxicus', 0),
	(null, 1, 94, 'Sclerophthora rayssiae var zeae', 0),
	(null, 1, 94, 'Synchytrium endobioticum', 0),
	(null, 1, 94, 'Xanthomonas oryzae', 0),
	(null, 1, 94, 'Xylella fastkey_idiosa (citrus variegated chlorosis strain)', 0),
	(null, 1, 1, 'Human-derived Materials', 0),	-- 103
	(null, 1, 103, 'Blood', 0),
	(null, 1, 103, 'Flukey_ids', 0),
	(null, 1, 103, 'Cells', 0),
	(null, 1, 103, 'Cell line', 0),
	(null, 1, 103, 'Other tissue', 0),
	(null, 1, 1, 'Biosafety Level 1 (BSL-1)', 0),
	(null, 1, 1, 'Biosafety Level 2 (BSL-2)', 0),
	(null, 1, 1, 'Biosafety Level 2+ (BSL-2+)', 0),
	(null, 1, 1, 'Biosafety Level 3 (BSL-3)', 0),
	(null, 1, 1, 'Animal Biosafety Level 1 (ABSL-1)', 0),
	(null, 1, 1, 'Animal Biosafety Level 2 (ABSL-2)', 0),
	(null, 1, 1, 'Animal Biosafety Level 2+ (ABSL-2+)', 0),
	(null, 1, 1, 'Animal Biosafety Level 3 (ABSL-3)', 0),
	(null, 1, 1, 'Biosafety Level 1 - Plants (BL1-P)', 0),
	(null, 1, 1, 'Biosafety Level 2 - Plants (BL2-P)', 0),
	(null, 1, 1, 'Biosafety Level 3 - Plants (BL3-P)', 0)
;

-- Create Equipment
INSERT INTO hazard_room (
	hazard_id,
	room_id,
	equipment_serial_number
)
VALUES
	(2, 1, '1k2h493233'),
	(4, 1, null)
;

-- Create Checklists

-- Create Questions
INSERT INTO question (
	date_created,
	last_modified_user_id,
	order_index,
	is_mandatory,
	checklist_id,
	text,
	standards_and_guidelines
)
VALUES
	(null, 1, 1, 1, null, 'Lab supervisor enforces policies that control access to the laboratory', 'Biosafety in Microbiological & Biomedical Labs, 5th Ed.'),
	(null, 1, 2, 1, null, 'Persons wash their hands after working with hazardous materials and before leaving the lab', 'Biosafety in Microbiological & Biomedical Labs, 5th Ed.'),
	(null, 1, 3, 1, null, 'Eating, drinking, and storing food for consumption are not permitted in lab areas', 'Biosafety in Microbiological & Biomedical Labs, 5th Ed.'),
	
	(null, 1, 1, 1, null, 'Personnel shipping biological samples have completed biological shipping training in the past two years', 'International Air Transport Association (IATA) & DOT'),
	
	(null, 1, 1, 1, null, 'Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens', 'OSHA Bloodborne Pathogens (29 CFR 1910.1030)'),
	(null, 1, 2, 1, null, 'Exposure Control Plan has been reviewed and updated at least annually', 'OSHA Bloodborne Pathogens (29 CFR 1910.1030)')
;

-- TODO: Create Deficiencies

-- TODO: Create Deficiency Root Causes

-- TODO: Create Recommendations

-- TODO: Create Observations