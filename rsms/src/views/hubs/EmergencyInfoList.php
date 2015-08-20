<script type="text/ng-template" id="HazardEmergencyInfoList.html">
    <li ng-repeat="hazard in SubHazards | filter: {Is_equipment: false}" ng-if="hazard.IsPresent">
        <!--<pre>{{hazard | json}}</pre>-->
        <h2 ng-if="hazard.IsPresent">{{hazard.Name}}</h2>
        <ul ng-if="hazard.ActiveSubHazards" >
            <div ng-init="SubHazards = hazard.ActiveSubHazards;" ng-include="'HazardEmergencyInfoList.html'"></div>
        </ul>
    </li>
</script>
<li ng-repeat="hazard in SubHazards | filter: {Is_equipment: false}" ng-if="hazard.IsPresent">
    <h2 ng-if="hazard.IsPresent">{{hazard.Name}}</h2>
    <ul ng-if="hazard.ActiveSubHazards" >
        <div ng-init="SubHazards = hazard.ActiveSubHazards;" ng-include="'HazardEmergencyInfoList.html'"></div>
    </ul>
</li>
<h1 ng-show="equipment.length" style="margin-top:15px;margin-left:-24px;"><span ng-if="hazard.Name == 'Biological Safety' || hazard.Name == 'Chemical/Physical Safety' || hazard.Name == 'Chemical and Physical Safety'">Safety Equipment</span><span ng-if="hazard.Name == 'Radiation Safety'">Equipment/Device</span></h1>
<script type="text/ng-template" id="EquiptmentEmergencyInfoList.html">
    <li ng-repeat="hazard in (equipment = (SubHazards | filter: {Is_equipment: true}))" ng-if="hazard.IsPresent">
        <!--<pre>{{hazard | json}}</pre>-->
        <h2 ng-if="hazard.IsPresent">{{hazard.Name}}</h2>
        <ul ng-if="hazard.ActiveSubHazards">
            <div ng-init="SubHazards = hazard.ActiveSubHazards;" ng-include="'EquiptmentEmergencyInfoList.html'"></div>
        </ul>
    </li>
</script>
 <li ng-repeat="hazard in (equipment = (SubHazards | filter: {Is_equipment: true} | filter: {IsPresent:true}))" ng-if="hazard.IsPresent">
    <h2>{{hazard.Name}}</h2>
    <ul ng-if="hazard.ActiveSubHazards" >
        <div ng-init="SubHazards = hazard.ActiveSubHazards;" ng-include="'EquiptmentEmergencyInfoList.html'"></div>
    </ul>
</li>
