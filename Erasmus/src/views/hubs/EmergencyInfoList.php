<li ng-repeat="hazard in SubHazards">
	<h2 ng-if="hazard.IsPresent">{{hazard.Name}}</h2>
	<ul ng-if="hazard.ActiveSubHazards" >
		<div ng-init="SubHazards = hazard.ActiveSubHazards;" ng-include="'EmergencyInfoList.php'"></div>
	</ul>
</li>
