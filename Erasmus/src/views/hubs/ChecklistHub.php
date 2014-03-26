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
<div class="container-fluid whitebg" ng-app='checklistHub' ng-controller="ChecklistHubController">
	<h1 id="currentChecklist">Currently Editing:<br>{{checklist.Name}}</h1>
	
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
    <ul class="questionList sortable" id="sortable"><!--<a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': question.Is_active == true, 'btn-success' :  question.Is_active == false}" ng-click="handleHazardActive(question)" ></a>-->
   		<li ng-repeat="question in checklist.Questions" ng-class="{inactive: question.Is_active == false}"><h3><img ng-show="question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>{{question.Text}}</h3><div class="checklistButtons"><a href="QuestionHub.php#?id={{question.Key_id}}" class="btn btn-large btn-primary hazardBtn"><i class="icon-pencil"></i>Edit</a><a ng-click="handleQuestionActive(question)"  ng-class="{'btn-danger': question.Is_active == true, 'btn-success' :  question.Is_active == false}" class="btn btn-large"><i ng-class="{ 'icon-check-alt' :  question.Is_active == false, 'icon-remove' :  question.Is_active == true}" ></i><span ng-show="question.Is_active == true">Disable</span><span ng-show="question.Is_active == false">Activate</span></a></div></li>
    </ul>
    <div style="clear:both;"></div>
  </div>
  <div style="clear:both;"></div>


<script src="../../js/checklistHub.js"></script>
<?php 
require_once '../bottom_view.php';

?>