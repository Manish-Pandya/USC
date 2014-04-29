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
	<div class="loading" ng-if="!question && !noQuestion" >
	  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
	  Getting Checklist...
	</div>
	<h3><span ng-show="noQuestion"  >Add a new question to</span> <span ng-show="question">Editing a question in </span><span ng-if="question || noQuestion">the checklist {{checklist.Name}}.<a class="btn btn-mini btn-info" style="margin-left:5px;" href="checklistHub.php#?id={{checklist.Hazard_id}}">View Checklist</a></span></h3>
	<h1 ng-show="!question.beingEdited" ng-hide="!question" id="currentQuestion">Current Question:<br><span id="questionText">{{question.Text}}</span><a style="margin-left:5px;" class="btn btn-primary btn-mini"  ng-click="editQuestion()"><i class="icon-pencil"></i>Edit Question</a></h1>
	<form ng-if="question.beingEdited || noQuestion" class="form" style="margin-top:10px;">
		<input type="text" class="span9" ng-model="questionCopy.Text" placeholder="Question text"/>
		<a style="margin:-10px 0 0 0;" ng-click="saveEditedQuestion(questionCopy)" class="btn btn-success btn-mini"><i class="icon-checkmark"></i>Save</a>
		<a ng-show="question" style="margin:-10px 0 0 3px;" class="btn btn-danger btn-mini" ng-click="cancelEdit(question)"><i class="icon-cancel"></i>Cancel</a>
		<img ng-if="questionCopy.IsDirty || question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
	</form>
	<span ng-hide="!question">
	<h2 style="margin-top:30px;" class="bold">Deficiencies:<a class="btn btn-mini btn-success" ng-class="{'btn-success':!addQuestion, 'btn-danger': addQuestion}" ng-click="addQuestion = !addQuestion"><i ng-class="{'icon-plus': !addQuestion, 'icon-cancel': addQuestion}"></i></a></h2>
	<form class="form" ng-if="!question.Deficiencies.length || addQuestion"  style="margin-top:10px;">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
		    <div class="controls">
		      <label>DEFICIENCY DESCRIPTION</label>
		      <textarea rows="5" id="newDeficiency" ng-model="question.newDeficiency.Text" cols="500" style="width:50%"></textarea>
		      <label>COMPLIANCE REFERENCE</label>
		 	  <input type="text" ng-model="question.newDeficiency.Reference"/>
		 	  <label>COMPLIANCE DESCRIPTION</label>
		 	  <textarea rows="3" ng-model="question.newDeficiency.Description" cols="500" style="width:50%"></textarea>
		 	</div>
		 </div>
		 <a class="btn btn-large btn-success addDeficiency" ng-click="addDeficiency(question)">Add</a><img ng-if="savingDeficiency" class="smallLoading" src="../../img/loading.gif"/>
	</form>

	<span ng-hide="!question">
	<hr>
	<ul class="deficiencyList questionList">
		<li ng-repeat="def in question.Deficiencies">
			<span ng-show="!def.edit">
				<h3><span class="bold">{{def.Text}}</span>
					<a class="btn btn-danger btn-mini DeactivateeRow" ng-click="handleObjActive(def,question)" ng-if="def.Is_active"><i class="icon-remove"></i></a>
					<a class="btn btn-success btn-mini DeactivateeRow" ng-click="handleObjActive(def,question)" ng-if="!def.Is_active"><i class="icon-checkmark"></i></a>
					<a class="btn btn-primary btn-mini DeactivateeRow" ng-click="editDef(def,question)"><i class="icon-pencil"></i></a>
					<img ng-show="def.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
		     	</h3>
		     	<h4><span class="bold">Compliance Reference:</span>  {{def.Reference}}</h4>
		     	<h4><span class="bold">Compliance Description:</span>  {{def.Description}}</h4>
	     	</span>
	     	<span ng-show="def.edit">
		     	<form class="form" style="margin-top:10px;">
			    	<div class="control-group">
				    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
				    <div class="controls">
				      <label>DEFICIENCY DESCRIPTION</label>
				      <textarea rows="5" id="newDeficiency" ng-model="question.newDeficiency.Text" cols="500" style="width:50%"></textarea>
				      <label>COMPLIANCE REFERENCE</label>
				 	  <input type="text" ng-model="question.newDeficiency.Reference"/>
				 	  <label>COMPLIANCE DESCRIPTION</label>
				 	  <textarea rows="3" ng-model="question.newDeficiency.Description" cols="500" style="width:50%"></textarea>
				 	</div>
				 </div>
				 <a class="btn btn-large btn-success addDeficiency" ng-click="addDeficiency(question)"><i class="icon-checkmark"></i>Save</a>
				 <a class="btn btn-large btn-danger addDeficiency" ng-click="cancelEdit(def)"><i class="icon-cancel"></i>Cancel</a>
				 <img ng-if="savingDeficiency" class="smallLoading" src="../../img/loading.gif"/>
				</form>
			</span>
     	</li>
	</ul>

	<h2 style="margin-top:50px;" class="bold">Recommendations<a class="btn btn-mini btn-success" ng-class="{'btn-success':!addRec, 'btn-danger': addRec}" ng-click="addRec = !addRec"><i ng-class="{'icon-plus': !addRec, 'icon-cancel': addRec}"></i></a></h2>
	<hr>
	<form class="form" style="margin-top:10px;" ng-if="addRec || !question.Recommendations.length">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newRecommendation" ng-model="question.newRecommendation.Text" cols="500" style="width:50%"></textarea>
		    </div>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addRecommendation(question)">Add</a><img ng-if="savingRecommendation" class="smallLoading" src="../../img/loading.gif"/>
	</form>

	
	<ul class="recommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="rec in question.Recommendations">
			<span ng-show="rec.edit">
				<form class="form" style="margin-top:10px;">
				    <div class="control-group">
					    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
					    <div class="controls">
					      <textarea rows="5" id="newRecommendation" ng-model="question.newRecommendation.Text" cols="500" style="width:50%"></textarea>
					    </div>
					 </div>
					 <a class="btn btn-large btn-success" ng-click="addRecommendation(question)"><i class="icon-checkmark"></i>Save</a>
					 <a class="btn btn-large btn-danger" ng-click="cancelEdit(rec)"><i class="icon-cancel"></i>Cancel</a>
					 <img ng-if="savingRecommendation" class="smallLoading" src="../../img/loading.gif"/>
				</form>
			</span>	
			
			<span ng-show="!rec.edit">
				<h3><span class="bold">{{rec.Text}}</span>
					<a class="btn btn-danger btn-mini DeactivateeRow" ng-click="handleObjActive(rec,question)" ng-if="rec.Is_active"><i class="icon-remove"></i></a>
					<a class="btn btn-success btn-mini DeactivateeRow" ng-click="handleObjActive(rec,question)" ng-if="!rec.Is_active"><i class="icon-checkmark"></i></a>
					<a class="btn btn-primary btn-mini DeactivateeRow" ng-click="editRec(rec,question)"><i class="icon-pencil"></i></a>
					<img ng-show="rec.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
		     	</h3>
			</span>
			
		</li>
	</ul>

	<h2 style="margin-top:50px;" class="bold">Notes<a class="btn btn-mini btn-success" ng-class="{'btn-success':!addObvs, 'btn-danger': addObvs}" ng-click="addObvs = !addObvs"><i ng-class="{'icon-plus': !addObvs, 'icon-cancel': addObvs}"></i></a></h2>
	
	<form class="form" style="margin-top:10px;" ng-if="addObvs || !question.Observations.length">
	    <div class="control-group">
		    <label class="control-label" for="email">Add a Observation for this Question:</label>
		    <div class="controls">
		      <textarea rows="5" id="newObservation" ng-model="question.newObservation.Text" cols="500" style="width:50%"></textarea>
		    </div>
		 </div>
		 <a class="btn btn-large btn-success" ng-click="addObservation(question)">Add</a><img ng-if="savingObservation" class="smallLoading" src="../../img/loading.gif"/>
	</form>

	
	<ul class="obsommendationList listWithChecks sortable" id="sortable">
		<li ng-repeat="obs in question.Observations">
			<span ng-show="obs.edit">
				<form class="form" style="margin-top:10px;">
				    <div class="control-group">
					    <label class="control-label" for="email">Add a Note for this Question:</label>
					    <div class="controls">
					      <textarea rows="5" id="newObservation" ng-model="question.newObservation.Text" cols="500" style="width:50%"></textarea>
					    </div>
					 </div>
					 <a class="btn btn-large btn-success" ng-click="addObservation(question)"><i class="icon-checkmark"></i>Save</a>
					 <a class="btn btn-large btn-danger" ng-click="cancelEdit(obs)"><i class="icon-cancel"></i>Cancel</a>
					 <img ng-if="savingObservation" class="smallLoading" src="../../img/loading.gif"/>
				</form>
			</span>	
			
			<span ng-show="!obs.edit">
				<h3><span class="bold">{{obs.Text}}</span>
					<a class="btn btn-danger btn-mini DeactivateeRow" ng-click="handleObjActive(obs,question)" ng-if="obs.Is_active"><i class="icon-remove"></i></a>
					<a class="btn btn-success btn-mini DeactivateeRow" ng-click="handleObjActive(obs,question)" ng-if="!obs.Is_active"><i class="icon-checkmark"></i></a>
					<a class="btn btn-primary btn-mini DeactivateeRow" ng-click="editObs(obs,question)"><i class="icon-pencil"></i></a>
					<img ng-show="obs.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
		     	</h3>
			</span>
			
		</li>
	</ul>
	</span>
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