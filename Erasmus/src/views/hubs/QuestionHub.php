<?php
require_once '../top_view.php';
?>

<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="span3">
			<img src="../../img/question-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Question Hub</h2>	
		</li>
		<li style="margin-top:2px;">
			<a class="addQuestion" style="text-shadow: none; color:white; background:#555" data-toggle="modal"  href="#addUser" ><img src='../../img/add-question-icon.png' style="margin-right:13px;">New Question</a>
		</li>
	</ul>
</div>
<div class="container-fluid whitebg">
	<h1 id="currentQuestion">Current Question:<br>Exposure Control Plan has been reviewed and updated at least annually</h1>
	<h3 style="margin-top:30px;">Possible Deficiencies for this question:</h3>
	<ul class="deficiencyList">
		<li>Exposure Control Plan has not been reviewed and updated at least annually<div class="checklistRow"><a class="btn  btn-danger deactivateRow">Deactivate</a></div></li>
		<li>Updates do not reflect new or modified tasks and procedures which affect occupational exposure<div class="checklistRow"><a class="btn  btn-danger deactivateRow">Deactivate</a></div></li>
		<li>Updates do not reflect new or revised employee positions with occupational exposure<div><a class="btn btn-danger deactivateRow">Deactivate</a></div></li>
		<li>Somebody did something very dangerous and stupid that is likely to cause a rift in the time-space continuum, allowing evil aliens to aggaghaghahhgagh...........END TRANSMISSION<div><a class="btn btn-danger deactivateRow">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
		    <div class="controls">
		      <textarea rows="10" id="newDeficiency" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success addDeficiency">Add</a>
	</form>
	</div>
</div>

<!-- begin add new question modal dialogue -->
<div class="modal hide fade" id="addUser">
	<div class="modal-header">
		<h3>Add a New Question</h3>
	</div>
	<form style="padding:0; margin:0;" class="form">
	<div class="modal-body">
		<div class="control-group">
		    <label class="control-label" for="email">Choose Checklist:</label>
		    <div class="controls">
		      <input autocomplete="off" data-provide="typeahead" type="text" name="email" id="email" class="tyepahead" placeholder="Biosafety Level 3 (BSL-3)"  data-source='["Biosafety Level 1 (BSL-1)","Biosafety Level 2 (BSL-2)", "Biosafety Level 2+ (BSL-2+)","Biosafety Level 3 (BSL-3)"]'/>
		    </div>
	    </div>
	    <div class="control-group">
		    <label class="control-label" for="email">Question Text:</label>
		    <div class="controls">
		      <textarea rows="10" id="newDeficiency" cols="500" style="width:100%"></textarea>
		 </div>
		
	</div>
	 <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" class="btn btn-primary btn-large">Create</a>
  </div>
</div>
<!-- end add new question modal dialogue -->

<script>
 $('.addDeficiency').click(function(){
	 $('.deficiencyList').append('<li>' + $('#newDeficiency').val() + '<div><a class="btn btn-danger deactivateRow">Deactivate</a></div></li>');
 })
 </script>
<?php 
require_once '../bottom_view.php';
?>
<!-- 

Somebody did something very dangerous and stupid that is likely to cause a rift in the time-space continuum, allowing evil aliens to aggaghaghahhgagh...........END TRANSMISSION
<li><h4>Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li> -->