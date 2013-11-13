<?php
require_once '../top_view.php';
?>

<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="span3">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Checklist Hub</h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg">
	<h1 id="currentChecklist">Currently Editing:<br>BLOODBORNE PATHOGENS (e.g. research involving human blood, body fluids, unfixed tissue) OSHA Bloodborne Pathogens (29 CFR 1910.1030)</h1>
	
	<form class="form" style="margin-top:10px;">
	
	    <div class="control-group">
		    <label class="control-label" for="email">Change Checklist:</label>
		    <div class="controls">
		      <input autocomplete="off" data-provide="typeahead" type="text" name="email" id="email" class="tyepahead" placeholder="Biosafety Level 3 (BSL-3)"  data-source='["Biosafety Level 1 (BSL-1)","Biosafety Level 2 (BSL-2)", "Biosafety Level 2+ (BSL-2+)","Biosafety Level 3 (BSL-3)"]'/>
		    </div>
	    </div>  
    </form>
    <hr>
    <h3>This Checklist's Questions:</h3>
    <div id="showHideQuestions" class="btn btn-primary btn-large" style="margin:10px 0">Hide Disabled Questions</div>
    <ul class="questionList sortable" id="sortable">
   		<li><h4>Exposure Control Plan is accessible to employees with occupational exposure to bloodborne pathogens</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li>
   		<li><h4>Exposure Control Plan has been reviewed and updated at least annually</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li>
   		<li><h4>Hepatitis B vaccine has been made available at no cost to all personnel who have occupational exposure</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li>
    	<li><h4>Post-exposure evaluation & follow-up is available at no cost to personnel who have an exposure incident</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li>    
   		<li><h4>Biohazard warning labels are affixed to all appliances or containers used to store or transport samples</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li>  
    	<li><h4>All personnel with occupational exposure have completed annual bloodborne pathogens training</h4><div class="checklistButtons"><a href="QuestionHub.php" class="btn btn-large">Edit</a><a class="btn btn-large btn-danger deactivateRow">Deactivate</a></div></li>
    </ul>
    <div style="clear:both;"></div>
  </div>
  <div style="clear:both;"></div>


<script>
$(document.body).on("click", '.remove', function(){
	 $(this).parent().hide();
});

$(document.body).on("click", "#showHideQuestions", function(e){
	e.preventDefault();
	$('.error').toggle();
	 $(this).text(function(i, text){
         return text === "Hide Disabled Questions" ? "Show Disabled Questions" : "Hide Disabled Questions";
     })
	
});

 $('#addChecklistToHazard').click(function(){
	 $('#parentHazards').append('<li>' + $('#toAdd').val() + '<a class="remove  btn btn-danger">Remove</a></li>');
 })
</script>
<?php 
require_once '../bottom_view.php';

?>