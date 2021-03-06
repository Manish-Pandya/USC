<?php
require_once '../top_view.php';
require_once '../../includes/modules/lab-inspection/js/room-type-constants.js.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.16.6/lodash.min.js"></script>
<script src="../../js/manageInspections.js"></script>
<div ng-app="manageInspections" ng-controller="manageInspectionCtrl" class="hub-theme-red" ng-cloak>

    <div class="alert savingBox" ng-if="saving">
    <h1>
        <i style="color:white" class="icon-spinnery-dealie spinner large"></i>
        <span style="margin-left: 13px;display: inline-block;">Scheduling Inspection...</span>
    </h1>
    </div>
    
    <div class="alert savingBox" ng-if="filtering"><!---->
    <h1>
        <i class="icon-clock large" style="margin-bottom: -30px;
        font-size: 66px;
        color: white;
        width: 59px;
        margin-top: 20px;"></i>
        <span style="margin-left: 13px;display: inline-block;">Filtering Inspections...</span>
    </h1>
    </div>

    <hub-banner-nav
        hub-title="Manage Inspections"
        hub-image="../../img/manage-inspections-icon.png">
        <li>
        </li>
    </hub-banner-nav>

    <div class="hub-toolbar">
        <div class="loading" ng-if="loading" style="z-index:9999">
            <i class="icon-spinnery-dealie spinner large"></i>
            <span>Loading...</span>
        </div>
    </div>

    <table class="table table-striped table-bordered userList manage-inspections-table sticky-headers"
        sticky-headers watch="filtered.length" ng-if="dtos.length">
        <thead>
            <tr>
                <th colspan="8" class="theme-main-element">
                    <div class="filter-holder">
                        <div>
                            <label>Inspection Year:</label>
                            <select ng-model="yearHolder.selectedYear" ng-change="selectYear()" ng-options="year as year.Name for year in yearHolder.years">
                                <option value="">-- select year --</option>
                            </select>
                        </div>
                        <div>
                            <label>Inspection Type:</label>
                            <select ng-model="search.type" ng-options="v as v for (k, v) in constants.INSPECTION.TYPE" ng-change="genericFilter()">
                                <option value="">All Types</option>
                            </select>
                        </div>
                        <h2 style="flex-grow: 1; text-align: right;">
                            <span class="underline">{{filtered.length || 0}}</span>
                            <span>Inspections Displayed</span>
                        </h2>
                    </div>
                </th>
            </tr>
            <tr>
                <th ng-class="{'theme-underlight-element': search.pi}">
                    Investigator<br>
                    <input class="span2" ng-model="search.pi" placeholder="Filter by PI" blur-it="genericFilter()" /><i ng-if="search.pi" class="icon-magnifying-glass" ng-click="genericFilter()"></i>
                </th>
                <th ng-class="{'theme-underlight-element': search.campus}">
                    Campus<br>
                    <input class="span2" ng-model="search.campus" placeholder="Filter by Campus " blur-it="genericFilter()" /><i ng-if="search.campus" class="icon-magnifying-glass" ng-click="genericFilter()"></i>
                 </th>
                <th ng-class="{'theme-underlight-element': search.building}">
                    Building<br>
                    <input class="span2" ng-model="search.building" placeholder="Filter by Building" blur-it="genericFilter()" /><i ng-if="search.building" class="icon-magnifying-glass" ng-click="genericFilter()"></i>
                </th>
                <th ng-class="{'theme-underlight-element': search.room_type}">
                    Lab Room(s)
                    <select ng-model="search.room_type" style="margin-bottom:0; max-width:150px;" ng-options="type.name as type.label for type in roomTypes = (constants.ROOM_TYPE | toArray | filter:{inspectable:true})" ng-change="genericFilter()">
                        <option value="">Select room type</option>
                    </select>
                </th>
                <th ng-class="{'theme-underlight-element': search.date}">
                    Month Scheduled<br>
                    <input class="span2" ng-model="search.date" placeholder="Filter by Date" blur-it="genericFilter()" /><i ng-if="search.date" class="icon-magnifying-glass" ng-click="genericFilter()"></i><i ng-if="search.date" class="icon-magnifying-glass" ng-click="genericFilter()"></i>
                </th>
                <th ng-class="{'theme-underlight-element': search.inspector}">
                    EHS Inspector<br>
                    <input class="span2" ng-model="search.inspector" placeholder="Filter by Inspector" blur-it="genericFilter()" /><i ng-if="search.inspector" class="icon-magnifying-glass" ng-click="genericFilter()"></i>

                </th>
                <th ng-class="{'theme-underlight-element': search.status}">
                    Status<br>
                    <select ng-model="search.status" style="margin-bottom:0; max-width:185px;" ng-options="status as status for status in statuses = (constants.INSPECTION.STATUS | toArray | filterableInspectionStatus)" ng-change="genericFilter()">
                        <option value="">Select a status</option>
                    </select>
                </th>
                <th ng-class="{'theme-underlight-element': search.hazards}">
                    Lab Hazards
                    <select ng-model="search.hazards" ng-options="v.value as v.label for v in constants.ROOM_HAZARDS" style="margin-bottom: 0;width: 142px;" ng-change="genericFilter()">
                        <option value="">Select</option>
                    </select>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="dto in filtered" ng-class="{inactive: dto.Inspections.Status.indexOf(constants.INSPECTION.STATUS.OVERDUE_CAP)>-1 || dto.Inspections.Status.indexOf(constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION)>-1 ,'pending':dto.Inspections.Status==constants.INSPECTION.STATUS.CLOSED_OUT && !dto.Inspections.Cap_complete,'complete':dto.Inspections.Status==constants.INSPECTION.STATUS.CLOSED_OUT && dto.Inspections.Cap_complete}" repeat-done="layoutDone()">
                <td style="width:8.5%"><span once-text="dto.Pi_name"></span></td>
                <td class="triple-inner" style="width:8%;vertical-align:top !important;">
                    <div ng-repeat="campus in dto.Campuses" style="{{getMargin(campus)}}">{{campus.Campus_name}}</div>
                </td>
                <td class="triple-inner-inner" style="width:8.5%;vertical-align:top !important;">
                    <div ng-repeat="campus in dto.Campuses">
                        <div style="{{getMargin(building)}}" ng-repeat="building in campus.Buildings">{{building.Building_name}}</div>
                    </div>
                </td>
                <td class="triple-inner-inner" style="width:8%">
                    <div ng-repeat="campus in dto.Campuses">
                        <div ng-repeat="building in campus.Buildings" style="margin-bottom:10px">
                            <div ng-class="{'red':room.notInspected}" ng-repeat="room in building.Rooms | orderBy: convenienceMethods.sortAlphaNum('Name')"
                                style="display:flex;">
                                <span class="italic grayed-out" style="padding-right: 5px;">
                                    <room-type-icon room-type-name="room.Room_type"></room-type-icon>
                                </span>
                                <span>{{room.Name}}</span>
                            </div>
                        </div>
                    </div>
                </td>
                <!--
                <td style="width:24.5%" class="triple">
                    <table>
                        <tr ng-repeat="campus in dto.Campuses">
                            <td class="triple-inner">{{campus.Campus_name}}</td>
                            <td class="triple-inner-2">
                                <table>
                                    <tr ng-repeat="building in campus.Buildings">
                                        <td class="triple-inner-inner">{{building.Building_name}}</td>
                                        <td class="triple-inner-inner-2">
                                            <div ng-class="{'red':room.notInspected}" ng-repeat="room in building.Rooms">{{room.Name}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
                    -->
                <td style="width:6.5%">
                    <span ng-if="dto.Inspection_id">
                        <span ng-if="dto.Inspections.Date_started">
                            <span ng-repeat="month in months" ng-if="month.val==dto.Inspections.Schedule_month">{{month.string}}</span>
                        </span>
                        <select ng-if="!dto.Inspections.Date_started && rbf.getHasPermission([ R[constants.ROLE.NAME.ADMIN],  R[constants.ROLE.NAME.RADIATION_ADMIN]])" ng-model="dto.Schedule_month" ng-change="mif.scheduleInspection( dto, yearHolder.selectedYear )">
                            <option value="">-- select month --</option>
                            <option ng-selected="month.val==dto.Inspections.Schedule_month" ng-repeat="month in months" value="{{month.val}}">{{month.string}}</option>
                        </select>
                    </span>

                    <select ng-if="!dto.Inspection_id && rbf.getHasPermission([ R[constants.ROLE.NAME.ADMIN],  R[constants.ROLE.NAME.RADIATION_ADMIN]])" ng-model="dto.Schedule_month" ng-change="mif.scheduleInspection( dto, yearHolder.selectedYear )" ng-options="month.val as month.string for month in months">
                        <option value="">-- select month --</option>
                    </select>

                </td>
                <td style="width:7.5%">
                    <div ng-if="!dto.Inspections.Inspectors.length">
                        {{constants.INSPECTION.SCHEDULE_STATUS.NOT_ASSIGNED}}<br>
                    </div>
                    <select ng-model="dto.selectedInspector" ng-if="rbf.getHasPermission([ R[constants.ROLE.NAME.ADMIN],  R[constants.ROLE.NAME.RADIATION_ADMIN]]) && (!dto.Inspections || !dto.Inspections.Inspectors.length || dto.replaceInspector)" ng-change="mif.scheduleInspection( dto, yearHolder.selectedYear, dto.selectedInspector )">
                        <option value="">-- Select inspector --</option>
                        <option ng-repeat="inspector in inspectors" value="{{$index}}">{{inspector.Name}}</option>
                    </select>

                    <ul ng-if="dto.Inspections.Inspectors">
                        <li ng-repeat="inspector in dto.Inspections.Inspectors">
                            <span ng-if="!inspector.edit" once-text="inspector.Name"></span>
                            <span ng-if="inspector.edit && dtoCopy && rbf.getHasPermission([ R[constants.ROLE.NAME.ADMIN],  R[constants.ROLE.NAME.RADIATION_ADMIN]])">
                                <select ng-options="innerInspector as innerInspector.Name for innerInspector in inspectors| onlyUnselected:dto.Inspections.Inspectors" ng-model="dtoCopy.replacementInspector" ng-change="mif.replaceInspector( dto, yearHolder.selectedYear, $index, dtoCopy.replacementInspector, inspector)">
                                    <option value="" style="display:none" selected>Select an Inspector</option>
                                </select>
                                <i class="icon-cancel-2 danger" style="margin-top:-1px;" ng-click="mif.cancelEditInspector(inspector)"></i>
                            </span>
                            <span ng-if="!inspector.edit && rbf.getHasPermission([ R[constants.ROLE.NAME.ADMIN],  R[constants.ROLE.NAME.RADIATION_ADMIN]])">
                                <i class="icon-pencil primary" title="Edit" ng-click="mif.editInspector(inspector, dto)"></i>
                                <i class="icon-remove danger" title="Remove" ng-click="mif.removeInspector(dto, yearHolder.selectedYear, inspector)"></i>
                                <i ng-if="$last" title="Add" alt="Add" class="icon-plus-2 success" ng-click="dto.addInspector = true"></i></a>
                            </span>
                        </li>
                        <li ng-if="dto.addInspector && rbf.getHasPermission([ R[constants.ROLE.NAME.ADMIN],  R[constants.ROLE.NAME.RADIATION_ADMIN]])">
                            <select ng-options="innerInspector as innerInspector.Name for innerInspector in inspectors| onlyUnselected:dto.Inspections.Inspectors" ng-model="dto.addedInspector" ng-change="mif.addInspector( dto, yearHolder.selectedYear, dto.addedInspector )">
                                <option value="" style="display:none" selected>Add an Inspector</option>
                            </select>
                            <i class="icon-cancel-2 danger" ng-click="dto.addInspector = false"></i>
                        </li>
                    </ul>

                </td>
                <td style="width:10.5%">
                    <i class="inspection-schedule-marker" ng-class="{'existing': dto.Inspection_id, 'expected': !dto.Inspection_id }"></i>
                    <span ng-if="!dto.Inspection_id">{{constants.INSPECTION.STATUS.NOT_SCHEDULED}}</span>
                    <span ng-if="dto.Inspections.Status">
                        <span once-text="dto.Inspections.Status"></span>
                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.SCHEDULED">
                            <span ng-init="dto = getRoomUrlString(dto)"></span>
                            <br>Scheduled ({{dto.Inspections.Schedule_month | getMonthName}})
                            <br>
                            <a target="_blank" style="margin:  5px 0;" class="btn btn-danger left" href="../../hazard-inventory/#?pi={{dto.Pi_key_id}}&{{dto.roomUrlParam}}"><img src="../../img/hazard-icon.png" />Hazard Inventory</a>
                        </span>

                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.PENDING_CLOSEOUT">
                            <p>
                                (Report Sent: {{dto.Inspections.Notification_date | dateToISO | date:"MM/dd/yy"}})
                                <br>
                                <a target="_blank" style="margin:  5px 0; " class="btn btn-info left" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i>View Report</a>
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.INCOMPLETE_CAP || dto.Inspections.Status == constants.INSPECTION.STATUS.OVERDUE_CAP">
                            <p>
                                (CAP Due: {{dto.Inspections.Notification_date | getDueDate | date:"MM/dd/yy"}})
                                <br>
                                <a target="_blank" style="margin:  5px 0; " class="btn btn-info left" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i>View Report</a>
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.CLOSED_OUT">
                            <p ng-if="dto.Inspections.HasDeficiencies">
                                (CAP Approved: {{dto.Inspections.Date_closed | dateToISO}})
                                <br>
                                <a target="_blank" style="margin:  5px 0; " class="btn btn-info left" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i>Archived Report</a>
                            </p>
                            <p ng-if="!dto.Inspections.HasDeficiencies">
                                (No deficiencies.)
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.INCOMPLETE_INSPECTION">
                            <p>
                                (Started: {{dto.Inspections.Date_started | dateToISO}})
                                <br>
                                <a target="_blank" style="margin:  5px 0;" class="btn btn-danger left" href="InspectionChecklist.php#?inspection={{dto.Inspections.Key_id}}"><i style="font-size:21px;margin:3px 2px 0" class="icon-zoom-in"></i>Continue Inspection</a>
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.SUBMITTED_CAP">
                            <span><br>(CAP Sent: {{dto.Inspections.Cap_submitted_date | dateToISO}})</span>
                            <br>
                            <a target="_blank" style="margin:  5px 0;" class="btn btn-info left" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i>Submitted Report</a>
                        </span>
                        <span ng-if="dto.Inspections.Status == constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION">
                            <span><br>(Scheduled for {{dto.Inspections.Schedule_month | getMonthName}})</span>
                            <br>
                            <span ng-init="dto = getRoomUrlString(dto)"></span>
                            <a target="_blank" style="margin:  5px 0;" class="btn btn-danger left" href="../../hazard-inventory/#?pi={{dto.Pi_key_id}}&{{dto.roomUrlParam}}"><img src="../../img/hazard-icon.png" />Inventory</a>
                        </span>
                    </span>
                    <i class="icon-spinnery-dealie spinner small" style="position:absolute;margin: 3px;" ng-if="dto.IsDirty"></i>
                </td>
                <td style="width:9.5%" class="hazard-icons">
                    <span ng-if="dto.Bio_hazards_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/biohazard-largeicon.png" /></span>
                    <span ng-if="dto.Chem_hazards_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/chemical-blue-icon.png" /></span>
                    <span ng-if="dto.Rad_hazards_present" ng-class="{'grayed-out': !dto.Inspections || !dto.Inspections.Is_rad}"><img src="../../img/radiation-large-icon.png" /></span>
                    <span ng-if="dto.Recombinant_dna_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/dna.png" /></span>                    
                    
                    <span ng-if="dto.Corrosive_gas_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/corrosive-gas.png" /></span>
                    <span ng-if="dto.Flammable_gas_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/flammable-gas.png" /></span>
                    <span ng-if="dto.Toxic_gas_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/toxic-gas.png" /></span>
                    <span ng-if="dto.Hf_present" ng-class="{'grayed-out': !dto.Inspections || dto.Inspections.Is_rad}"><img src="../../img/hf.jpg" /></span>
                    
                    <span ng-if="dto.Lasers_present" ng-class="{'grayed-out': !dto.Inspections || !dto.Inspections.Is_rad}"><img src="../../img/laser.png" /></span>
                    <span ng-if="dto.Xrays_present" ng-class="{'grayed-out': !dto.Inspections || !dto.Inspections.Is_rad}"><img src="../../img/xray.png" /></span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

