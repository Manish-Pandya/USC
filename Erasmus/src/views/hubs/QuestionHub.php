<?php
require_once '../top_view.php';
?>
<script src="../../js/questionHub.js"></script>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/question-icon.png" class="pull-left" style="height:50px" />
			<h2 style="padding: 11px 0 5px 85px;margin-left: -15px;">Question Hub
				<a style="float:right;margin: 6px 35px 0 9px;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>  
        		<a href="HazardHub.php" style="float:right; font-size:20px;"><i class="icon-arrow-left-2" style="font-size: 20px; margin: 3px 12px 0 0;"></i>Hazard Hub</a>
        	</h2>
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" ng-app="questionHub" ng-controller="QuestionHubController"><br>
	<div class="loading" ng-if="!question && !noQuestion || !checklist" >
	  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
	  Loading...
	</div>
	<span ng-if="checklist">
		<h1 ng-if="checklist" id="currentChecklist"><span class="underline">Checklist Title:</span> 
			 {{checklist.Name}}
			 <a class="btn btn-info" style="margin-left:5px;" href="ChecklistHub.php#?id={{checklist.Hazard_id}}"><i class="icon-checkmark">	
			 </i>View Checklist</a>
		</h1>
		<span ng-if="!question.beingEdited && !noQuestion" class="spacer small"></span>
		<a ng-if="!question.beingEdited && !noQuestion" class="btn btn-primary" ng-click="editQuestion()"><i class="icon-pencil"></i>Edit Question, Compliance Reference or Description</a>
		<span ng-if="!question.beingEdited && !noQuestion" class="spacer small"></span>
		<h2 class="row" ng-if="question" ng-hide="!question" id="currentQuestion"><span class="span3 bold">Question:</span><span class="span9" id="questionText">{{question.Text}}</span>
		</h2>
		<span ng-if="!question.beingEdited && !noQuestion" class="spacer med"></span>
		<h3 class="row" ><span ng-if="question" class="bold span3">Compliance Reference:</span><span ng-if="!question.beingEdited && question.Reference" class="span9">{{question.Reference}}</span></h3>
		<span ng-if="!question.beingEdited && !noQuestion" class="spacer small"></span>
		<h3 class="row"><span ng-if="question" class="bold span3">Compliance Description:</span><span ng-if="!question.beingEdited && question.Reference" class="span9">{{question.Description}}</span></h3>

		<span ng-if="!question.beingEdited && !noQuestion" class="spacer med"></span>

		<form ng-if="question.beingEdited || noQuestion" class="form" style="margin-top:10px;">

			<div class="control-group">
			    <label ng-if="question" class="control-label" for="email">EDIT QUESTION:</label>
			    <label ng-if="!question" class="control-label" for="email">ENTER CHECKLIST QUESTION:</label>
			    <div class="controls">
					<input type="text" class="span9" ng-model="questionCopy.Text" placeholder="Question text"/>
				</div>
			</div>

			<div class="control-group">
			    <label ng-if="question" class="control-label" for="email">EDIT COMPLIANCE REFERENCE:</label>
			    <label ng-if="!question" class="control-label" for="email">ENTER COMPLIANCE REFERENCE:</label>
			    <div class="controls">
				 	<input type="text" placeholder="Compliance Reference" ng-model="questionCopy.Reference"/>
				</div>
			</div>

			<div class="control-group">
		 		<label ng-if="question" class="control-label" for="email">EDIT COMPLIANCE DESCRIPTION:</label>
				    <label ng-if="!question" class="control-label" for="email">ENTER COMPLIANCE DESCRIPTION:</label>
		 		<div class="controls">
		 			<textarea rows="3" placeholder="Compliance Description"  ng-model="questionCopy.Description" cols="500" style="width:50%"></textarea><br>
		 		</div>
		 	</div>

		 	<a style="margin:-10px 0 0 0;" ng-click="saveEditedQuestion( question )" class="btn btn-success"><i class="icon-checkmark"></i>Save Question</a>
			<a ng-show="question" style="margin:-10px 0 0 3px;" class="btn btn-danger" ng-click="cancelEdit( question )"><i class="icon-cancel"></i>Cancel</a>
			<img ng-if="questionCopy.IsDirty || question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
		</form>
		<span ng-hide="!question">
		<h2 style="margin-top:30px;" class="bold">Deficiencies
			<a ng-show="question.Deficiencies.length" class="btn btn-mini btn-success" ng-class="{'btn-success':!addDef, 'btn-danger': addDef}" ng-click="addDef = !addDef">
				<i ng-class="{'icon-plus': !addDef, 'icon-cancel': addDef}"></i>
			</a>
		</h2>
		<form class="form" ng-if="!question.Deficiencies.length || addDef"  style="margin-top:10px;">
		    <div class="control-group">
			    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
			    <div class="controls">
			      <textarea rows="2" id="newDeficiency" ng-model="question.newDeficiency.Text" cols="500" style="width:50%"></textarea>
			 	</div>
			 </div>
			 <a class="btn btn-success addDeficiency" ng-click="addDeficiency(question)"><i class="icon-checkmark"></i>Save Deficiency</a><img ng-if="savingDeficiency" class="smallLoading" src="../../img/loading.gif"/>
		</form>

		<span ng-hide="!question">
		<hr>
		<ul class="deficiencyList questionList">
			<li ng-repeat="def in question.Deficiencies" ng-class="{inactive: !def.Is_active}">
				<span ng-show="!def.edit">
					<h3><span class="bold">{{def.Text}}</span>
						<a class="btn btn-danger btn-mini DeactivateeRow" ng-click="handleObjActive(def,question)" ng-if="def.Is_active"><i class="icon-remove"></i></a>
						<a class="btn btn-success btn-mini DeactivateeRow" ng-click="handleObjActive(def,question)" ng-if="!def.Is_active"><i class="icon-checkmark"></i></a>
						<a class="btn btn-primary btn-mini DeactivateeRow" ng-click="editDef(def,question)"><i class="icon-pencil"></i></a>
						<img ng-show="def.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
			     	</h3>
		     	</span>
		     	<span ng-show="def.edit">
			     	<form class="form" style="margin-top:10px;">
				    	<div class="control-group">
					    <label class="control-label" for="email">Add a Deficiency for this Question:</label>
					    <div class="controls">
					      <label>DEFICIENCY DESCRIPTION</label>
					      <textarea rows="2" id="newDeficiency" ng-model="question.newDeficiency.Text" cols="500" style="width:50%"></textarea>
					 	</div>
					 </div>
					 <a class="btn  btn-success addDeficiency" ng-click="addDeficiency(question)"><i class="icon-checkmark"></i>Save</a>
					 <a class="btn  btn-danger addDeficiency" ng-click="cancelEdit(def)"><i class="icon-cancel"></i>Cancel</a>
					 <img ng-if="savingDeficiency" class="smallLoading" src="../../img/loading.gif"/>
					</form>
				</span>
	     	</li>
		</ul>

		<h2 style="margin-top:50px;" class="bold">Recommendations
			<a ng-show="question.Recommendations.length" class="btn btn-mini btn-success" ng-class="{'btn-success':!addRec, 'btn-danger': addRec}" ng-click="addRec = !addRec">
				<i ng-class="{'icon-plus': !addRec, 'icon-cancel': addRec}"></i>
			</a>
		</h2>
		<hr>
		<form class="form" style="margin-top:10px;" ng-if="addRec || !question.Recommendations.length">
		    <div class="control-group">
			    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
			    <div class="controls">
			      <textarea rows="2" id="newRecommendation" ng-model="question.newRecommendation.Text" cols="500" style="width:50%"></textarea>
			    </div>
			 </div>
			 <a class="btn  btn-success" ng-click="addRecommendation(question)"><i class="icon-checkmark"></i>Save Recommendation</a><img ng-if="savingRecommendation" class="smallLoading" src="../../img/loading.gif"/>
		</form>

		
		<ul class="questionList sortable" id="sortable">
			<li ng-repeat="rec in question.Recommendations" ng-class="{inactive: !rec.Is_active}">
				<span ng-show="rec.edit">
					<form class="form" style="margin-top:10px;">
					    <div class="control-group">
						    <label class="control-label" for="email">Add a Recommendation for this Question:</label>
						    <div class="controls">
						      <textarea rows="2" id="newRecommendation" ng-model="question.newRecommendation.Text" cols="500" style="width:50%"></textarea>
						    </div>
						 </div>
						 <a class="btn  btn-success" ng-click="addRecommendation(question)"><i class="icon-checkmark"></i>Save</a>
						 <a class="btn  btn-danger" ng-click="cancelEdit(rec)"><i class="icon-cancel"></i>Cancel</a>
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

		<h2 style="margin-top:50px;" class="bold">Notes
			<a class="btn btn-mini btn-success" ng-class="{'btn-success':!addObvs, 'btn-danger': addObvs}" ng-click="addObvs = !addObvs" ng-show="question.Observations.length">
				<i ng-class="{'icon-plus': !addObvs, 'icon-cancel': addObvs}"></i>
			</a>
		</h2>
		
		<form class="form" style="margin-top:10px;" ng-if="addObvs || !question.Observations.length">
		    <div class="control-group">
			    <label class="control-label" for="email">Add a Note or Comment for this Question:</label>
			    <div class="controls">
			      <textarea rows="2" id="newObservation" ng-model="question.newObservation.Text" cols="500" style="width:50%"></textarea>
			    </div>
			 </div>
			 <a class="btn  btn-success" ng-click="addObservation(question)"><i class="icon-checkmark"></i>Save Note</a><img ng-if="savingObservation" class="smallLoading" src="../../img/loading.gif"/>
		</form>

		
		<ul class="questionList sortable" id="sortable">
			<li ng-repeat="obs in question.Observations" ng-class="{inactive: !obs.Is_active}">
				<span ng-show="obs.edit">
					<form class="form" style="margin-top:10px;">
					    <div class="control-group">
						    <label class="control-label" for="observation">Add a Note or Comment for this Question:</label>
						    <div class="controls">
						      <textarea rows="2" id="newObservation" ng-model="question.newObservation.Text" cols="500" style="width:50%"></textarea>
						    </div>
						 </div>
						 <a class="btn  btn-success" ng-click="addObservation(question)"><i class="icon-checkmark"></i>Save</a>
						 <a class="btn  btn-danger" ng-click="cancelEdit(obs)"><i class="icon-cancel"></i>Cancel</a>
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
    <a href="#" class="btn btn-danger " data-dismiss="modal">Close</a>
    <a href="#" id="confirmNewQuestionText" class="btn btn-primary " data-dismiss="modal">Set Question Text</a>
  </div>
  </form>
</div>
<!-- end add new question modal dialogue -->

<?php 
require_once '../bottom_view.php';
?>