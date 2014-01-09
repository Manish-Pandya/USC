<?php

function addHazardToParent($h, $hazards){
	$ph = $hazards[$h->getParentHazardId()];
	$subs = $ph->getSubHazards();
	$subs[] = $h;
	$ph->setSubHazards($subs);
}

function getStaticHazards(){
	$hazards = array();

	//Define all hazards & IDs; assign their indexes as their ids

	$h = new Hazard(); $h->setName("Biological Materials"); $h->setKeyId(1); $h->setSubHazards( array() ); $hazards[1] = $h;

	$h = new Hazard(); $h->setName("Biosafety Levels"); $h->setKeyId(55555); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Biosafety Level 1 (BSL-1)"); $h->setKeyId(109); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 2 (BSL-2)"); $h->setKeyId(110); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 2+ (BSL-2+)"); $h->setKeyId(111); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 3 (BSL-3)"); $h->setKeyId(112); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 1 (ABSL-1)"); $h->setKeyId(113); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 2 (ABSL-2)"); $h->setKeyId(114); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 2+ (ABSL-2+)"); $h->setKeyId(115); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Animal Biosafety Level 3 (ABSL-3)"); $h->setKeyId(116); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 1 - Plants (BL1-P)"); $h->setKeyId(117); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 2 - Plants (BL2-P)"); $h->setKeyId(118); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Biosafety Level 3 - Plants (BL3-P)"); $h->setKeyId(119); $h->setParentHazardId(55555); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Recombinant DNA"); $h->setKeyId(2); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Viral Vectors"); $h->setKeyId(3); $h->setParentHazardId(2); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Adeno-associated Virus (AAV)"); $h->setKeyId(4); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Adenovirus"); $h->setKeyId(5); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Baculovirus"); $h->setKeyId(120); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Epstein-Barr Virus (EBV)"); $h->setKeyId(6); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Herpes Simplex Virus (HSV)"); $h->setKeyId(7); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Poxvirus / Vaccinia"); $h->setKeyId(8); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (EIAV)"); $h->setKeyId(9); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (FIV)"); $h->setKeyId(10); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (HIV)"); $h->setKeyId(11); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / Lentivirus (SIV)"); $h->setKeyId(12); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / MMLV (Amphotropic or Pseudotyped)"); $h->setKeyId(13); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Retrovirus / MMLV (Ecotropic)"); $h->setKeyId(14); $h->setParentHazardId(3); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Select AGENTS and Toxins"); $h->setKeyId(15); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("HHS Select Agents and Toxins"); $h->setKeyId(16); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Abrin"); $h->setKeyId(17); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Botulinum neurotoxins"); $h->setKeyId(18); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Botulinum neurotoxin producing species of Clostrkey_idium"); $h->setKeyId(19); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cercopithecine herpesvirus 1 (Herpes B virus)"); $h->setKeyId(20); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Clostrkey_idium perfringens epsilon toxin"); $h->setKeyId(21); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cocckey_idiokey_ides posadasii/Cocckey_idiokey_ides immitis"); $h->setKeyId(22); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Conotoxins"); $h->setKeyId(23); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Coxiella burnetii"); $h->setKeyId(24); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Crimean-Congo haemorrhagic fever virus"); $h->setKeyId(25); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Diacetoxyscirpenol"); $h->setKeyId(26); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Eastern Equine Encephalitis virus"); $h->setKeyId(27); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ebola virus"); $h->setKeyId(28); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Francisella tularensis"); $h->setKeyId(29); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Lassa fever virus"); $h->setKeyId(30); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Marburg virus"); $h->setKeyId(31); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Monkeypox virus"); $h->setKeyId(32); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Reconstructed 1918 Influenza virus"); $h->setKeyId(33); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ricin"); $h->setKeyId(34); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rickettsia prowazekii"); $h->setKeyId(35); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rickettsia rickettsii"); $h->setKeyId(36); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Saxitoxin"); $h->setKeyId(37); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Shiga-like ribosome inactivating proteins"); $h->setKeyId(38); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Shigatoxin"); $h->setKeyId(39); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("South American Haemorrhagic Fever viruses"); $h->setKeyId(40); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Flexal"); $h->setKeyId(41); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Guanarito"); $h->setKeyId(42); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Junin"); $h->setKeyId(43); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Machupo"); $h->setKeyId(44); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Sabia"); $h->setKeyId(45); $h->setParentHazardId(40); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Staphylococcal enterotoxins"); $h->setKeyId(46); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("T-2 toxin"); $h->setKeyId(47); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Tetrodotoxin"); $h->setKeyId(48); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Tick-borne encephalitis complex (flavi) viruses"); $h->setKeyId(49); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Central European Tick-borne encephalitis"); $h->setKeyId(50); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Far Eastern Tick-borne encephalitis"); $h->setKeyId(51); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Kyasanur Forest disease"); $h->setKeyId(52); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Omsk Hemorrhagic Fever"); $h->setKeyId(53); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Russian Spring and Summer encephalitis"); $h->setKeyId(54); $h->setParentHazardId(49); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Variola major virus (Smallpox virus)"); $h->setKeyId(55); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Variola minor virus (Alastrim)"); $h->setKeyId(56); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Yersinia pestis"); $h->setKeyId(57); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("OVERLAP SELECT AGENTS AND TOXINS"); $h->setKeyId(58); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Bacillus anthracis"); $h->setKeyId(59); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Brucella abortus"); $h->setKeyId(60); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Brucella melitensis"); $h->setKeyId(61); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Brucella suis"); $h->setKeyId(62); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Burkholderia mallei (formerly Pseudomonas mallei)"); $h->setKeyId(63); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Burkholderia pseudomallei"); $h->setKeyId(64); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Hendra virus"); $h->setKeyId(65); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Nipah virus"); $h->setKeyId(66); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rift Valley fever virus"); $h->setKeyId(67); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Venezuelan Equine Encephalitis virus"); $h->setKeyId(68); $h->setParentHazardId(58); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("USDA VETERINARY SERVICES (VS) SELECT AGENTS"); $h->setKeyId(69); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("African horse sickness virus"); $h->setKeyId(70); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("African swine fever virus"); $h->setKeyId(71); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Akabane virus"); $h->setKeyId(72); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Avian influenza virus (highly pathogenic)"); $h->setKeyId(73); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Bluetongue virus (exotic)"); $h->setKeyId(74); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Bovine spongiform encephalopathy agent"); $h->setKeyId(75); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Camel pox virus"); $h->setKeyId(76); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Classical swine fever virus"); $h->setKeyId(77); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ehrlichia ruminantium (Heartwater)"); $h->setKeyId(78); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Foot-and-mouth disease virus"); $h->setKeyId(79); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Goat pox virus"); $h->setKeyId(80); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Japanese encephalitis virus"); $h->setKeyId(81); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Lumpy skin disease virus"); $h->setKeyId(82); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)"); $h->setKeyId(83); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Menangle virus"); $h->setKeyId(84); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)"); $h->setKeyId(85); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Mycoplasma mycokey_ides subspecies mycokey_ides small colony (Mmm SC) (contagious bovine pleuropneumonia)"); $h->setKeyId(86); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Peste des petits ruminants virus"); $h->setKeyId(87); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rinderpest virus"); $h->setKeyId(88); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Sheep pox virus"); $h->setKeyId(89); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Swine vesicular disease virus"); $h->setKeyId(90); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3"); $h->setKeyId(91); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Virulent Newcastle disease virus 1"); $h->setKeyId(92); $h->setParentHazardId(69); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("USDA PPQ SELECT AGENTS AND TOXINS"); $h->setKeyId(93); $h->setParentHazardId(15); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Peronosclerospora philippinensis (Peronosclerospora sacchari)"); $h->setKeyId(95); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Phoma glycinicola (formerly Pyrenochaeta glycines)"); $h->setKeyId(96); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Ralstonia solanacearum race 3, biovar 2"); $h->setKeyId(97); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Rathayibacter toxicus"); $h->setKeyId(98); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Sclerophthora rayssiae var zeae"); $h->setKeyId(99); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Synchytrium endobioticum"); $h->setKeyId(100); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Xanthomonas oryzae"); $h->setKeyId(101); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Xylella fastkey_idiosa (citrus variegated chlorosis strain)"); $h->setKeyId(102); $h->setParentHazardId(93); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Human-derived Materials"); $h->setKeyId(103); $h->setParentHazardId(1); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Blood"); $h->setKeyId(104); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Flukey_ids"); $h->setKeyId(105); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cells"); $h->setKeyId(106); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Cell line"); $h->setKeyId(107); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Other tissue"); $h->setKeyId(108); $h->setParentHazardId(103); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("General Laboratory Safety"); $h->setKeyId(16); $h->setSubHazards( array() ); $hazards[16] = $h;

	$h = new Hazard(); $h->setName("DEA Controlled Substances"); $h->setKeyId(19); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Particularly Hazardous Substances "); $h->setKeyId(17); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Nanomaterials or Nanoparticles"); $h->setKeyId(18); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Compressed Gas Tanks"); $h->setKeyId(20); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Chemical Fume Hoods"); $h->setKeyId(21); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Special Use Chemical Hazards"); $h->setKeyId(22); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Other Laboratory Hazards"); $h->setKeyId(23); $h->setParentHazardId(16); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	$h = new Hazard(); $h->setName("Radiation Safety"); $h->setKeyId(1017); $h->setSubHazards( array() ); $hazards[1017] = $h;

	$h = new Hazard(); $h->setName("Radioisotopes"); $h->setKeyId(19); $h->setParentHazardId(1017); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("X-Ray Machines"); $h->setKeyId(17); $h->setParentHazardId(1017); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);
	$h = new Hazard(); $h->setName("Lasers"); $h->setKeyId(18); $h->setParentHazardId(1017); $h->setSubHazards( array() ); $hazards[$h->getKeyId()] = $h; addHazardToParent($h, $hazards);

	unset($h);
	unset($ph);

	//TODO: Remove dupes?
	foreach($hazards as $key=>$hazard){
		if( $hazard->getParentHazardId() !== NULL ){
			unset($hazards[$key]);
		}
	}
	
	return $hazards;
}

?>