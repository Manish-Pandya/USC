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