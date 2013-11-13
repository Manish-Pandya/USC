<?php 
	require_once '../top_view.php';
?>
<style>
[ng-cloak] {
    display: none;
}
* {
    box-sizing: border-box
}
.minimized > ol > li {
    display:none !important;
}
.minimized > ol {
    border: 0 none transparent;
}
.toggle {
    border: 0 none transparent;
    background:transparent;
    width:2em;
    color:#aaa;
}
button {
    cursor: pointer
}
</style>
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
            <ol id="hazardTree" ui-nested-sortable="{
        listType: 'ol',
        items: 'li',
        doNotClear: true,
        placeholder: 'ui-state-highlight',
        forcePlaceholderSize: true,
        toleranceElement: '> div'
      }" ui-nested-sortable-stop="update($event, $ui)">
                <li ya-tree="child in data.children at ol" ng-class="{minimized:child.minimized}" ng-init="child.minimized=true" item="{{child}}">
                    <div>
                        <button class="toggle" ng-click="toggleMinimized(child)">
                        <ng-switch on="child.children.length > 0">
                        <span ng-switch-when="true">
                            <ng-switch on="child.minimized">
                              <span ng-switch-when="false">&#x25BC;</span><span ng-switch-default>&#x25B6;</span>
                         </span>
                       </ng-switch>
                       </button>
                       
                        <input ng-model="child.label" /><div class="hazarNodeButtons"><a class="btn btn-large btn-info hazardBtn" href="ChecklistHub.php?id={{child.key_id}}"><i class="icon-checkmark"></i>Edit Checklist</a><a class="btn btn-large btn-warning hazardBtn"  node-id="'+node.id+'" data-toggle="modal" href="#hazardModal"><span>!</span>Edit Hazard</a><a data-toggle="modal" href="#hazardModal" ng-click="addChild(child)" class="btn btn-large btn-primary childHazard" node-id="'+node.id+'">Add Child Hazard</a></div>
                        <!--<button ng-click="addChild(child)">+</button>
                        <button ng-click="remove(child)">x</button>-->
                    </div>
                    <ol ng-class="{pregnant:child.children.length}"></ol>
                </li>
            </ol>
        </div>
        <!--
        <div class="shadow">
            <ol>
                <li ya-tree="child in data.children at ol" class="bg{{$depth%6}}" ng-class="{minimized:child.minimized}">
                    <div>
                        <input disabled value="{{child.label}}" /> <em>({{$depth}})</em>

                    </div>
                    <ol ng-class="{pregnant:child.children.length}"></ol>
                </li>
            </ol>
        </div>
    </div>
  -->

<?php 
require_once '../bottom_view.php';
?>