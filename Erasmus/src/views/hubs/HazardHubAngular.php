<?php 
	require_once '../top_view.php';
?>

<script src="../../js/HazardHub.js"></script>
<div class="navbar">
<ul class="nav pageMenu" style="background: #e67e1d;">
	<li class="span3">
		<img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">Hazard Hub</h2>	
	</li>
</ul>
</div>
<div class="whitebg" >
	<div ng-app="hazardHub" ng-cloak>
    
    <div ng-controller="TreeController">
        <div>
            <button ng-click="addChild(data)">+ New</button>
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
                  ui-nested-sortable-stop="update($event, $ui)"
            >  
     
                <li ya-tree="child in SubHazards at ol" ng-class="{minimized:child.minimized}" ng-init="child.minimized=true" item="{{child}}">
                    <div>
                        <!--<pre>{{child | json}}</pre>-->
                       <div class="leftThings">

                                <button class="toggle" ng-click="toggleMinimized(child)">
                                <ng-switch on="child.SubHazards.length > 0">
                                <span ng-switch-when="true">
                                    <ng-switch on="child.minimized">
                                      <span ng-switch-when="false">&#x25BC;</span><span ng-switch-default>&#x25B6;</span>
                                 </span>
                                </ng-switch>
                                 </button>
                               
                                <span ng-hide="child.isBeingEdited">
                                    <h2>{{child.Name}}</h2>
                                </span>

                                <span ng-show="child.isBeingEdited">
                                    <input ng-model="hazardCopy.Name" placeholder="New Hazard" ng-click="$event.stopPropagation;" /><a class="btn btn-success" ng-click="saveEditedHazard(child); $event.stopPropagation();"><i class="icon-checkmark"></i>Save</a><a class="btn btn-danger" ng-click="cancelHazardEdit(child, $index); $event.stopPropagation();"><i class="icon-cancel"></i>Cancel</a>
                                </span>
                          

                        </div>
                        <div class="hazarNodeButtons"><a class="btn btn-large btn-primary hazardBtn" node-id="'+node.id+'" ng-click="editHazard(child)" ><i class="icon-pencil"></i>Edit Hazard</a><a data-toggle="modal" href="#hazardModal" ng-click="addChild(child)" class="btn btn-large btn-success childHazard" node-id="'+node.id+'"><i class="icon-plus"></i>Add Child Hazard</a><a class="btn btn-large btn-info hazardBtn" href="ChecklistHub.php?id={{child.key_id}}"><i class="icon-checkmark" style="width:1em;"></i>Edit Checklist</a></div>
                        <!--<button ng-click="addChild(child)">+</button>
                        
                        <button ng-click="remove(child)">x</button>-->
                    </div>
                    <ol ng-class="{pregnant:child.children.length}"></ol>
                </li>
            </ol>

            <pre>{{SubHazards | json}}</pre>


        </div>
<?php 
require_once '../bottom_view.php';
?>