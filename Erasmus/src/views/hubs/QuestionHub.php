<?php
require_once '../top_view.php';
?>
<script src="../../js/questionHub.js"></script>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/question-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Question Hub
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
			</h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" ng-app="questionHub" ng-controller="QuestionHubController"><br>
	<h3><span ng-show="noQuestion">Add a new question to</span> <span ng-show="question">Editing a question in </span>the checklist {{checklist.Name}}.<a class="btn btn-mini btn-info" style="margin-left:5px;" href="checklistHub.php#?id={{checklist.Hazard_id}}">View Checklist</a></h3>
	<h1 ng-show="!question.beingEdited" ng-hide="!question" id="currentQuestion">Current Question:<br><span id="questionText">{{question.Text}}</span><a style="margin-left:5px;" class="btn btn-primary btn-mini"  ng-click="editQuestion()"><i class="icon-pencil"></i>Edit Question</a></h1>
	<form ng-if="question.beingEdited || noQuestion" class="form" style="margin-top:10px;">
		<input type="text" class="span9" ng-model="questionCopy.Text" placeholder="Question text"/>
		<a style="margin:-10px 0 0 0;" ng-click="saveEditedQuestion(questionCopy)" class="btn btn-success btn-mini"><i class="icon-checkmark"></i>Save</a>
		<a ng-show="question" style="margin:-10px 0 0 3px;" class="btn btn-danger btn-mini" ng-click="cancelEdit()"><i class="icon-cancel"></i>Cancel</a>
		<img ng-if="questionCopy.IsDirty || question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
	</form>

	<span ng-hide="!question">
	<h3 style="margin-top:30px;" ng-show="question.Deficiencies.length">Deficiencies for this question:</h3>
	<h3 style="margin-top:30px;" ng-hide="question.Deficiencies.length">This question doesn't have any deficiencies yet.</h3>
	<ul class="deficiencyList listWithChecks sortable" id="sortable">
		<li ng-repeat="def in question.Deficiencies">{{def.Text}}<div class="checklistRow"><a class="btn btn-danger deactivateRow" ng-click="handleObjActive(def,question)">Deactivate</a></div></li>
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
		 <a class="btn btn-large btn-success addDeficiency" ng-click="addDeficiency(question)">Add</a><img ng-if="savingDeficiency" class="smallLoading" src="../../img/loading.gif"/>
	</form>
	
	<h3 style="margin-top:30px;" ng-show="question.Recommendations.length">Recommendations for this question:</h3>
	<h3 style="margin-top:30px;" ng-hide="question.Recommendations.length">This question doesn't have any recommendations yet.</h3>
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="rec in question.Recommendations">{{rec.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow" ng-click="handleObjActive(rec,question)">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" ng-model="question.newRecommendation" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addRecommendation(question)">Add</a><img ng-if="savingRecommendation" class="smallLoading" src="../../img/loading.gif"/>
	</form>

	<h3 style="margin-top:30px;" ng-show="question.Notes.length">Notes for this question:</h3>
	<h3 style="margin-top:30px;" ng-hide="question.Notes.length">This question doesn't have any notes yet:</h3>
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="obs in question.Observations">{{obs.Text}}<div class="checklistRow"><a class="btn  btn-danger deactivateRow" ng-click="handleObjActive(obs,question)">Deactivate</a></div></li>
	</ul>
		
	<form class="form" style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Note for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" ng-model="question.newObservation" cols="500" style="width:50%"></textarea>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addObservation(question)">Add</a><img ng-if="savingObservation" class="smallLoading" src="../../img/loading.gif"/>
	</form>
	</span>
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