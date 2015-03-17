<?php
require_once '../top_view.php';
?>
<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Checklist Hub
        <a style="float:right;margin: 6px 35px 0 9px;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>  
        <a href="HazardHub.php" style="float:right; font-size:20px;"><i class="icon-arrow-left-2" style="font-size: 20px; margin: 7px 12px 0 0;"></i>Hazard Hub</a>
      </h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" ng-app='checklistHub' ng-controller="ChecklistHubController">
<span class="spacer"></span>
<span ng-if="!checklist && !noChecklist" class="loading">
  <i class="icon-spinnery-dealie spinner large"></i>
  <span>Loading Checklist</span>
</span>

	<h1 ng-hide="!checklist" id="currentChecklist"><span class="underline">Checklist Title:</span>  {{checklist.Name}}<a class="btn btn-primary" style="margin-left:10px;" ng-click="edit = !edit" ng-show="!edit" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i>Edit</a></h1>
    <h2 ng-if="noChecklist && !checklist" style="">{{hazard.Name}}</h2>
    <a ng-if="!edit && doneLoading && !checklist"  style="margin-top:5px;" ng-click="editChecklist()" class="btn btn-primary">Create Checklist</a>
   <form ng-show="edit" style="margin-top:5px;">
    	<input ng-model="checklistCopy.Name" class="span6" placeholder="Enter a name for this checklist."/>
    	<a class="btn btn-success " ng-click="saveChecklist(checklistCopy, checklist)"><i class="icon-checkmark"></i>Save Checklist</a>
    	<a class="btn btn-danger " ng-show="!noChecklist" ng-click="edit = false"><i class="icon-cancel"></i>Cancel</a>
    	<img ng-show="checklistCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
    </form>
    <span ng-hide="!checklist">
        <span class="spacer"></span>
        <a ng-hide="!showInactive.Is_active" href="QuestionHub.php#?checklist_id={{checklist.Key_id}}" class="btn btn-success left btn-large">
         <i class="icon-plus-2"></i>Add Question
        </a>
        <Input type="hidden" ng-model="showInactive.Is_active" ng-init="showInactive.Is_active = true">
        <a class="btn btn-large" ng-class="{'btn-danger':showInactive.Is_active,'btn-success':!showInactive.Is_active}"ng-click="showInactive.Is_active = !showInactive.Is_active">
         <span ng-show="!showInactive.Is_active">Show Active Questions</span>
         <span ng-hide="!showInactive.Is_active">Show Inactive Questions</span>
        </a>
      </h3>
      <span class="spacer"></span>
      <table class="table table-striped table-hover table-bordered large" id="sortable"><!--<a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': question.Is_active == true, 'btn-success' :  question.Is_active == false}" ng-click="handleHazardActive(question)" ></a>-->
     		
        <tr class="blue-tr">
          <th>Checklist Questions</th>
          <th>Edit</th>
        </tr>

        <tr ng-repeat="question in checklist.Questions | filter: showInactive"  ng-class="{inactive: question.Is_active == false}">
          <td>
            <h2 once-text="question.Text"></h2>
          </td>

          <td>
              <a href="QuestionHub.php#?id={{question.Key_id}}"class="btn btn-primary" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a>
              <a class="btn btn-success" ng-click="handleQuestionActive(question)" ng-if="!question.Is_active || question.Is_active == 0"><i class="icon-checkmark"></i></a>
              <a class="btn btn-danger" ng-click="handleQuestionActive(question)" ng-if="question.Is_active"alt="Deactivate" title="Deactivate"><i class="icon-remove"></i></a>
              <img ng-show="question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
              <!--<a ng-click="handleQuestionActive(question)"  ng-class="{'btn-danger': question.Is_active, 'btn-success' :  !question.Is_active}" class="btn btn-large"><i ng-class="{ 'icon-check-alt' :  !question.Is_active, 'icon-remove' :  question.Is_active}" ></i><span ng-show="question.Is_active == true">Disable</span><span ng-show="question.Is_active == false">Activate</span></a></div></li>-->
          </td>
        </tr>
      </table>
      <div style="clear:both;"></div>
    </span>
  </div>


  <div style="clear:both;"></div>


<script src="../../js/checklistHub.js"></script>
<?php 
require_once '../bottom_view.php';

?>