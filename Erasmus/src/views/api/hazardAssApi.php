<?php
header('content-type: application/javascript');

if(isset($_GET['update']) && $_GET['update'] === 'true'){
	// /echo 'test';
	//echo json_encode($_POST);
	$data = file_get_contents("php://input");
	//print_r($data);
	http_response_code(201);
}


echo $_GET["callback"]; 

if (isset($_GET['hazards'])){?>
([
{Name: 'Biological Materials', key_id:1,parent_id:100000,
SubHazards: [
	{Name: 'Biosafety Levels', key_id: 55555,
		SubHazards: [			
		{Name: 'Biosafety Level 1 (BSL-1)', key_id: 109,parent_id: 55555},				
		{Name: 'Biosafety Level 2 (BSL-2)', key_id: 110,parent_id: 55555},				
		{Name: 'Biosafety Level 2+ (BSL-2+)', key_id: 111,parent_id: 55555},				
		{Name: 'Biosafety Level 3 (BSL-3)', key_id: 112,parent_id: 55555},				
		{Name: 'Animal Biosafety Level 1 (ABSL-1)', key_id: 113,parent_id: 55555},				
		{Name: 'Animal Biosafety Level 2 (ABSL-2)', key_id: 114,parent_id: 55555},				
		{Name: 'Animal Biosafety Level 2+ (ABSL-2+)', key_id: 115,parent_id: 55555},				
		{Name: 'Animal Biosafety Level 3 (ABSL-3)', key_id: 116,parent_id: 55555},				
		{Name: 'Biosafety Level 1 - Plants (BL1-P)', key_id: 117,parent_id: 55555},				
		{Name: 'Biosafety Level 2 - Plants (BL2-P)', key_id: 118,parent_id: 55555},				
		{Name: 'Biosafety Level 3 - Plants (BL3-P)', key_id: 119,parent_id: 55555}
		]
	},
	{Name: 'Recombinant DNA', key_id:2,serialRequired: 1,serialNumber:"1k2h493233",parent_id: 1, 
		SubHazards: [				
			{Name: 'Viral Vectors',parent_id: 2, key_id:3,serialRequired: 1,serialNumber:"3fk2h493233", 
			SubHazards: [		
				{Name: 'Adeno-associated Virus (AAV)', hasChecklist: '1',  key_id:4,parent_id: 3},
				{Name: 'Adenovirus', key_id:5, hasChecklist: '1',parent_id: 3, SubHazards: []},
				{Name: 'Baculovirus', key_id: 120,parent_id: 3},
				{Name: 'Epstein-Barr Virus (EBV)', key_id: 6,parent_id: 3},
				{Name: 'Herpes Simplex Virus (HSV)', key_id: 7,parent_id: 3},
				{Name: 'Poxvirus / Vaccinia', key_id: 8,parent_id: 3},
				{Name: 'Retrovirus / Lentivirus (EIAV)', key_id: 9,parent_id: 3},
				{Name: 'Retrovirus / Lentivirus (FIV)',key_id: 10,parent_id: 3},
				{Name: 'Retrovirus / Lentivirus (HIV)',key_id: 11,parent_id: 3},
				{Name: 'Retrovirus / Lentivirus (SIV)',key_id: 12,parent_id: 3},
				{Name: 'Retrovirus / MMLV (Amphotropic or Pseudotyped)',key_id: 13,parent_id: 3},
				{Name: 'Retrovirus / MMLV (Ecotropic)',key_id: 14,parent_id: 3}
				]}		
			]
		},				
	{Name: 'Select AGENTS and Toxins',key_id: 15,parent_id: 1,
		SubHazards: [				
				{Name: 'HHS Select Agents and Toxins',key_id: 16, parent_id: 15,
					SubHazards: [		
						{Name: 'Abrin',key_id: 17,parent_id: 16},
						{Name: 'Botulinum neurotoxins', key_id: 18,parent_id: 16},
						{Name: 'Botulinum neurotoxin producing species of Clostrkey_idium', key_id: 19,parent_id: 16},
						{Name: 'Cercopithecine herpesvirus 1 (Herpes B virus)', key_id: 20,parent_id: 16},
						{Name: 'Clostrkey_idium perfringens epsilon toxin', key_id: 21,parent_id: 16},
						{Name: 'Cocckey_idiokey_ides posadasii/Cocckey_idiokey_ides immitis', key_id: 22,parent_id: 16},
						{Name: 'Conotoxins', key_id: 23,parent_id: 16},
						{Name: 'Coxiella burnetii', key_id: 24,parent_id: 16},
						{Name: 'Crimean-Congo haemorrhagic fever virus', key_id: 25,parent_id: 16},
						{Name: 'Diacetoxyscirpenol', key_id: 26,parent_id: 16},
						{Name: 'Eastern Equine Encephalitis virus', key_id: 27,parent_id: 16},
						{Name: 'Ebola virus', key_id: 28,parent_id: 16},
						{Name: 'Francisella tularensis', key_id: 29,parent_id: 16},
						{Name: 'Lassa fever virus', key_id: 30,parent_id: 16},
						{Name: 'Marburg virus', key_id: 31,parent_id: 16},
						{Name: 'Monkeypox virus', key_id: 32,parent_id: 16},
						{Name: 'Reconstructed 1918 Influenza virus', key_id: 33,parent_id: 16},
						{Name: 'Ricin', key_id: 34,parent_id: 16},
						{Name: 'Rickettsia prowazekii', key_id: 35,parent_id: 16},
						{Name: 'Rickettsia rickettsii', key_id: 36,parent_id: 16},
						{Name: 'Saxitoxin', key_id: 37,parent_id: 16},
						{Name: 'Shiga-like ribosome inactivating proteins', key_id: 38,parent_id: 16},
						{Name: 'Shigatoxin', key_id: 39,parent_id: 16},
						{Name: 'South American Haemorrhagic Fever viruses', key_id: 40,parent_id: 16,
							SubHazards: [
								{Name: 'Flexal', key_id: 41,parent_id: 40},
								{Name: 'Guanarito', key_id: 42,parent_id: 40},
								{Name: 'Junin', key_id: 43,parent_id: 40},
								{Name: 'Machupo', key_id: 44,parent_id: 40},
								{Name: 'Sabia', key_id: 45,parent_id: 40}			
							]
						},
						{Name: 'Staphylococcal enterotoxins', key_id: 46,parent_id: 16},
						{Name: 'T-2 toxin', key_id: 47,parent_id: 16},
						{Name: 'Tetrodotoxin', key_id: 48,parent_id: 16},
						{Name: 'Tick-borne encephalitis complex (flavi) viruses', key_id: 49,parent_id: 16,
							SubHazards: [
								{Name: 'Central European Tick-borne encephalitis', key_id: 50,parent_id: 49},
								{Name: 'Far Eastern Tick-borne encephalitis', key_id: 51,parent_id: 49},
								{Name: 'Kyasanur Forest disease', key_id: 52,parent_id: 49},
								{Name: 'Omsk Hemorrhagic Fever', key_id: 53,parent_id: 49}			,
								{Name: 'Russian Spring and Summer encephalitis', key_id: 54}			
							]
						},
						{Name: 'Variola major virus (Smallpox virus)', key_id: 55,parent_id: 16},
						{Name: 'Variola minor virus (Alastrim)', key_id: 56,parent_id: 16},
						{Name: 'Yersinia pestis', key_id: 57,parent_id: 16}
					]
				},		
				{Name: 'OVERLAP SELECT AGENTS AND TOXINS', key_id: 58,parent_id: 15,
					SubHazards: [		
						{Name: 'Bacillus anthracis', key_id: 59,parent_id: 58},
						{Name: 'Brucella abortus', key_id: 60,parent_id: 58},
						{Name: 'Brucella melitensis', key_id: 61,parent_id: 58},
						{Name: 'Brucella suis', key_id: 62,parent_id: 58},
						{Name: 'Burkholderia mallei (formerly Pseudomonas mallei)', key_id: 63,parent_id: 58},
						{Name: 'Burkholderia pseudomallei', key_id: 64,parent_id: 58},
						{Name: 'Hendra virus', key_id: 65,parent_id: 58},
						{Name: 'Nipah virus', key_id: 66,parent_id: 58},
						{Name: 'Rift Valley fever virus', key_id: 67,parent_id: 58},
						{Name: 'Venezuelan Equine Encephalitis virus', key_id: 68,parent_id: 58}
					]},		
				{Name: 'USDA VETERINARY SERVICES (VS) SELECT AGENTS', key_id: 69,parent_id: 15,
					SubHazards: [		
						{Name: 'African horse sickness virus', key_id: 70,parent_id: 69},
						{Name: 'African swine fever virus', key_id: 71,parent_id: 69},
						{Name: 'Akabane virus', key_id: 72,parent_id: 69},
						{Name: 'Avian influenza virus (highly pathogenic)', key_id: 73,parent_id: 69},
						{Name: 'Bluetongue virus (exotic)', key_id: 74,parent_id: 69},
						{Name: 'Bovine spongiform encephalopathy agent', key_id: 75,parent_id: 69},
						{Name: 'Camel pox virus', key_id: 76,parent_id: 69},
						{Name: 'Classical swine fever virus' , key_id: 77,parent_id: 69},
						{Name: 'Ehrlichia ruminantium (Heartwater)', key_id: 78,parent_id: 69},
						{Name: 'Foot-and-mouth disease virus', key_id: 79,parent_id: 69},
						{Name: 'Goat pox virus', key_id: 80,parent_id: 69},
						{Name: 'Japanese encephalitis virus', key_id: 81,parent_id: 69},
						{Name: 'Lumpy skin disease virus', key_id: 82,parent_id: 69},
						{Name: 'Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)', key_id: 83,parent_id: 69},
						{Name: 'Menangle virus', key_id: 84,parent_id: 69},
						{Name: 'Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)', key_id: 85,parent_id: 69},
						{Name: 'Mycoplasma mycokey_ides subspecies mycokey_ides small colony (Mmm SC) (contagious bovine pleuropneumonia)', key_id: 86,parent_id: 69},
						{Name: 'Peste des petits ruminants virus', key_id: 87,parent_id: 69},
						{Name: 'Rinderpest virus', key_id: 88,parent_id: 69},
						{Name: 'Sheep pox virus', key_id: 89,parent_id: 69},
						{Name: 'Swine vesicular disease virus', key_id: 90,parent_id: 69},
						{Name: 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3', key_id: 91,parent_id: 69},
						{Name: 'Virulent Newcastle disease virus 1', key_id: 92,parent_id: 69}
					]},		
				{Name: 'USDA PPQ SELECT AGENTS AND TOXINS', key_id: 93,parent_id: 15,
					SubHazards: [		
						{Name: 'Peronosclerospora philippinensis (Peronosclerospora sacchari)', key_id: 95,parent_id: 93},
						{Name: 'Phoma glycinicola (formerly Pyrenochaeta glycines)', key_id: 96,parent_id: 93},
						{Name: 'Ralstonia solanacearum race 3, biovar 2', key_id: 97,parent_id: 93},
						{Name: 'Rathayibacter toxicus', key_id: 98,parent_id: 93},
						{Name: 'Sclerophthora rayssiae var zeae', key_id: 99,parent_id: 93},
						{Name: 'Synchytrium endobioticum', key_id: 100,parent_id: 93},
						{Name: 'Xanthomonas oryzae', key_id: 101,parent_id: 93},
						{Name: 'Xylella fastkey_idiosa (citrus variegated chlorosis strain)', key_id: 102,parent_id: 93}
					]}		
		]},				
		{Name: 'Human-derived Materials', key_id: 103,parent_id: 1,
			SubHazards: [				
					{Name: 'Blood', key_id: 104,parent_id: 103},		
					{Name: 'Flukey_ids', key_id: 105,parent_id: 103},		
					{Name: 'Cells', key_id: 106,parent_id: 103},		
					{Name: 'Cell line', key_id: 107,parent_id: 103},		
					{Name: 'Other tissue', key_id: 108,parent_id: 103}		
			]},					
	]},

{Name: 'General Laboratory Safety', key_id:1,parent_id:1000500,
SubHazards: [
				{Name: 'DEA Controlled Substances', key_id: 19,parent_id: 16},
				{Name: 'Particularly Hazardous Substances ',key_id: 17,parent_id: 16},
				{Name: 'Nanomaterials or Nanoparticles', key_id: 18,parent_id: 16},
				{Name: 'Compressed Gas Tanks', key_id: 20,parent_id: 16},
				{Name: 'Chemical Fume Hoods', key_id: 21,parent_id: 16},
				{Name: 'Special Use Chemical Hazards', key_id: 22,parent_id: 16},
				{Name: 'Other Laboratory Hazards', key_id: 23,parent_id: 16}
	]},
	{Name: 'Radiation Safety', key_id:1,parent_id:1000500,
SubHazards: [
				{Name: 'Radioisotopes', key_id: 19,parent_id: 16},
				{Name: 'X-Ray Machines',key_id: 17,parent_id: 16},
				{Name: 'Lasers', key_id: 18,parent_id: 16}
	]}
					
	
	]);
<?php } ?>

<?php if (isset($_GET['rooms'])){?>
	([{room: '101', key_id: 1, hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109},{label: 'Recombinant DNA', key_id: 2},{label: 'Biological Materials', key_id:1,}]},
		{room: '104', key_id: 2, hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109},{label: 'Japanese encephalitis virus', key_id: 81,parent_id: 69}]},
		{room: '105', key_id: 3, hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109},{label: 'Shigatoxin', key_id: 39}]},
		{room: '107', key_id: 4, hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109},{label: 'Goat pox virus', key_id: 80}]}])
<?php }?>

<?php if (isset($_GET['checklists'])){?>
	({ "Checklists" : [ { "key_id" : 200,
        "label" : "STANDARD MICROBIOLOGICAL PRACTICES",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Lab supervisor is not controlling access to the laboratory" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 300,
              "Observations" : [ { "key_id" : 224,
                    "Text" : "Test note"
                  },
                  { "key_id" : 229,
                    "Text" : "Test note"
                  }
                ],
              "orderIndex" : 1,
              "Recommendations" : [ { "key_id" : 224,
                    "Text" : "Test recommendation"
                  },
                  { "key_id" : 2454,
                    "Text" : "Test recommendation"
                  }
                ],
              "StandardsAndGuidelines" : "Biosafety in Microbiological & Biomedical Labs, 5th Ed.",
              "Text" : "Lab supervisor enforces policies that control access to the laboratory"
            },
            { "Deficiencies" : [ { "Text" : "Lab personnel are not washing their hands after working with samples" },
                  { "Text" : "Lab personnel are not washing their hands before leaving the lab" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 301,
              "orderIndex" : 2,
              "Recommendations" : [ { "Text" : "Test Recommendation" } ],
              "StandardsAndGuidelines" : "Biosafety in Microbiological & Biomedical Labs, 5th Ed.",
              "Text" : "Persons wash their hands after working with hazardous materials and before leaving the lab"
            },
            { "Deficiencies" : [ { "Text" : "Lab personnel are eating in lab areas" },
                  { "Text" : "Lab personnel are drinking in lab areas" },
                  { "Text" : "Lab personnel are storing food for human consumption in lab areas" }
                ],
              "DeficiencyRootCauses" : [ { "Text" : "Test Root Cause" } ],
              "isMandatory" : true,
              "key_id" : 302,
              "Observations" : [ { "key_id" : 224,
                    "Text" : "Test note"
                  },
                  { "key_id" : 229,
                    "Text" : "Test note"
                  }
                ],
              "orderIndex" : 3,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "Biosafety in Microbiological & Biomedical Labs, 5th Ed.",
              "Text" : "Eating, drinking, and storing food for consumption are not permitted in lab areas"
            }
          ],
        "rooms" : [ "101",
            "102",
            "103"
          ]
      },
      { "key_id" : 201,
        "label" : "SHIPPING BIOLOGICAL MATERIALS",
        "Questions" : [ { "Deficiencies" : [ { "key_id" : 222,
                    "Text" : "Personnel shipping biological samples have not completed biological shipping training"
                  },
                  { "key_id" : 223,
                    "Text" : "Personnel shipping biological samples are overdue for completing biological shipping training"
                  }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 310,
              "Observations" : [ { "key_id" : 224,
                    "Text" : "Test note"
                  },
                  { "key_id" : 229,
                    "Text" : "Test note"
                  }
                ],
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "International Air Transport Association (IATA) & DOT",
              "Text" : "Personnel shipping biological samples have completed biological shipping training in the past two years"
            } ],
        "rooms" : [ "101",
            "102"
          ]
      },
      { "key_id" : 202,
        "label" : "BLOODBORNE PATHOGENS (e.g. research involving human blood, body fluids, unfixed tissue)",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      },
      { "key_id" : 203,
        "label" : "Test Checklist 1",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      },
      { "key_id" : 204,
        "label" : "Test Checklist 2",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      },
      { "key_id" : 205,
        "label" : "Test Checklist 3",
        "Questions" : [ { "Deficiencies" : [ { "Text" : "Exposure Control Plan is not accessible to employees with occupational exposure" } ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],
              "isMandatory" : true,
              "key_id" : 320,
              "orderIndex" : 1,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens"
            },
            { "Deficiencies" : [ { "Text" : "Exposure Control Plan has not been reviewed and updated at least annually" },
                  { "Text" : "Updates do not reflect new or modified tasks and procedures which affect occupational exposure" },
                  { "Text" : "Updates do not reflect new or revised employee positions with occupational exposure" }
                ],
              "DeficiencyRootCauses" : [{'Text' : 'Sample Root Cause 1' },{'Text' : 'Sample Root Cause 2' },{'Text' : 'Sample Root Cause 3' }  ],

              "isMandatory" : true,
              "key_id" : 321,
              "orderIndex" : 2,
              "Recommendations" : [  ],
              "StandardsAndGuidelines" : "OSHA Bloodborne Pathogens (29 CFR 1910.1030)",
              "Text" : "Exposure Control Plan has been reviewed and updated at least annually"
            }
          ],
        "rooms" : [ "101",
            102,
            "103"
          ]
      }
    ],
  "PrincipalInvestigator" : { 
  			"Name" : "Doctor Pricnipio Inspecticus",
  			"KeyId"  : 1234,
  			"Contacts" : [
  				{"Name" : "Contact 1",
  				 "KeyId"  : 12344,
  				 "Phone"  : "12345679" 
  				},
  				{"Name" : "Contact 2",
  				 "KeyId"  : 12343434344,
  				 "Phone"  : "123456dfdf79" 
  				}
  			]
  		 }
})
<?php }
if (isset($_GET['users'])) {
echo $_GET["callback"]; ?> 
([
	{"id":1, "name": "User 1", email: "tfdsest@test.test", ldap: "bUserington"},
    {"id":2, "name": "User 2", email: "test@test.test", ldap: "bdUserington"},
    {"id":3,"name": "User 3", email: "tedfst@test.test", ldap: "bcUserington"},
    {"id":3,"name": "User 4", email: "tesdfadft@test.test", ldap: "baUserington"},
    {"id":3,"name": "User 5", email: "teadsfst@test.test", ldap: "bfdfUserington"}
])
<?php 
}

?>
