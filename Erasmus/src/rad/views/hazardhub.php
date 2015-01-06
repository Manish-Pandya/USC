<?php require_once 'top_view.php' ?>
<span ng-app="00RsmsAngularOrmApp" ng-controller="HazardHubCtrl">
<!--application state indicators -->
<div ng-if="error" class="alert alert-danger"><h1>{{error}}</h1></div>
<div cg-busy="{promise:Hazard,message:'Loading Hazards', backdrop:true}"></div>
<div cg-busy="{promise:HazardsBusy,message:'Loading SubHazards', backdrop:true}"></div>
<div cg-busy="{promise:directiveParsing,message:'Loading Hazards', backdrop:true}"></div>
<div cg-busy="{promise:hazardPromise,message:'Loading Hazards', backdrop:true}"></div>
<div cg-busy="{promise:HazardSaving,message:'Saving Hazard', backdrop:true}"></div>

<script type="text/ng-template" id="hazard-hub-partial.html">

	<div class="leftThings">
	    <button class="toggle" ng-click="hazard.getSubHazards(); hazard.UnMinimized = !hazard.UnMinimized">
	      <span ng-if="hazard.HasChildren" >
	            <span ng-if="hazard.UnMinimized">&#x25BC;</span><span ng-if="hazard.UnMinimized != true">&#x25B6;</span>
	      </span>
	     </button>

	    <span ng-if="hazard.Edit != true" class="hazardName">
	        <h2>{{hazard.getName()}}</h2>
	    </span>

	    <span ng-if="hazard.Edit">
	        <input ng-class="{invalid: hazard.Invalid == true}" ng-model="hazard.Name" placeholder="New Hazard" ng-click="$event.stopPropagation;" />
	        <a class="btn btn-success" ng-click="af.saveObject( hazard ); $event.stopPropagation();">
        		<i class="icon-checkmark"></i><span>Save</span>
	        </a>
	        <a class="btn btn-danger" ng-click="af.cancelEdit(hazard)">
        		<i class="icon-cancel"></i><span>Cancel</span>
        	</a>
	    </span>
    </div>

	<div class="hazarNodeButtons" >
	    <div class="span1" style="width:40px;" ng-if="hazard.Parent_hazard_id != 10000">
	      <a class="btn btn-mini btn-info upvote" style="margin-bottom:1px;" ng-if="!$first" ng-click="af.moveHazard($index, hazard, 'up', filteredSubHazards)"><i class="icon-arrow-up"></i></a><br>
	      <a class="btn btn-mini btn-info upvote" ng-if="!$last" ng-click="af.moveHazard($index, hazard, 'down', filteredSubHazards)"><i class="icon-arrow-down"></i></a>
	    </div>
	    <a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': hazard.Is_active == true, 'btn-success' :  hazard.Is_active == false}" ng-click="af.setObjectActiveState( hazard )">
	      <i ng-class="{'icon-check-alt' :  hazard.Is_active == false, 'icon-remove' :  hazard.Is_active == true}" ></i>
	      <span ng-show="hazard.Is_active == true">Disable</span><span ng-show="hazard.Is_active == false">Activate</span>
	    </a>
	    <a class="btn btn-large btn-primary hazardBtn" node-id="'+node.id+'" ng-click="af.copy(hazard)">
	      <i class="icon-pencil"></i>
	      <span>Edit Hazard</span>
	    </a>
	    <a href="" ng-click="addhazard(hazard)" class="btn btn-large btn-warning hazardHazard hazardBtn" node-id="'+node.id+'">
	      <i class="icon-plus-2"></i><span>Add hazard</span>
	    </a>
	      <a class="btn btn-large hazardBtn" ng-class="{'btn-info':hazard.Checklist, 'btn-primary':!hazard.Checklist}" href="ChecklistHub.php#?id={{hazard.Key_id}}">
	        <i class="icon-checkmark" style="width:1em;"></i>
	        <span style="margin-left:-3px;" ng-if="!hazard.Checklist">Create Checklist</span><span style="margin-left:-3px;"  ng-if="hazard.Checklist">Edit Checklist</span>
	      </a>
	    </div>
	</div>
	<ul ng-if="hazard.SubHazards.length && hazard.UnMinimized">
		<li ng-repeat="hazard in (filteredSubHazards = (hazard.SubHazards | orderBy: [order] | filter: hazardFilter))" hazard-hub-li>
			<span ng-include src="'hazard-hub-partial.html'"></span>
		</li>
	</ul>

</script>	

<select ng-model="hazardFilterSetting.Is_active" style="margin:21px 19px 0;" ng-show="hazards" ng-init="hazardFilterSetting.Is_active = 'active'">
	<option value="active">Display Active Hazards</option>
	<option value="inactive">Display Inactive Hazards</option>
	<option value="both">Display Active & Inactive Hazards</option>
</select>

<ul id="hazardTree">
	<li ng-repeat="hazard in hazards | orderBy : [name]" hazard-hub-li>
		<span ng-include src="'hazard-hub-partial.html'"></span>
	</li>
</ul>
</span>
<?php require_once "bottom_view.php" ?>