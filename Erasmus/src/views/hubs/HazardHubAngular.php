<?php 
	require_once '../top_view.php';
?>
<script src="../../js/HazardHub.js"></script>
<div class="navbar">
<ul class="nav pageMenu" style="background: #e67e1d;">
	<li class="">
		<img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">Hazard Hub
			<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
		</h2>	
	</li>
</ul>

</div><!-- ui-nested-sortable-stop="update($event, $ui)"
                  ui-nested-sortable-begin="start($event, $ui)"-->

<div class="whitebg" >

	<div ng-app="hazardHub" ng-cloak>
   
    <div ng-controller="TreeController">
     <div ng-hide="doneLoading" class="container loading" style="margin-left:70px; margin-top:15px;">
      <img class="" src="../../img/loading.gif"/>
      Building Hazard List...
    </div>
        <div class="live">
            <ol id="hazardTree" 
              ui-nested-sortable="{
                listType: 'ol',
                items: 'li',
                doNotClear: true,
                placeholder: 'ui-state-highlight',
                forcePlaceholderSize: true,
                toleranceElement: '> div'
              }" 
            >  
     
                <li ya-tree="child in SubHazards at ol" ng-class="{minimized:child.minimized, inactive: child.Is_active == false}" ng-init="child.minimized=true" item="{{child}}" buttonGroup>
                    <div>
                       <div class="leftThings">
                                <button class="toggle" ng-click="toggleMinimized(child, false)">
                                  <span ng-if="child.HasChildren" >  
                                        <span ng-if="!child.minimized">&#x25BC;</span><span ng-if="child.minimized">&#x25B6;</span>
                                  </span>
                                 </button>
                                <span ng-hide="child.isBeingEdited">
                                    <h2><img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>{{child.Name}}</h2>
                                </span>

                                <span ng-show="child.isBeingEdited">
                                    <img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/><input ng-class="{invalid: child.Invalid == true}" ng-model="child.Name" placeholder="New Hazard" ng-click="$event.stopPropagation;" /><a class="btn btn-success" ng-click="saveEditedHazard(child); $event.stopPropagation();"><i class="icon-checkmark"></i><span>Save</span></a><a class="btn btn-danger" ng-click="cancelHazardEdit(child, $index); $event.stopPropagation();"><i class="icon-cancel"></i><span>Cancel</span></a>
                                </span>
                        </div>
                        <div class="hazarNodeButtons" ><a class="btn btn-large hazardBtn" node-id="'+node.id+'" ng-class="{'btn-danger': child.Is_active == true, 'btn-success' :  child.Is_active == false}" ng-click="handleHazardActive(child)" ><i ng-class="{ 'icon-check-alt' :  child.Is_active == false, 'icon-remove' :  child.Is_active == true}" ></i><span ng-show="child.Is_active == true">Disable</span><span ng-show="child.Is_active == false">Activate</span></a><a class="btn btn-large btn-primary hazardBtn" node-id="'+node.id+'" ng-click="editHazard(child)" ><i class="icon-pencil"></i><span>Edit</span></a><a href="#hazardModal" ng-click="addChild(child)" class="btn btn-large btn-warning childHazard hazardBtn" node-id="'+node.id+'"><i class="icon-plus-2"></i><span>Add Child</span></a><a class="btn btn-large hazardBtn" ng-class="{'btn-info':child.Checklist, 'btn-primary':!child.Checklist}" href="ChecklistHub.php#?id={{child.Key_id}}"><i class="icon-checkmark" style="width:1em;"></i><span style="margin-left:-3px;" ng-if="!child.Checklist">Create</span><span style="margin-left:-3px;"  ng-if="child.Checklist">Edit</span></a></div>
                    </div>
                    <div ng-if="child.loadingChildren">
                       <div class="container loading" style="margin-left:50px; margin-top:15px;">
                        <img class="" src="../../img/loading.gif"/>
                         Loading Subhazards for {{child.Name}}...
                      </div>                  
                    </div>
                    <ol ng-class="{pregnant:child.children.length,notTheOpenedOne:child != openedHazard}" infinite-scroll infinite-scroll-distance=".5" infinite-scroll-down="setSubs(child, 'addToBottom')" infinite-scroll-bottom-on-screen="setSubs(child,'addToBottom')" infinite-scroll-top-on-screen="setSubs(child,'addToTop')" infinite-scroll-top-off-screen="setSubs(child,'removeFromTop')"></ol>
                </li>
            </ol>
        </div>
<?php 
require_once '../bottom_view.php';
?>