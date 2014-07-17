<?php
require_once '../top_view.php';
?>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Checklist Hub
        <a style="float:right;margin: 6px 28px 0 30px;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>  
        <a href="hazardhub.php" style="float:right;"><img src="../../img/hazard-icon.png" class="pull-left" style="height:50px; margin:-7px 7px 0 5px" />Return to Hazard Hub</a>
      </h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" ng-app='checklistHub' ng-controller="ChecklistHubController">

<span ng-if="!checklist && !noChecklist" class="loading">
   <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
  Loading Checklist
</span>

	<h1 ng-hide="!checklist" id="currentChecklist">Currently Editing:<br>{{checklist.Name}}<a class="btn btn-mini btn-primary" ng-click="edit = !edit" ng-show="!edit"><i class="icon-pencil"></i></a></h1>
    <h2 ng-if="noChecklist && !checklist" style="">{{hazard.Name}}</h2>
    <a ng-show="!edit && doneLoading"  style="margin-top:5px;" ng-click="edit = true" class="btn btn-primary">Create Checklist</a>
   <form ng-show="edit" style="margin-top:5px;">
    	<input ng-model="checklistCopy.Name" class="span6" placeholder="Enter a name for this checklist."/>
    	<a class="btn btn-success btn-mini" ng-click="saveChecklist(checklistCopy, checklist)"><i class="icon-checkmark"></i>Save Checklist</a>
    	<a class="btn btn-danger btn-mini" ng-show="!noChecklist" ng-click="edit = false"><i class="icon-cancel"></i>Cancel</a>
    	<img ng-show="checklistCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
    </form>

    <hr>
    <span ng-hide="!checklist">
      <h3>This Checklist's Questions:
        <a href="QuestionHub.php#?checklist_id={{checklist.Key_id}}" class="btn btn-success hazardBtn" style="margin-left:10px">
         <i class="icon-plus"></i>Add Question
        </a>
        <Input type="hidden" ng-model="showInactive.Is_active" ng-init="showInactive.Is_active = true">
        <a class="btn" ng-click="showInactive.Is_active = !showInactive.Is_active">
         <span ng-show="!showInactive.Is_active"><i class="icon-minus-3"></i>Hide Inactive Questions</span>
         <span ng-hide="!showInactive.Is_active"><i class="icon-plus-3" ></i>Show Inactive Questions</span>
        </a>
      </h3>
      <br>
      <ul class="questionList sortable" id="sortable"><!--<a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': question.Is_active == true, 'btn-success' :  question.Is_active == false}" ng-click="handleHazardActive(question)" ></a>-->
     		<li ng-repeat="question in checklist.Questions | filter: showInactive"  ng-class="{inactive: question.Is_active == false}">
          <h2>
              {{question.Text}}
              <a href="QuestionHub.php#?id={{question.Key_id}}"class="btn btn-primary btn-mini DeactivateeRow"><i class="icon-pencil"></i></a>
              <a class="btn btn-success btn-mini DeactivateeRow" ng-click="handleQuestionActive(question)" ng-if="!question.Is_active || question.Is_active == 0"><i class="icon-checkmark"></i></a>
              <a class="btn btn-danger btn-mini DeactivateeRow" ng-click="handleQuestionActive(question)" ng-if="question.Is_active"><i class="icon-remove"></i></a>
              <!--<a ng-click="handleQuestionActive(question)"  ng-class="{'btn-danger': question.Is_active, 'btn-success' :  !question.Is_active}" class="btn btn-large"><i ng-class="{ 'icon-check-alt' :  !question.Is_active, 'icon-remove' :  question.Is_active}" ></i><span ng-show="question.Is_active == true">Disable</span><span ng-show="question.Is_active == false">Activate</span></a></div></li>-->
              <img ng-show="question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
            </h2>
          </li>
      </ul>
      <div style="clear:both;"></div>
    </span>
  </div>


  <div style="clear:both;"></div>


<script src="../../js/checklistHub.js"></script>
<?php 
require_once '../bottom_view.php';

?>