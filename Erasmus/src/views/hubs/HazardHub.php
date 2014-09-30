<?php 
	require_once '../top_view.php';
?>
<script src="../../js/HazardHub.js"></script>
<span ng-app="hazardHub" ng-controller="TreeController">
<script type="text/ng-template" id="hazard-hub-partial.html">
    <div>
     <div class="leftThings">
        <button class="toggle" ng-click="toggleMinimized(child, false)">
          <span ng-if="child.HasChildren" >
                <span ng-if="!child.minimized">&#x25BC;</span><span ng-if="child.minimized">&#x25B6;</span>
          </span>
         </button>
        <span ng-hide="child.isBeingEdited" class="hazardName">
            <h2><img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>{{child.Name}} | {{child.Order_index}}</h2>
        </span>

        <span ng-show="child.isBeingEdited">
            <img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/><input ng-class="{invalid: child.Invalid == true}" ng-model="hazardCopy.Name" placeholder="New Hazard" ng-click="$event.stopPropagation;" /><a class="btn btn-success" ng-click="saveEditedHazard(child); $event.stopPropagation();"><i class="icon-checkmark"></i><span>Save</span></a><a class="btn btn-danger" ng-click="cancelHazardEdit(child, $index); $event.stopPropagation();"><i class="icon-cancel"></i><span>Cancel</span></a>
        </span>
      </div>
      <div class="hazarNodeButtons" >
        <div class="span1" style="width:40px;" ng-if="child.Parent_hazard_id != 10000">
          <!-- 
              we are four $scopes down from the parent hazard here:
              1. the $scope created by the ng-id above
              2. the $scope created by the ng-include directive
              3. the $scope created by ng-repeat
              4. the $scope created by the ng-if below
              Therefore, the parent hazard is in the $scope $parent.$parent.$parent.$parent
          -->
          <a class="btn btn-mini btn-info upvote" style="margin-bottom:1px;" ng-if="!$first" ng-click="moveHazard($index, $parent.$parent.$parent.$parent.child, 'up', filteredSubHazards)"><i class="icon-arrow-up"></i></a><br>
          <a class="btn btn-mini btn-info upvote" ng-if="!$last" ng-click="moveHazard($index, $parent.$parent.$parent.$parent.child, 'down', filteredSubHazards)"><i class="icon-arrow-down"></i></a>
        </div>
        <a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': child.Is_active == true, 'btn-success' :  child.Is_active == false}" ng-click="handleHazardActive(child)" >
          <i ng-class="{ 'icon-check-alt' :  child.Is_active == false, 'icon-remove' :  child.Is_active == true}" ></i>
          <span ng-show="child.Is_active == true">Disable</span><span ng-show="child.Is_active == false">Activate</span>
        </a>
        <a class="btn btn-large btn-primary hazardBtn" node-id="'+node.id+'" ng-click="editHazard(child)" >
          <i class="icon-pencil"></i>
          <span>Edit Hazard</span>
        </a>
        <a href="" ng-click="addChild(child)" class="btn btn-large btn-warning childHazard hazardBtn" node-id="'+node.id+'">
          <i class="icon-plus-2"></i><span>Add Child</span>
        </a>
          <a class="btn btn-large hazardBtn" ng-class="{'btn-info':child.Checklist, 'btn-primary':!child.Checklist}" href="ChecklistHub.php#?id={{child.Key_id}}">
            <i class="icon-checkmark" style="width:1em;"></i>
            <span style="margin-left:-3px;" ng-if="!child.Checklist">Create Checklist</span><span style="margin-left:-3px;"  ng-if="child.Checklist">Edit Checklist</span>
          </a>
        </div>
    </div>

    <div ng-if="child.loadingChildren">
       <div class="container loading" style="margin-left:50px; margin-top:15px;">
        <img class="" src="../../img/loading.gif"/>
         Loading Subhazards for <span once-text="child.Name"></span>
      </div>                  
    </div>
     
      <ol ng-if="!child.minimized && child.SubHazards"> <!--infinite-scroll infinite-scroll-distance=".5" infinite-scroll-down="setSubs(child, 'addToBottom')" infinite-scroll-bottom-on-screen="setSubs(child,'addToBottom')" infinite-scroll-top-on-screen="setSubs(child,'addToTop')" infinite-scroll-top-off-screen="setSubs(child,'removeFromTop')"-->
        <li ng-repeat="child in (filteredSubHazards = (child.SubHazards | orderBy: [order] | filter: hazardFilter))" id="hazard{{child.Key_id}}" ng-class="{minimized:child.minimized, inactive: child.Is_active == false, lastSub: child.lastSub == true}" ng-init="child.minimized=true" buttonGroup> 
         <span ng-include src="'hazard-hub-partial.html'" autoscroll></span>       
        </li>
      </ol> 
    </li>
</script>
<div class="navbar">
<ul class="nav pageMenu" style="background: #e67e1d;">
	<li class="">
		<img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">Hazard Hub
			<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
		</h2>	
	</li>
</ul>

</div><!-- ui-nested-sortable-stop="update($event, $ui)"
                  ui-nested-sortable-begin="start($event, $ui)"-->

<div class="whitebg" >
	<div>
    <div>
    <div ng-hide="doneLoading" class="container loading" style="margin-left:70px; margin-top:15px;">
      <img class="" src="../../img/loading.gif"/>
      Building Hazard List...
    </div>
    <div class="alert alert-danger" ng-if="error">
      <h1>{{error}}</h1>
    </div>
        <div class="live" ng-hide="!SubHazards.length">
          <select ng-model="hazardFilterSetting.Is_active" style="margin:21px 19px 0;" ng-init="hazardFilterSetting.Is_active = 'active'">
            <option value="active">Display Active Hazards</option>
            <option value="inactive">Display Inactive Hazards</option>
            <option value="both">Display Active & Inactive Hazards</option>
          </select>
          <ol id="hazardTree" style="padding-top:0"> 
            <li ng-repeat="child in SubHazards" id="hazard{{child.Key_id}}" ng-class="{minimized:child.minimized, inactive: child.Is_active == false, lastSub: child.lastSub == true}" ng-init="child.minimized=true"  buttonGroup>
              <span ng-include src="'hazard-hub-partial.html'" autoscroll></span>
            </li>
          </ol>
        </div>
</span>
<?php 
require_once '../bottom_view.php';
?>