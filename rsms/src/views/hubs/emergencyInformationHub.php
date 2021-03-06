<?php
require_once '../top_view.php';
?>
<script src="../../js/emergencyInfoHub.js"></script>

<span id="buildingHub"  ng-app="emergencyInfo" ng-controller="emergencyInfoController">
    <div class="navbar">
        <ul class="nav pageMenu row-fluid" style="background:#002060;">
            <li class="span12">
                <h2 style="padding: 11px 0 5px 0; font-weight:bold;">
                    <img src="../../img/hazard-icon.png"  style="height:50px" />
                    Emergency Information
                    <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT;?>"><i class="icon-home" style="font-size:40px;"></i></a>
                </h2>
            </li>
        </ul>
        <div style=>&nbsp;</div>
    </div>
    <div class="whiteBg" style="margin-top:-40px; padding-bottom:15px !important;">
        <span id="emergency-info">

            <ul style="font-size:20px; font-weight:bold; list-style:none; margin:20px auto; width:500px; height:112px" class="well center link-list">
                <li style="padding:10px"><a target="_blank" class="btn btn-info btn-large" href="http://wiser.nlm.nih.gov/">WISER</a></li>
                <li style="padding:10px"><a target="_blank" class="btn btn-info btn-large" href="http://cameochemicals.noaa.gov/">CAMEO Chemicals</a></li>
                <li style="padding:10px"><a target="_blank" class="btn btn-info btn-large" href="https://asprtracie.hhs.gov/?source=govdelivery&utm_medium=email&utm_source=govdelivery">ASPR TRACIE</a></li>
            </ul>
            <div class="center" ng-show="!showingHazards">
                <a class="btn btn-info btn-large" ng-click="searchType = 'location'"><h2>Search by Location</h2></a>
                <a class="btn btn-info btn-large" ng-click="searchType = 'pi'"><h2>Search by Principal Investigator</h2></a>
            </div>
            <div class="center" ng-show="showingHazards">
                <a class="btn btn-info left btn-large"  ng-click="resetSearch()"><i class="icon-redo"></i>Search Again</a>
            </div>
            <div class="spacer large"></div>
            <div class="spacer small"></div>
            <h2 class="alert" ng-if="error">{{error}}</h2>
            <div id="buildings">
                <form class="row form-inline" style="margin-left:0" ng-if="!showingHazards">
                    <span ng-if="searchType == 'location'">
                        <label>Building Name or Physical Address:</label>
                            <ui-select ng-if="buildings" ng-show="!PI || selectPI" style="width:350px !important;" ng-model="building.selected" theme="selectize" ng-disabled="disabled" on-select="eif.onSelectBuilding($item)">
                                <ui-select-match placeholder="Select Building">{{$select.selected.Name}}</ui-select-match>
                                <ui-select-choices repeat="building in buildings | propsFilter: {Name: $select.search} | filter: {Is_active: true}">
                                  <div ng-bind-html="building.Name | highlight: $select.search"></div>
                                </ui-select-choices>
                            </ui-select>
                        <input ng-if="!buildings" style="width:350px" type="text" disabled="disabled" placeholder="Getting buildings...">
                           <i ng-if="!buildings" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>

                        <label>Room:</label>
                        <!--
                        <input  style="" type="text" typeahead-on-select='onSelectRoom($item)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="room as room.roomText for room in rooms | filter:{roomText: $viewValue}">-->
                        <ui-select ng-if="rooms" style="width:250px;" ng-model="room.selected" theme="selectize" ng-disabled="disabled" on-select="onSelectRoom($item)">
                            <ui-select-match placeholder="Select Room">{{$select.selected.roomText}}</ui-select-match>
                            <ui-select-choices repeat="room in rooms | propsFilter: {Name: $select.search} | orderBy: 'Name'">
                              <div ng-bind-html="room.roomText | highlight: $select.search"></div>
                            </ui-select-choices>
                        </ui-select>

                        <input ng-if="!rooms && !gettingRooms" placeholder="Select a Building" disabled="disabled">
                        <input ng-if="!rooms && gettingRooms" placeholder="Getting rooms..." disabled="disabled">
                        <i ng-if="!rooms && gettingRooms" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>
                    </span>

                    <span ng-if="searchType == 'pi'">
                        <label>Principal Investigator:</label>
                        <ui-select ng-if="pis" style="width:350px;" ng-model="room.selected" theme="selectize" ng-disabled="disabled" on-select="eif.onSelectPI($item)">
                            <ui-select-match placeholder="Select Principal Investigator">{{$select.selected.Name}}</ui-select-match>
                            <ui-select-choices repeat="pi in pis | propsFilter: {Name: $select.search}">
                              <div ng-bind-html="pi.Name | highlight: $select.search"></div>
                            </ui-select-choices>
                        </ui-select>
                        <input ng-if="!pis" style="width:280px" type="text" disabled="disabled" placeholder="Getting Principal Investigators...">
                           <i ng-if="!pis" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>

                        <label>Location:</label>
                        <ui-select ng-if="rooms && !gettingRoomsForPI" style="width:500px;" ng-model="room.selected" theme="selectize" ng-disabled="disabled" on-select="onSelectRoom($item)">
                            <ui-select-match placeholder="Select Room">{{$select.selected.roomText}}</ui-select-match>
                            <ui-select-choices repeat="room in rooms | propsFilter: {Name: $select.search} | orderBy: 'Name'">
                              <div ng-bind-html="room.roomText | highlight: $select.search"></div>
                            </ui-select-choices>
                        </ui-select>
                        <input ng-if="gettingRoomsForPI" style="width:350px" placeholder="Searching for rooms..." disabled="disabled">
                           <i ng-if="gettingRoomsForPI" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>
                        <input ng-if="!rooms && !gettingRoomsForPI" style="width:350px" placeholder="Select a Principal Investigator" disabled="disabled">
                    </span>

                </form>
                <span ng-if="loading && !error" class="loading" style="margin-left:-140px;">
                     <i class="icon-spinnery-dealie spinner large"></i>
                  <span>Loading...</span>
                </span>
                <h2 class="bold" style="margin:-35px 0 10px" ng-if="room && building">Room {{room.Name}}, {{building.Name}}</h2>

                <h1 class="hazardHeader" ng-if="pisByRoom && showingHazards">EMERGENCY CONTACTS</h1>
                <table ng-if="hazards && pisByRoom && showingHazards" class="table table-striped pisTable table-bordered">
                    <tr class="blue-tr">
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Role</th>
                    </tr>
                    <tr ng-repeat="pi in pisByRoom">
                        <td style="width:26%">{{pi.Name}}</td>
                        <td style="width:17%"><span ng-if="pi.User.Emergency_phone">{{pi.User.Emergency_phone | tel}}</span><span ng-if="!pi.User.Emergency_phone">Unknown</span></td>
                        <td style="width:34%">
                            <ul style="list-style: none;">
                                <li ng-repeat="dept in pi.Departments">{{dept.Name}}</li>
                            </ul>
                        </td>
                        <td style="width:23%">
                            <ul style="list-style: none;">
                                <li ng-repeat="role in pi.User.Roles">{{role.Name}}</li>
                            </ul>
                        </td>
                    </tr>
                    <tr ng-repeat="contact in personnel">
                        <td style="width:26%">{{contact.Name}}</td>
                        <td style="width:17%"><span ng-if="contact.Emergency_phone">{{contact.Emergency_phone | tel}}</span><span ng-if="!contact.Emergency_phone">Unknown</span></td>
                        <td style="width:34%">{{contact.Primary_department.Name}}</td>
                        <td style="width:23%">
                            <ul style="list-style: none;">
                                <li ng-repeat="role in contact.Roles">{{role.Name}}</li>
                            </ul>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

        <span ng-if="showingHazards">
            <h1 class="hazardHeader" ng-if="hazards">LABORATORY HAZARDS</h1>
            <ul class="modalHazardList">
                <li ng-if="hazards" data-ng-repeat="hazard in hazards" class="modalHazard{{hazard.Key_id}}">
                    <h1>{{hazard.Name}}</h1>
                    <h3 style="margin-left:32px" ng-if="eif.noSubHazardsPresent(hazard)">No {{hazard.Name}} hazards.</h3>
                    <ul ng-if="hazard.ActiveSubHazards">
                        <div ng-include="'EmergencyInfoList.php'" ng-init="SubHazards = hazard.ActiveSubHazards"></div>
                    </ul>
                </li>
                <div style='clear:both'>&nbsp;</div>
            </ul>
        </span>
    </span>
</span>
