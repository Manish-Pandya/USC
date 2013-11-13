<?php
require_once '../top_view.php';
?>
<div class="navbar">
	<ul class="nav pageMenu" style="background: #d00;">
		<li class="span4">
			<img src="../../img/new-inspection-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">New Inspection</h2>	
		</li>
		<li class=>
			<a href="#"  class="btn btn-large" style="margin-top:3px;" id="showSecondInspector"><img style="margin:0 10px;" src='../../img/add-user-icon.png'>Add an Inspector</a>
		</li>
	</ul>
</div>
<div class="container-fluid whitebg">
	<h2 id="inspector">Inspector<span class="plural"></span>:  Bob Userington<span class="moreInpectors"></span></h2>
	<form class="form form-horizontal" style="margin-top:10px;" method="post" action="HazardAssessment.php">
			
		<div class="control-group hide" id="secondInspector">
		    <label class="control-label" for="email">Add Inspector:</label>
		    <div class="controls">
		      <input autocomplete="off" name="pi" data-provide="typeahead" type="text" name="email" id="secondInspectorField" class="tyepahead" data-source='["SHERLOCK HOLMES","GADGET","HERCULE POIROT", "COLUMBO","SAM SPADE","THOMAS MAGNUM"," PHILIP MARLOWE","VERONICA MARS","Mike Hammer"]'/>
		      <a class="btn btn-success" href="#inspector" id="setSecondInspector">Set Inspector</a>
		    </div>
	    </div>
	
	    <div class="control-group">
		    <label class="control-label" for="email">Principle Investigator:</label>
		    <div class="controls">
		      <input autocomplete="off" name="pi" data-provide="typeahead" type="text" name="email" id="pi" class="tyepahead" data-source='["SHERLOCK HOLMES","GADGET","HERCULE POIROT", "COLUMBO","SAM SPADE","THOMAS MAGNUM"," PHILIP MARLOWE","VERONICA MARS","Mike Hammer"]'/>
		      <a class="btn btn-success" id="showHazards">Set PI</a>
		    </div>
	    </div>
   
   	<div class="rooms" id="hazards" action="HazardAssessment.php" method="post">
   		<a class="btn btn-large btn-primary" href="../hubs/PIHub.php?rooms=true">Add/Edit Rooms</a>
   		<a class="btn btn-large btn-info" href="../hubs/PIHub.php?safetyContacts=true">Add/Edit Safety Contacts</a>
   		<a class="btn btn-large btn-warning href="../hubs/PIHub.php?specialHazards=true">Add/Edit Special Hazards</a>
   		<h3 id="hazardLabel"></h3>
   		<div class="hazard">
   			<label class="checkbox">
				<input type="checkbox" name="optionsRadios" id="optionsRadios1" value="option1" checked>
					<span class="metro-checkbox">Room 107</span>
			</label>
			 <label class="checkbox ">
     			<input type="checkbox" id="inlineCheckbox1" value="option1" checked>
				<span class="metro-checkbox">Room 109</span>
			</label>
			<label class="checkbox ">
     			<input type="checkbox" id="inlineCheckbox1" value="option1" checked>
				<span class="metro-checkbox">Room 111</span>
			</label>
   		</div>
   		<button type="submit" href="HazardAssessment.php" style="margin:15px 0; height:50px;" class="btn btn-large btn-info">Hazard Assesment<i style="font-size:45px; margin:3px 16px 0 -9px" class="icon-arrow-right"></i></button>
   	</div>
   	</form>
</div>
<script>
	$(document).ready(function(){
		$('#hazards').hide();
	});

	$('#showHazards').click(function(){
		console.log('adsfadsfad');
		$('#hazards').show();	
		$('#hazardLabel').text($("#pi").val() + "'s Rooms:");
	});

	$(document.body).on("click", "#showSecondInspector", function(e){
		e.preventDefault();
		console.log('clclclclclc');
		$('#secondInspector').toggleClass('hide');
	});
	$(document.body).on("click", "#setSecondInspector", function(e){
		e.preventDefault();
		val = $("#secondInspectorField").val();
		$("#inspector .plural").text('s');
		$("#inspector .moreInpectors").append(", "+val+'');
	});
	
</script>
<?php
require_once '../bottom_view.php';
?>