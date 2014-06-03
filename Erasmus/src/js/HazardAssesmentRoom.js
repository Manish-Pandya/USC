/*
var doRecursively = function(dataArray, methodsArray){
for (var i=0; i<children.length; i++){
	for(var i = 0; methodsArray.length < i; i+=;){
		methodsArray[i](dataArray[i]);
	}
}

*/
function HazardAssesmentRooms(){

	var hotParentkey_ids = [];
	//properties:

	//factory.getInspectionRooms returns a collection of the inspections rooms, each with its hazards populated as a property
	this.getRooms(){
		this.rooms = factory.getInsepctionRooms();
	};

	//factory.getHazards returns the hazard tree
	this.getHazards =function(){
		this.hazards = factory.getHazardTree();                                                                                                            //some more code over here
	};
/*

	var rooms = [{room: '101', hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109}]},
		{room: '103', hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109}]},
		{room: '105', hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109}]},
		{room: '107', hazards: [{label: 'Biosafety Level 1 (BSL-1)', key_id: 109},{label: 'Goat pox virus', key_id: 80}]}];


	var hazards = [
{label: 'Biological Materials', key_id:1,parent_id:100000,
children: [	
	{label: 'Recombinant DNA', key_id:2,serialRequired: 1,serialNumber:"1k2h493233",parent_id: 1, 

		children: [				
			{label: 'Viral Vectors',parent_id: 2, key_id:3,serialRequired: 1,serialNumber:"3fk2h493233", 
			children: [		
				{label: 'Adeno-associated Virus (AAV)', hasChecklist: '1',  key_id:4,parent_id: 3},
				{label: 'Adenovirus', key_id:5, hasChecklist: '1',parent_id: 3, children: []},
				{label: 'Baculovirus', key_id: 120,parent_id: 3},
				{label: 'Epstein-Barr Virus (EBV)', key_id: 6,parent_id: 3},
				{label: 'Herpes Simplex Virus (HSV)', key_id: 7,parent_id: 3},
				{label: 'Poxvirus / Vaccinia', key_id: 8,parent_id: 3},
				{label: 'Retrovirus / Lentivirus (EIAV)', key_id: 9,parent_id: 3},
				{label: 'Retrovirus / Lentivirus (FIV)',key_id: 10,parent_id: 3},
				{label: 'Retrovirus / Lentivirus (HIV)',key_id: 11,parent_id: 3},
				{label: 'Retrovirus / Lentivirus (SIV)',key_id: 12,parent_id: 3},
				{label: 'Retrovirus / MMLV (Amphotropic or Pseudotyped)',key_id: 13,parent_id: 3},
				{label: 'Retrovirus / MMLV (Ecotropic)',key_id: 14,parent_id: 3}
				]}		
			]
		},				
	{label: 'Select AGENTS and Toxins',key_id: 15,parent_id: 1,
		children: [				
				{label: 'HHS Select Agents and Toxins',key_id: 16, parent_id: 15,
					children: [		
						{label: 'Abrin',key_id: 17,parent_id: 16},
						{label: 'Botulinum neurotoxins', key_id: 18,parent_id: 16},
						{label: 'Botulinum neurotoxin producing species of Clostrkey_idium', key_id: 19,parent_id: 16},
						{label: 'Cercopithecine herpesvirus 1 (Herpes B virus)', key_id: 20,parent_id: 16},
						{label: 'Clostrkey_idium perfringens epsilon toxin', key_id: 21,parent_id: 16},
						{label: 'Cocckey_idiokey_ides posadasii/Cocckey_idiokey_ides immitis', key_id: 22,parent_id: 16},
						{label: 'Conotoxins', key_id: 23,parent_id: 16},
						{label: 'Coxiella burnetii', key_id: 24,parent_id: 16},
						{label: 'Crimean-Congo haemorrhagic fever virus', key_id: 25,parent_id: 16},
						{label: 'Diacetoxyscirpenol', key_id: 26,parent_id: 16},
						{label: 'Eastern Equine Encephalitis virus', key_id: 27,parent_id: 16},
						{label: 'Ebola virus', key_id: 28,parent_id: 16},
						{label: 'Francisella tularensis', key_id: 29,parent_id: 16},
						{label: 'Lassa fever virus', key_id: 30,parent_id: 16},
						{label: 'Marburg virus', key_id: 31,parent_id: 16},
						{label: 'Monkeypox virus', key_id: 32,parent_id: 16},
						{label: 'Reconstructed 1918 Influenza virus', key_id: 33,parent_id: 16},
						{label: 'Ricin', key_id: 34,parent_id: 16},
						{label: 'Rickettsia prowazekii', key_id: 35,parent_id: 16},
						{label: 'Rickettsia rickettsii', key_id: 36,parent_id: 16},
						{label: 'Saxitoxin', key_id: 37,parent_id: 16},
						{label: 'Shiga-like ribosome inactivating proteins', key_id: 38,parent_id: 16},
						{label: 'Shigatoxin', key_id: 39,parent_id: 16},
						{label: 'South American Haemorrhagic Fever viruses', key_id: 40,parent_id: 16,
							children: [
								{label: 'Flexal', key_id: 41,parent_id: 40},
								{label: 'Guanarito', key_id: 42,parent_id: 40},
								{label: 'Junin', key_id: 43,parent_id: 40},
								{label: 'Machupo', key_id: 44,parent_id: 40},
								{label: 'Sabia', key_id: 45,parent_id: 40}			
							]
						},
						{label: 'Staphylococcal enterotoxins', key_id: 46,parent_id: 16},
						{label: 'T-2 toxin', key_id: 47,parent_id: 16},
						{label: 'Tetrodotoxin', key_id: 48,parent_id: 16},
						{label: 'Tick-borne encephalitis complex (flavi) viruses', key_id: 49,parent_id: 16,
							children: [
								{label: 'Central European Tick-borne encephalitis', key_id: 50,parent_id: 49},
								{label: 'Far Eastern Tick-borne encephalitis', key_id: 51,parent_id: 49},
								{label: 'Kyasanur Forest disease', key_id: 52,parent_id: 49},
								{label: 'Omsk Hemorrhagic Fever', key_id: 53,parent_id: 49}			,
								{label: 'Russian Spring and Summer encephalitis', key_id: 54}			
							]
						},
						{label: 'Variola major virus (Smallpox virus)', key_id: 55,parent_id: 16},
						{label: 'Variola minor virus (Alastrim)', key_id: 56,parent_id: 16},
						{label: 'Yersinia pestis', key_id: 57,parent_id: 16}
					]
				},		
				{label: 'OVERLAP SELECT AGENTS AND TOXINS', key_id: 58,parent_id: 15,
					children: [		
						{label: 'Bacillus anthracis', key_id: 59,parent_id: 58},
						{label: 'Brucella abortus', key_id: 60,parent_id: 58},
						{label: 'Brucella melitensis', key_id: 61,parent_id: 58},
						{label: 'Brucella suis', key_id: 62,parent_id: 58},
						{label: 'Burkholderia mallei (formerly Pseudomonas mallei)', key_id: 63,parent_id: 58},
						{label: 'Burkholderia pseudomallei', key_id: 64,parent_id: 58},
						{label: 'Hendra virus', key_id: 65,parent_id: 58},
						{label: 'Nipah virus', key_id: 66,parent_id: 58},
						{label: 'Rift Valley fever virus', key_id: 67,parent_id: 58},
						{label: 'Venezuelan Equine Encephalitis virus', key_id: 68,parent_id: 58}
					]},		
				{label: 'USDA VETERINARY SERVICES (VS) SELECT AGENTS', key_id: 69,parent_id: 15,
					children: [		
						{label: 'African horse sickness virus', key_id: 70,parent_id: 69},
						{label: 'African swine fever virus', key_id: 71,parent_id: 69},
						{label: 'Akabane virus', key_id: 72,parent_id: 69},
						{label: 'Avian influenza virus (highly pathogenic)', key_id: 73,parent_id: 69},
						{label: 'Bluetongue virus (exotic)', key_id: 74,parent_id: 69},
						{label: 'Bovine spongiform encephalopathy agent', key_id: 75,parent_id: 69},
						{label: 'Camel pox virus', key_id: 76,parent_id: 69},
						{label: 'Classical swine fever virus' , key_id: 77,parent_id: 69},
						{label: 'Ehrlichia ruminantium (Heartwater)', key_id: 78,parent_id: 69},
						{label: 'Foot-and-mouth disease virus', key_id: 79,parent_id: 69},
						{label: 'Goat pox virus', key_id: 80,parent_id: 69},
						{label: 'Japanese encephalitis virus', key_id: 81,parent_id: 69},
						{label: 'Lumpy skin disease virus', key_id: 82,parent_id: 69},
						{label: 'Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)', key_id: 83,parent_id: 69},
						{label: 'Menangle virus', key_id: 84,parent_id: 69},
						{label: 'Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)', key_id: 85,parent_id: 69},
						{label: 'Mycoplasma mycokey_ides subspecies mycokey_ides small colony (Mmm SC) (contagious bovine pleuropneumonia)', key_id: 86,parent_id: 69},
						{label: 'Peste des petits ruminants virus', key_id: 87,parent_id: 69},
						{label: 'Rinderpest virus', key_id: 88,parent_id: 69},
						{label: 'Sheep pox virus', key_id: 89,parent_id: 69},
						{label: 'Swine vesicular disease virus', key_id: 90,parent_id: 69},
						{label: 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3', key_id: 91,parent_id: 69},
						{label: 'Virulent Newcastle disease virus 1', key_id: 92,parent_id: 69}
					]},		
				{label: 'USDA PPQ SELECT AGENTS AND TOXINS', key_id: 93,parent_id: 15,
					children: [		
						{label: 'Peronosclerospora philippinensis (Peronosclerospora sacchari)', key_id: 95,parent_id: 93},
						{label: 'Phoma glycinicola (formerly Pyrenochaeta glycines)', key_id: 96,parent_id: 93},
						{label: 'Ralstonia solanacearum race 3, biovar 2', key_id: 97,parent_id: 93},
						{label: 'Rathayibacter toxicus', key_id: 98,parent_id: 93},
						{label: 'Sclerophthora rayssiae var zeae', key_id: 99,parent_id: 93},
						{label: 'Synchytrium endobioticum', key_id: 100,parent_id: 93},
						{label: 'Xanthomonas oryzae', key_id: 101,parent_id: 93},
						{label: 'Xylella fastkey_idiosa (citrus variegated chlorosis strain)', key_id: 102,parent_id: 93}
					]}		
		]},				
		{label: 'Human-derived Materials', key_id: 103,parent_id: 1,
			children: [				
					{label: 'Blood', key_id: 104,parent_id: 103},		
					{label: 'Flukey_ids', key_id: 105,parent_id: 103},		
					{label: 'Cells', key_id: 106,parent_id: 103},		
					{label: 'Cell line', key_id: 107,parent_id: 103},		
					{label: 'Other tissue', key_id: 108,parent_id: 103}		
			]},				
		{label: 'Biosafety Level 1 (BSL-1)', key_id: 109,parent_id: 1},				
		{label: 'Biosafety Level 2 (BSL-2)', key_id: 110,parent_id: 1},				
		{label: 'Biosafety Level 2+ (BSL-2+)', key_id: 111,parent_id: 1},				
		{label: 'Biosafety Level 3 (BSL-3)', key_id: 112,parent_id: 1},				
		{label: 'Animal Biosafety Level 1 (ABSL-1)', key_id: 113,parent_id: 1},				
		{label: 'Animal Biosafety Level 2 (ABSL-2)', key_id: 114,parent_id: 1},				
		{label: 'Animal Biosafety Level 2+ (ABSL-2+)', key_id: 115,parent_id: 1},				
		{label: 'Animal Biosafety Level 3 (ABSL-3)', key_id: 116,parent_id: 1},				
		{label: 'Biosafety Level 1 - Plants (BL1-P)', key_id: 117,parent_id: 1},				
		{label: 'Biosafety Level 2 - Plants (BL2-P)', key_id: 118,parent_id: 1},				
		{label: 'Biosafety Level 3 - Plants (BL3-P)', key_id: 119,parent_id: 1}				
	]}];
*/
	//object literal to hold final, fully populated list of hazards, including the rooms each one is present in
	inspectionHazards = {};

	setParentkey_ids = function(parent){
		if (parent.hasOwnProperty('children') && parent.children.length > 0){
			for(var i = 0; parent.children.length > i; i++){
				parent.children[i].parentkey_id = parent.key_id;
				if (parent.children[i].hasOwnProperty('children') && parent.children[i].children.length > 0){
					setParentkey_ids(parent.children[i]);
				}
			}
		}
		//console.log(parent);
	};
	iterator = 0;
	recursivelySearchChildren = function(hazard){
		iterator++;
		if (hazard.hasOwnProperty('children') && hazard.children.length > 0){
	
			hazard.isLeaf = false;
			for(var i = 0; hazard.children.length > i; i++){
				hazard.isLeaf = false;
				childHazard = hazard.children[i];
				recursivelySearchChildren(childHazard);
			}

		}else{
			hazard.isLeaf = true;
			hazardkey_id = hazard.key_id;
			hazard.rooms = [];
		
				for(var roomCount=0; roomCount<rooms.length; roomCount++){

					hazard.rooms.room = {};
					room = rooms[roomCount];
					// Does a given array contain a item
					function roomContainsHazard(array, obj) {

					    var i = array.length;
					    while (i--) {
					        if (array[i].key_id === obj.key_id) {
					            return true;
					        }
					    }
					    return false;
					};
					
					hazard.rooms.room.key_id = room.key_id;
					hazard.rooms.room.roomNumber = room.room;
				}
				if(roomContainsHazard(room.hazards, hazard)){
					hazard.isPresent = true;
					hotParentkey_ids.push(hazard.parentkey_id);
				}else{
					hazard.isPresent = false;
					hazard.rooms.room.hasThisHazard = false;					
				}
			}

			if(hotParentkey_ids.indexOf(hazard.key_id) > -1){
				hazard.containsHotChildren = true;
				hotParentkey_ids.push(hazard.parent_id);				

			}else{
				hazard.containsHotChildren = false;			
			};

		}


	finalizeTree = function(hazards, rooms){
		//console.log(hazards);
		//array to hold fully populated hazard tree
		finalHazards = [];

		//loop through the hazards
		for (var i=0;i<hazards.length;i++){

			parentHazard = hazards[i];
			setParentkey_ids(parentHazard);
			//create a holder object for each hazard
			//tempHazard is the top level of a given hazard tree, ie Biological Hazards
			var tempHazard = hazards[i];

			//an array containing all the children of a top level hazard
			var children = tempHazard.children;

			recursivelySearchChildren(parentHazard);
			console.log(parentHazard);

			
		}


		finalHazards.push(tempHazard);

		//console.log('hazards');
//		console.log(finalHazards);

		return finalHazards;
	}
	finalizeTree(hazards, rooms);
}


tree = new HazardAssesmentRooms();