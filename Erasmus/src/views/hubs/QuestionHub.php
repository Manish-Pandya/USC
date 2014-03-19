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
<pre>{{question|json}}</pre>
	<h1 ng-show="!question.beingEdited" id="currentQuestion">Current Question:<br><span id="questionText">{{question.Text}}</span><a class="btn btn-primary bt-large" ng-click="editQuestion()"><i class="icon-pencil"></i>Edit Question</a></h1>
	<input ng-show="question.beingEdited" type="text" class="span9" ng-model="questionCopy.Text"/><a ng-click="saveEditedQuestion(question)" class="btn btn-success btn-small"><i class="icon-checkmark"></i>Save</a><a class="btn btn-danger btn-small" ng-click="cancelEdit()"><i class="icon-cancel"></i>Cacnel</a>
	<h3 style="margin-top:30px;">Deficiencies for this question:</h3>
	<ul class="deficiencyList listWithChecks sortable" id="sortable">
		<li ng-repeat="def in question.Deficiencies">{{def.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow" ng-click="handleObjActive(def,question)">Deactivate</a></div></li>
	</ul>
	
	<form class="form" style="margin-top:10px;">
	
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
		    <div class="controls">
		      <label>DEFICIENCY DESCRIPTION</label>
		      <textarea rows="5" id="newDeficiency" ng-model="question.newDeficiency.text" cols="500" style="width:50%"></textarea>
		      <label>COMPLIANCE REFERENCE</label>
		 	  <input type="text" ng=model="question.newDeficiency.reference"/>
		 	  <label>COMPLIANCE DESCRIPTION</label>
		 	  <textarea rows="3" ng-model="question.newDeficiency.description" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success addDeficiency" ng-click="addDeficiency(question)">Add</a>
	</form>
	
	<h3 style="margin-top:30px;">Recommendations for this question:</h3>
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="rec in question.Recommendations">{{rec.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow" ng-click="handleObjActive(rec,question)">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" ng-model="question.newRecommendation" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addRecommendation(question)">Add</a>
	</form>

	<h3 style="margin-top:30px;">Notes for this question:</h3>
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="obs in question.Observations">{{obs.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow" ng-click="handleObjActive(obs,question)">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Note for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" ng-model="question.newObservation" cols="500" style="width:50%"></textarea>
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