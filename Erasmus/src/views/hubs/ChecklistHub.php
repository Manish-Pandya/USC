<?php
require_once '../top_view.php';
?>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Checklist Hub
        <a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>  
      </h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" ng-app='checklistHub' ng-controller="ChecklistHubController">

<span ng-if="!checklist && !noChecklist" class="loading">
   <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
  Loading Checklist
</span>

<form class="form" style="margin-top:10px;">
      <div class="control-group row">
       <label class="control-label" for="name"><h3>Choose a different hazard.</h3></label>
       <div class="controls">
       <span ng-show="!hazards">
         <input class="span4" style="background:white;border-color:#999"  type="text"  placeholder="Getting Hazards..." disabled="disabled">
       	<img class="" style="height:23px; margin: -11px 0 0 -37px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
       </span>
       <span ng-hide="!hazards">
       	<input style="" class="span4" typeahead-on-select='onSelectHazard($item, $model, $label)' type="text" ng-model="$viewValue" placeholder="Select Hazard" typeahead="hazard as hazard.Name for hazard in hazards | filter:$viewValue">
       </span>
      </div>
      </div>
    </form>
	<h1 ng-hide="!checklist" id="currentChecklist">Currently Editing:<br>{{checklist.Name}}<a class="btn btn-mini btn-primary" ng-click="edit = !edit" ng-show="!edit"><i class="icon-pencil"></i></a></h1>
    <h2 ng-show="noChecklist">No checklist has been created for the hazard {{hazard.Name}} yet.</h2>
    <form ng-show="edit">
    	<input ng-model="checklistCopy.Name" class="span6" placeholder="Enter a name for this checklist."/>
    	<a class="btn btn-success btn-mini" ng-click="saveChecklist(checklistCopy, checklist)"><i class="icon-checkmark"></i>Save Checklist</a>
    	<a class="btn btn-danger btn-mini" ng-show="!noChecklist" ng-click="edit = false"><i class="icon-cancel"></i>Cancel</a>
    	<img ng-show="checklistCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
    </form>

    <hr>
    <span ng-hide="!checklist">
      <h3>This Checklist's Questions:<a href="QuestionHub.php#?checklist_id={{checklist.Key_id}}" class="btn btn-success hazardBtn" style="margin-left:10px"><i class="icon-plus"></i>Add Question</a></h3>
      <div id="showHideQuestions" class="btn btn-primary btn-large" style="margin:10px 0">Hide Disabled Questions</div>
      <ul class="questionList sortable" id="sortable"><!--<a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': question.Is_active == true, 'btn-success' :  question.Is_active == false}" ng-click="handleHazardActive(question)" ></a>-->
     		<li ng-repeat="question in checklist.Questions" ng-class="{inactive: question.Is_active == false}">
          <h3>
            <img ng-show="question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>{{question.Text}}
            </h3><div class="checklistButtons">
            <a href="QuestionHub.php#?id={{question.Key_id}}" class="btn btn-large btn-primary hazardBtn"><i class="icon-pencil"></i>Edit</a><a ng-click="handleQuestionActive(question)"  ng-class="{'btn-danger': question.Is_active, 'btn-success' :  !question.Is_active}" class="btn btn-large"><i ng-class="{ 'icon-check-alt' :  !question.Is_active, 'icon-remove' :  question.Is_active}" ></i><span ng-show="question.Is_active == true">Disable</span><span ng-show="question.Is_active == false">Activate</span></a></div></li>
      </ul>
      <div style="clear:both;"></div>
    </span>
  </div>


  <div style="clear:both;"></div>


<script src="../../js/checklistHub.js"></script>
<?php 
require_once '../bottom_view.php';

?>