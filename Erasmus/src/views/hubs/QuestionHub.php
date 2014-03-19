<?php
require_once '../top_view.php';
?>
<script src="../../js/questionHub.js"></script>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="span3">
			<img src="../../img/question-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Question Hub</h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" ng-app="questionHub" ng-controller="QuestionHubController">
{{question}}
	<h1 id="currentQuestion">Current Question:<br><span id="questionText">{{question.Text}}</span></h1>
	<h3 style="margin-top:30px;">Deficiencies for this question:</h3>
	<ul class="deficiencyList listWithChecks sortable" id="sortable">
		<li ng-repeat="def in question.Deficiencies">Exposure Control Plan has not been reviewed and updated at least annually<div class="checklistRow"><a class="btn  btn-danger deactivateRow">Deactivate</a></div></li>
	</ul>
	
	<form class="form" style="margin-top:10px;">
	
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newDeficiency" ng-model="question.newDeficiency" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success addDeficiency" ng-click="addDeficiency(question)">Add</a>
	</form>
	
	<h3 style="margin-top:30px;">Recommendations for this question:</h3>
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="rec in question.Recommendations">{{rec.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addRecommendation(question)">Add</a>
	</form>

	<h3 style="margin-top:30px;">Notes for this question:</h3>
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="obs in question.Observations">{{obs.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addObservation(question)">Add</a>
	</form>
	
	</div>
</div>

<!-- begin add new question modal dialogue -->
<div class="modal hide fade" id="editQuestion">
	<div class="modal-header">
		<h3>Add a New Question</h3>
	</div>
	<form style="padding:0; margin:0;" class="form">
	<div class="modal-body">
	<!-- 
		<div class="control-group">
		    <label class="control-label" for="email">Choose Checklist:</label>
		    <div class="controls">
		      <input autocomplete="off" data-provide="typeahead" type="text" name="email" id="email" class="tyepahead" placeholder="Biosafety Level 3 (BSL-3)"  data-source='["Biosafety Level 1 (BSL-1)","Biosafety Level 2 (BSL-2)", "Biosafety Level 2+ (BSL-2+)","Biosafety Level 3 (BSL-3)"]'/>
		    </div>
	    </div>
	 -->
	    <div class="control-group">
		    <label class="control-label" for="email">Question Text:</label>
		    <div class="controls">
		      <textarea rows="10" id="newQuestionText" cols="500" style="width:100%"></textarea>
		 </div>
	</div>
	 <div class="modal-footer">
    <a href="#" class="btn btn-danger btn-large" data-dismiss="modal">Close</a>
    <a href="#" id="confirmNewQuestionText" class="btn btn-primary btn-large" data-dismiss="modal">Set Question Text</a>
  </div>
  </form>
</div>
<!-- end add new question modal dialogue -->

<?php 
require_once '../bottom_view.php';
?>