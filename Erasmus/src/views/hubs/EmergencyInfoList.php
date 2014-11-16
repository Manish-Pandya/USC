<li ng-repeat="hazard in SubHazards" ng-if="hazard.IsPresent">
	<!--<pre>{{hazard | json}}</pre>-->
	<h2 ng-if="hazard.IsPresent">{{hazard.Name}}</h2>
	<ul ng-if="hazard.ActiveSubHazards" >
		<div ng-init="SubHazards = hazard.ActiveSubHazards;" ng-include="'EmergencyInfoList.php'"></div>
	</ul>
</li>
