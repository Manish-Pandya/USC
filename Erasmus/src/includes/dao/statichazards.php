<?php

function addHazardToParent($h, $hazards){
	$ph = $hazards[$h->getParentHazardId()];
	$subs = $ph->getSubHazards();
	$subs[] = $h;
	$ph->setSubHazards($subs);
}

function getStaticHazardsAsTree(){
	$hazards = array();

	//Define all hazards & IDs; assign their indexes as their ids

	$h = new Hazard(); $h->setName("Biological Materials"); $h->setKey_Id(1); $h->setSubHazards( array() ); $hazards[1] = $h;

	$h = new Hazard(); $h->setName("Biosafety Levels"); $h->setKey_Id(55555); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Biosafety Level 1 (BSL-1)"); $h->setKey_Id(109); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 2 (BSL-2)"); $h->setKey_Id(110); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 2+ (BSL-2+)"); $h->setKey_Id(111); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 3 (BSL-3)"); $h->setKey_Id(112); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 1 (ABSL-1)"); $h->setKey_Id(113); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 2 (ABSL-2)"); $h->setKey_Id(114); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 2+ (ABSL-2+)"); $h->setKey_Id(115); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 3 (ABSL-3)"); $h->setKey_Id(116); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 1 - Plants (BL1-P)"); $h->setKey_Id(117); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 2 - Plants (BL2-P)"); $h->setKey_Id(118); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 3 - Plants (BL3-P)"); $h->setKey_Id(119); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Recombinant DNA"); $h->setKey_Id(2); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Viral Vectors"); $h->setKey_Id(3); $h->setParentHazardId(2); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Adeno-associated Virus (AAV)"); $h->setKey_Id(4); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Adenovirus"); $h->setKey_Id(5); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Baculovirus"); $h->setKey_Id(120); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Epstein-Barr Virus (EBV)"); $h->setKey_Id(6); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Herpes Simplex Virus (HSV)"); $h->setKey_Id(7); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Poxvirus / Vaccinia"); $h->setKey_Id(8); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (EIAV)"); $h->setKey_Id(9); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (FIV)"); $h->setKey_Id(10); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (HIV)"); $h->setKey_Id(11); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (SIV)"); $h->setKey_Id(12); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / MMLV (Amphotropic or Pseudotyped)"); $h->setKey_Id(13); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / MMLV (Ecotropic)"); $h->setKey_Id(14); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Select AGENTS and Toxins"); $h->setKey_Id(15); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("HHS Select Agents and Toxins"); $h->setKey_Id(16); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Abrin"); $h->setKey_Id(17); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Botulinum neurotoxins"); $h->setKey_Id(18); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Botulinum neurotoxin producing species of Clostrkey_idium"); $h->setKey_Id(19); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cercopithecine herpesvirus 1 (Herpes B virus)"); $h->setKey_Id(20); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Clostrkey_idium perfringens epsilon toxin"); $h->setKey_Id(21); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cocckey_idiokey_ides posadasii/Cocckey_idiokey_ides immitis"); $h->setKey_Id(22); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Conotoxins"); $h->setKey_Id(23); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Coxiella burnetii"); $h->setKey_Id(24); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Crimean-Congo haemorrhagic fever virus"); $h->setKey_Id(25); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Diacetoxyscirpenol"); $h->setKey_Id(26); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Eastern Equine Encephalitis virus"); $h->setKey_Id(27); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ebola virus"); $h->setKey_Id(28); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Francisella tularensis"); $h->setKey_Id(29); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Lassa fever virus"); $h->setKey_Id(30); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Marburg virus"); $h->setKey_Id(31); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Monkeypox virus"); $h->setKey_Id(32); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Reconstructed 1918 Influenza virus"); $h->setKey_Id(33); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ricin"); $h->setKey_Id(34); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rickettsia prowazekii"); $h->setKey_Id(35); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rickettsia rickettsii"); $h->setKey_Id(36); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Saxitoxin"); $h->setKey_Id(37); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Shiga-like ribosome inactivating proteins"); $h->setKey_Id(38); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Shigatoxin"); $h->setKey_Id(39); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("South American Haemorrhagic Fever viruses"); $h->setKey_Id(40); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Flexal"); $h->setKey_Id(41); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Guanarito"); $h->setKey_Id(42); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Junin"); $h->setKey_Id(43); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Machupo"); $h->setKey_Id(44); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Sabia"); $h->setKey_Id(45); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Staphylococcal enterotoxins"); $h->setKey_Id(46); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("T-2 toxin"); $h->setKey_Id(47); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Tetrodotoxin"); $h->setKey_Id(48); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Tick-borne encephalitis complex (flavi) viruses"); $h->setKey_Id(49); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Central European Tick-borne encephalitis"); $h->setKey_Id(50); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Far Eastern Tick-borne encephalitis"); $h->setKey_Id(51); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Kyasanur Forest disease"); $h->setKey_Id(52); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Omsk Hemorrhagic Fever"); $h->setKey_Id(53); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Russian Spring and Summer encephalitis"); $h->setKey_Id(54); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Variola major virus (Smallpox virus)"); $h->setKey_Id(55); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Variola minor virus (Alastrim)"); $h->setKey_Id(56); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Yersinia pestis"); $h->setKey_Id(57); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("OVERLAP SELECT AGENTS AND TOXINS"); $h->setKey_Id(58); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Bacillus anthracis"); $h->setKey_Id(59); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Brucella abortus"); $h->setKey_Id(60); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Brucella melitensis"); $h->setKey_Id(61); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Brucella suis"); $h->setKey_Id(62); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Burkholderia mallei (formerly Pseudomonas mallei)"); $h->setKey_Id(63); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Burkholderia pseudomallei"); $h->setKey_Id(64); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Hendra virus"); $h->setKey_Id(65); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Nipah virus"); $h->setKey_Id(66); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rift Valley fever virus"); $h->setKey_Id(67); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Venezuelan Equine Encephalitis virus"); $h->setKey_Id(68); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("USDA VETERINARY SERVICES (VS) SELECT AGENTS"); $h->setKey_Id(69); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("African horse sickness virus"); $h->setKey_Id(70); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("African swine fever virus"); $h->setKey_Id(71); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Akabane virus"); $h->setKey_Id(72); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Avian influenza virus (highly pathogenic)"); $h->setKey_Id(73); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Bluetongue virus (exotic)"); $h->setKey_Id(74); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Bovine spongiform encephalopathy agent"); $h->setKey_Id(75); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Camel pox virus"); $h->setKey_Id(76); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Classical swine fever virus"); $h->setKey_Id(77); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ehrlichia ruminantium (Heartwater)"); $h->setKey_Id(78); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Foot-and-mouth disease virus"); $h->setKey_Id(79); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Goat pox virus"); $h->setKey_Id(80); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Japanese encephalitis virus"); $h->setKey_Id(81); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Lumpy skin disease virus"); $h->setKey_Id(82); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)"); $h->setKey_Id(83); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Menangle virus"); $h->setKey_Id(84); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)"); $h->setKey_Id(85); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Mycoplasma mycokey_ides subspecies mycokey_ides small colony (Mmm SC) (contagious bovine pleuropneumonia)"); $h->setKey_Id(86); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Peste des petits ruminants virus"); $h->setKey_Id(87); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rinderpest virus"); $h->setKey_Id(88); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Sheep pox virus"); $h->setKey_Id(89); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Swine vesicular disease virus"); $h->setKey_Id(90); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3"); $h->setKey_Id(91); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Virulent Newcastle disease virus 1"); $h->setKey_Id(92); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("USDA PPQ SELECT AGENTS AND TOXINS"); $h->setKey_Id(93); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Peronosclerospora philippinensis (Peronosclerospora sacchari)"); $h->setKey_Id(95); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Phoma glycinicola (formerly Pyrenochaeta glycines)"); $h->setKey_Id(96); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ralstonia solanacearum race 3, biovar 2"); $h->setKey_Id(97); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rathayibacter toxicus"); $h->setKey_Id(98); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Sclerophthora rayssiae var zeae"); $h->setKey_Id(99); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Synchytrium endobioticum"); $h->setKey_Id(100); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Xanthomonas oryzae"); $h->setKey_Id(101); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Xylella fastkey_idiosa (citrus variegated chlorosis strain)"); $h->setKey_Id(102); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Human-derived Materials"); $h->setKey_Id(103); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Blood"); $h->setKey_Id(104); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Flukey_ids"); $h->setKey_Id(105); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cells"); $h->setKey_Id(106); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cell line"); $h->setKey_Id(107); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Other tissue"); $h->setKey_Id(108); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("General Laboratory Safety"); $h->setKey_Id(16); $h->setSubHazards( array() ); $hazards[16] = $h;

	$h = new Hazard(); $h->setName("DEA Controlled Substances"); $h->setKey_Id(19); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Particularly Hazardous Substances "); $h->setKey_Id(17); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Nanomaterials or Nanoparticles"); $h->setKey_Id(18); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Compressed Gas Tanks"); $h->setKey_Id(20); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Chemical Fume Hoods"); $h->setKey_Id(21); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Special Use Chemical Hazards"); $h->setKey_Id(22); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Other Laboratory Hazards"); $h->setKey_Id(23); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Radiation Safety"); $h->setKey_Id(1017); $h->setSubHazards( array() ); $hazards[1017] = $h;

	$h = new Hazard(); $h->setName("Radioisotopes"); $h->setKey_Id(19); $h->setParentHazardId(1017); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("X-Ray Machines"); $h->setKey_Id(17); $h->setParentHazardId(1017); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Lasers"); $h->setKey_Id(18); $h->setParentHazardId(1017); $h->setSubHazards( array() ); $hazards[$h->getKey_Id()] = $h; addHazardToParent($h, $hazards);

	unset($h);
	unset($ph);

	// Remove dupes (only top-level hazards will remain; others are listed as sub-hazards)
	foreach($hazards as $key=>$hazard){
		if( $hazard->getParentHazardId() !== NULL ){
			unset($hazards[$key]);
		}
	}
	
	return $hazards;
}

?>