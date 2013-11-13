<?php 
	require_once '../top_view.php';
?>
<div class="navbar">
<ul class="nav pageMenu" style="background: #e67e1d;">
	<li class="span3">
		<img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">Hazard Hub</h2>	
	</li>
</ul>
</div>
<div id="tree1" class="container-fluid whitebg" style="padding:50px 70px;"></div>
<div id="hazardModal" class="modal hide fade">
	<div class="modal-header" id="hazardModalHeader">
		<h2>Create Hazard</h2>
	</div>
	<form class="form-horizontal">
		<div class="modal-body" id="hazardModalBody">
		
			<div class="control-group">
		       <label class="control-label" for="inputEmail">Hazard Name:</label>
		       <div class="controls">
		         <input type="text" id="hazardName" placeholder="Name">
		       </div>
		     </div>
		     
		     <div class="control-group">
		     	<div class="controls">
			    	 <label class="checkbox">
			       	  	<input type="checkbox"><span class="metro-checkbox">Requires Authorization</span>
			         </label>
		         </div>
		     </div>
		     
		     <div class="control-group">
			     <div class="controls">
				     <label class="checkbox">
				    	 <input type="checkbox"><span class="metro-checkbox">Requires Label</span>
				     </label>
			     </div>
		     </div>
		</div>
		<div class="modal-footer">
			<input type="hidden" name="parentNodeId" id="parentNodeId"/>
			<a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Cancel</a>
			<a id="submitHazard" class="btn btn-primary btn-large">Create Hazard</a>
		</div>
	</form>
</div>
<script>

var data = [
{label: 'Biological Materials',children: [											
		{label: 'Recombinant DNA', id:'1', hasChecklist: '1', children: [									
				{label: 'Viral Vectors', id:"4", hasChecklist: '1', children: [							
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
						{label: 'Retrovirus / MMLV (Ecotropic)',children: []}					
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
						{label: 'Peronosclerospora philippinensis (Peronosclerospora sacchari)',children: []}					,
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

var $tree = $('#tree1');
$('#tree1').bind("open_node.jstree", function (event, data) { 
    if((data.inst._get_parent(data.rslt.obj)).length) { 
      data.inst.open_node(data.inst._get_parent(data.rslt.obj), false,true); 
    } 
    
});

$tree.tree({
	 data:data,
	 dragAndDrop: true,
	 
	// dataUrl: 'data.js',
	 onCreateLi: function(node, $li) {
		   console.log(node.getLevel());
	        if(node.hasChecklist == '1'){
		       // console.log(node);
	        	$li.find('.jqtree-title').after('<div class="hazarNodeButtons"> <a class="btn btn-large btn-info hazardBtn" href="ChecklistHub.php?id='+node.id+'"><i class="icon-checkmark"></i>Edit Checklist</a><a class="btn btn-large btn-warning hazardBtn"  node-id="'+node.id+'" data-toggle="modal" href="#hazardModal"><span>!</span>Edit Hazard</a><a data-toggle="modal" href="#hazardModal" class="btn btn-large btn-primary childHazard" node-id="'+node.id+'">Add Child Hazard</a></div>');
	        }else{
	        	//console.log(node);
	        	$li.find('.jqtree-title').after('<div class="hazarNodeButtons"><a class="btn btn-large btn-success hazardBtn" href="ChecklistHub.php?id='+node.id+'"><i class="icon-checkmark"></i>Create Checklist</a><a class="btn btn-large btn-warning hazardBtn"  node-id="'+node.id+'" data-toggle="modal" href="#hazardModal"><span>!</span>Edit Hazard</a><a data-toggle="modal" href="#hazardModal" class="btn btn-large btn-primary childHazard" node-id="testvalue">Add Child Hazard</a></div>');
		    }
	 }
    
});

$(document.body).on("click", ".childHazard", function(){
	nodeID = $(this).attr("node-id");
	var parentNode = $tree.tree('getNodeById', nodeID);
	console.log(parentNode);
	$("#hazardModalHeader").html('<h2>Create a New Hazard</h2>');
	$('#submitHazard').text('Create Hazard');
	$("#parentNodeId").val(parentNode.id);
});
$(document.body).on("click", "#submitHazard", function(){
	var parentNode = $tree.tree('getNodeById', $("#parentNodeId").val());
	console.log($("#parentNodeId").val());
	
	if($("#hazardModalHeader").html() == '<h2>Create a New Hazard</h2>'){
		$tree.tree('openNode', parentNode);
		$tree.tree(
			    'appendNode',
			    {
			        label: $('#hazardName').val(),
			        //id: 456
			    },
			    parentNode
			);
		$('#hazardModal').modal('hide')
	}else{
		//console.log$("#parentNodeId").val()
		$tree.tree(
			    'updateNode',
			    parentNode,
			    {
			        label: $('#hazardName').val(),
			        other_property: 'abc'
			    }
			);
	}
});

$(document.body).on("click", ".hazardBtn", function(){
	nodeID = $(this).attr("node-id");
	var parentNode = $tree.tree('getNodeById', nodeID);
	console.log(parentNode);
	$("#hazardModalHeader").html('<h2>Editing Hazard: '+parentNode.name+'</h2>');
	$('#submitHazard').text('Update Hazard');
	$("#parentNodeId").val(parentNode.id);
});


</script>
<?php 
require_once '../bottom_view.php';
?>