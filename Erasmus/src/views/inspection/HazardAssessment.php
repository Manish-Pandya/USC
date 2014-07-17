<?php
require_once '../top_view.php';
?>
<div class="navbar">
	<ul class="nav pageMenu" style="background: #e67e1d;">
		<li class="span4">
			<img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Hazard Assessment</h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg">
<form method="post" action="InspectionChecklist.php">
	<h2><?php echo $_POST['pi']?>'s Hazards</h2>
	<div id="tree1"></div>
	<a href="InspectionChecklist.php" class="btn btn-large btn-primary">Begin Inspection<i style="font-size:45px; margin:3px 16px 0 -9px" class="icon-arrow-right"></i></a>
</form>
</div>
<script>

//HAZARD TREE INIT CALLS

var data = [
{label: 'Biological Materials',children: [											
		{label: 'Recombinant DNA', id:'1',serialRequired:'1',serialNumber:"1k2h493233", rooms: [{room:'101'},{room:'103'}, {rooms:'106'}], children: [									
				{label: 'Viral Vectors', id:'2',serialRequired:'1',serialNumber:"3fk2h493233", rooms: [{room:'103'}], children: [							
						{label: 'Adeno-associated Virus (AAV)', hasChecklist: '1',  id:'2',children: []}					,
						{label: 'Adenovirus', id:'3', hasChecklist: '1', children: []}					,
						{label: 'Baculovirus',children: []}					,
						{label: 'Epstein-Barr Virus (EBV)',children: []}					,
						{label: 'Herpes Simplex Virus (HSV)',children: []}					,
						{label: 'Poxvirus / Vaccinia',children: []}					,
						{label: 'Retrovirus / Lentivirus (EIAV)',children: []}					,
						{label: 'Retrovirus / Lentivirus (FIV)',children: []}					,
						{label: 'Retrovirus / Lentivirus (HIV)',children: []}					,
						{label: 'Retrovirus / Lentivirus (SIV)',children: []}					,
						{label: 'Retrovirus / MMLV (Amphotropic or Pseudotyped)',children: []}					,
						{label: 'Retrovirus / MMLV (Ecotropic)', id:'4',rooms: [{room:'103'}],children: []}					
				]}		
			]					
		},									
		{label: 'Select Agents and Toxins',children: [									
				{label: 'HHS Select Agents and Toxins',children: [							
						{label: 'Abrin',children: []}					,
						{label: 'Botulinum neurotoxins',children: []}					,
						{label: 'Botulinum neurotoxin producing species of Clostridium',children: []}					,
						{label: 'Cercopithecine herpesvirus 1 (Herpes B virus)',children: []}					,
						{label: 'Clostridium perfringens epsilon toxin',children: []}					,
						{label: 'Coccidioides posadasii/Coccidioides immitis',children: []}					,
						{label: 'Conotoxins',children: []}					,
						{label: 'Coxiella burnetii',children: []}					,
						{label: 'Crimean-Congo haemorrhagic fever virus',children: []}					,
						{label: 'Diacetoxyscirpenol',children: []}					,
						{label: 'Eastern Equine Encephalitis virus',children: []}					,
						{label: 'Ebola virus',children: []}					,
						{label: 'Francisella tularensis',children: []}					,
						{label: 'Lassa fever virus',children: []}					,
						{label: 'Marburg virus',children: []}					,
						{label: 'Monkeypox virus',children: []}					,
						{label: 'Reconstructed 1918 Influenza virus',children: []}					,
						{label: 'Ricin',children: []}					,
						{label: 'Rickettsia prowazekii',children: []}					,
						{label: 'Rickettsia rickettsii',children: []}					,
						{label: 'Saxitoxin',children: []}					,
						{label: 'Shiga-like ribosome inactivating proteins',children: []}					,
						{label: 'Shigatoxin',children: []}					,
						{label: 'South American Haemorrhagic Fever viruses',children: [					
								{label: 'Flexal',children: []}			,
								{label: 'Guanarito',children: []}			,
								{label: 'Junin',children: []}			,
								{label: 'Machupo',children: []}			,
								{label: 'Sabia',children: []}			
						]},					
						{label: 'Staphylococcal enterotoxins',children: []}					,
						{label: 'T-2 toxin',children: []}					,
						{label: 'Tetrodotoxin',children: []}					,
						{label: 'Tick-borne encephalitis complex (flavi) viruses',children: [					
								{label: 'Central European Tick-borne encephalitis',children: []}			,
								{label: 'Far Eastern Tick-borne encephalitis',children: []}			,
								{label: 'Kyasanur Forest disease',children: []}			,
								{label: 'Omsk Hemorrhagic Fever',children: []}			,
								{label: 'Russian Spring and Summer encephalitis',children: []}			
						]},					
						{label: 'Variola major virus (Smallpox virus)',children: []}					,
						{label: 'Variola minor virus (Alastrim)',children: []}					,
						{label: 'Yersinia pestis',children: []}					
				]},							
				{label: 'OVERLAP SELECT AGENTS AND TOXINS',children: [							
						{label: 'Bacillus anthracis',children: []}					,
						{label: 'Brucella abortus',children: []}					,
						{label: 'Brucella melitensis',children: []}					,
						{label: 'Brucella suis',children: []}					,
						{label: 'Burkholderia mallei (formerly Pseudomonas mallei)',children: []}					,
						{label: 'Burkholderia pseudomallei',children: []}					,
						{label: 'Hendra virus',children: []}					,
						{label: 'Nipah virus',children: []}					,
						{label: 'Rift Valley fever virus',children: []}					,
						{label: 'Venezuelan Equine Encephalitis virus',children: []}					
				]},							
				{label: 'USDA VETERINARY SERVICES (VS) SELECT AGENTS',children: [							
						{label: 'African horse sickness virus',children: []}					,
						{label: 'African swine fever virus',children: []}					,
						{label: 'Akabane virus',children: []}					,
						{label: 'Avian influenza virus (highly pathogenic)',children: []}					,
						{label: 'Bluetongue virus (exotic)',children: []}					,
						{label: 'Bovine spongiform encephalopathy agent',children: []}					,
						{label: 'Camel pox virus',children: []}					,
						{label: 'Classical swine fever virus',children: []}					,
						{label: 'Ehrlichia ruminantium (Heartwater)',children: []}					,
						{label: 'Foot-and-mouth disease virus',children: []}					,
						{label: 'Goat pox virus',children: []}					,
						{label: 'Japanese encephalitis virus',children: []}					,
						{label: 'Lumpy skin disease virus',children: []}					,
						{label: 'Malignant catarrhal fever virus (Alcelaphine herpesvirus type 1)',children: []}					,
						{label: 'Menangle virus',children: []}					,
						{label: 'Mycoplasma capricolum subspecies capripneumoniae (contagious caprine pleuropneumonia)',children: []}					,
						{label: 'Mycoplasma mycoides subspecies mycoides small colony (Mmm SC) (contagious bovine pleuropneumonia)',children: []}					,
						{label: 'Peste des petits ruminants virus',children: []}					,
						{label: 'Rinderpest virus',children: []}					,
						{label: 'Sheep pox virus',children: []}					,
						{label: 'Swine vesicular disease virus',children: []}					,
						{label: 'Vesicular stomatitis virus (exotic): Indiana subtypes VSV-IN2, VSV-IN3',children: []}					,
						{label: 'Virulent Newcastle disease virus 1',children: []}					
				]},							
				{label: 'USDA PPQ SELECT AGENTS AND TOXINS',children: [							
						{label: 'Peronosclerospora philippinensis (Peronosclerospora sacchari)', id:'5', rooms: [{room:'103'}],children: []}					,
						{label: 'Phoma glycinicola (formerly Pyrenochaeta glycines)',children: []}					,
						{label: 'Ralstonia solanacearum race 3, biovar 2',children: []}					,
						{label: 'Rathayibacter toxicus',children: []}					,
						{label: 'Sclerophthora rayssiae var zeae',children: []}					,
						{label: 'Synchytrium endobioticum',children: []}					,
						{label: 'Xanthomonas oryzae',children: []}					,
						{label: 'Xylella fastidiosa (citrus variegated chlorosis strain)',children: []}					
				]}							
		]},									
		{label: 'Human-derived Materials',children: [									
				{label: 'Blood',children: []}					,		
				{label: 'Fluids',children: []}					,		
				{label: 'Cells',children: []}					,		
				{label: 'Cell line',children: []}					,		
				{label: 'Other tissue',children: []}							
		]},									
		{label: 'Biosafety Level 1 (BSL-1)',children: []}					,				
		{label: 'Biosafety Level 2 (BSL-2)',children: []}					,				
		{label: 'Biosafety Level 2+ (BSL-2+)',children: []}					,				
		{label: 'Biosafety Level 3 (BSL-3)',children: []}					,				
		{label: 'Animal Biosafety Level 1 (ABSL-1)',children: []}					,				
		{label: 'Animal Biosafety Level 2 (ABSL-2)',children: []}					,				
		{label: 'Animal Biosafety Level 2+ (ABSL-2+)',children: []}					,				
		{label: 'Animal Biosafety Level 3 (ABSL-3)',children: []}					,				
		{label: 'Biosafety Level 1 - Plants (BL1-P)',children: []}					,				
		{label: 'Biosafety Level 2 - Plants (BL2-P)',children: []}					,				
		{label: 'Biosafety Level 3 - Plants (BL3-P)',children: []}									
]}											
];
//Rooms associated with this inspection's PI
var piRooms = [
             {
				room: '101'
             },
             {
				room: '102'
             },
             {
 				room: '103'
             },
             {
 				room: '106'
             }
];
//store whole tree in variable for easy access to its methods
var $tree = $('#tree1');
//initialize tree
$tree.tree({
	 data:data,
	// dataUrl: 'data.js',
	 onCreateLi: function(node, $li) {
		 $li.find('.jqtree-title').after('</div>');
		//an array that will contain rooms, after we've determined if this node's hazard is present in them.
		rooms = [];
		//an array of this node's room objects, with each room object converted to a string
		//loop through this PI's rooms
		for(piRoom in piRooms){
			if(node.rooms){
				nodeRooms = JSON.stringify(node.rooms);
			}else{
				nodeRooms = '';
			}
			//console.log(nodeRooms);
			//store the room as a strong
			piRoom = JSON.stringify(piRooms[piRoom]);
			//console.log(nodeRooms);
			
			//check if this room is was contained within this node's room object
			if (nodeRooms.indexOf(piRoom) > -1){
				console.log($li);
				$li.find('.jqtree-element').addClass('shadow blue-border');
				piRoom = piRoom.replace(/\{|\}|room|,|\:|["']/ig, '');
				rooms.push('<label class="checkbox inline"><input type="checkbox" id="inlineCheckbox1" value="option1" checked><span class="metro-checkbox">Room '+piRoom+'</span></label>');
				$tree.tree('selectNode', node);
			}else{
				piRoom = piRoom.replace(/\{|\}|room|,|\:|["']/ig, '');
				rooms.push('<label class="checkbox inline"><input type="checkbox" id="inlineCheckbox1" value="option1"><span class="metro-checkbox" checked>Room '+piRoom+'</span></label>');
			}	
		}
		
		if (node.parent.parent){
			//add checkboxes and id fields, as long as this is not a root node
			for(room in rooms){
				$li.find('.jqtree-title').after(rooms[room]);
			}
			$li.find('.jqtree-title').after('<div class="hazardRooms"><p>Rooms:</p>');
			if(node.serialRequired == '1'){
				var value = '';
				if(node.serialNumber){
					value = node.serialNumber;
				}
					$li.find('.jqtree-title').after('<div class="input-append"><input value="'+value+'" class="span3" id="appendedInputButton" type="text"><button class="btn " type="button">Set Serial Number</button></div>');		
			}
		}
	 }
    
});
</script>
<?php
require_once '../bottom_view.php';
?>