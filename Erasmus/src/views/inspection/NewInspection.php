<?php
require_once '../top_view.php';
?>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="span3">
			<img src="../../img/new-inspection-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">New Inspection</h2>	
		</li>
		<li style="margin-top:2px;">
			<a id="" style="text-shadow: none; color:white; background:#555" data-toggle="modal"  href="#addUser" ><img src='../../img/add-question-icon.png' style="margin-right:13px;">New Question</a>
		</li>
	</ul>
</div>
<div class="container-fluid whitebg">
	<form class="form form-horizontal" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Principle Investigator:</label>
		    <div class="controls">
		      <input autocomplete="off" data-provide="typeahead" type="text" name="email" id="pi" class="tyepahead" data-source='["SHERLOCK HOLMES","HERCULE POIROT", "COLUMBO","SAM SPADE","THOMAS MAGNUM"," PHILIP MARLOWE","VERONICA MARS","Mike Hammer"]'/>
		      <a class="btn btn-success" id="showHazards">Set PI</a>
		    </div>
	    </div>
   	</form>
   	<div class="hazards" id="hazards">
   		<h3 id="hazardLabel"></h3>
   		<div class="hazard">
   			Hazard
   		</div>
   	</div>
</div>
<script>
	$(document).ready(function(){
		$('#hazards').hide();
	});

	$('#showHazards').click(function(){
		$('#hazards').show();	
		$('#hazardLabel').text($("#pi").val() + "'s Hazards:");
	})
</script>
<?php
require_once '../bottom_view.php';
?>