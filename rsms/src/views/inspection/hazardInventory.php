<?php
require_once '../top_view.php';
?>
<script src="../../js/hazardInventory.js"></script>

<div class="navbar">
    <ul class="nav pageMenu row-fluid redBg">
        <li class="span12">
            <h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
                <img src="../../img/hazard-icon.png"  style="height:50px" />
                Laboratory Hazards & Equipment Inventory
                <a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
            </h2>
        </li>
    </ul>
</div>
<div data-ng-app="hazardAssesment" data-ng-controller="hazardAssessmentController">
<div class="container-fluid whitebg" style="padding-bottom:130px;" >
    <div class="">
        <div>
        <div id="editPiForm" class="row-fluid">
        <form class="form">
             <div class="control-group span4">
               <label class="control-label" for="name"><h3>Principal Investigator</h3></label>
               <div class="controls">
               <span ng-if="!PIs">
                     <input class="span8" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
                    <img class="" style="height:23px; margin:-13px 0 0 -30px" src="<?php echo WEB_ROOT?>img/loading.gif"/>
               </span>
               <span ng-if="PIs">
                    <ui-select ng-if="!PI || selectPI" ng-model="pi.selected" theme="selectize" ng-disabled="disabled" on-select="onSelectPi($item)" class="span8" >
                        <ui-select-match placeholder="Select or search for a PI">{{$select.selected.User.Name}}</ui-select-match>
                        <ui-select-choices repeat="pi in PIs | propsFilter: {User.Name: $select.search}">
                          <div ng-bind-html="pi.User.Name | highlight: $select.search"></div>
                        </ui-select-choices>
                    </ui-select>
                   <h3 style="display:inline" ng-if="PI && !selectPI">{{PI.User.Name}}</h3>
                    <span ng-click="selectPI = !selectPI">
                        <i  ng-if="PI && !selectPI" style="margin: -1px 2px;" class="icon-pencil primary"></i>
                        <i class="icon-cancel danger" ng-if="PI && selectPI" ng-click="selectPI = !selectPI" style="margin: 6px 5px;"></i>
                    </span>
                </span>
              </div>
             <h3 style="display:block; width:100%; margin-top:12px;" ng-if="!selectPI && PI"><a class="btn btn-info" href="../hubs/PIHub.php#/rooms?pi={{PI.Key_id}}&inspection=true">Manage Data for Selected PI</a></h3>
             </div>
            <div class="span8" ng-if="PI || pi">
               <div class="controls">
               <h3 class="span6">Building(s):</h3>
               <h3 class="span6">
               Laboratory Rooms:
               </h3>
                   <span ng-if="!buildings.length">
                           <p ng-if="!noRoomsAssigned" style="display: inline-block; margin-top:5px;">
                               Select a Principal Investigator.
                           </p>
                            <P ng-if="noRoomsAssigned" style="display: inline-block; margin-top:5px;">
                            <span once-text="PI.User.Name"></span> has no rooms <a class="btn btn-info" once-href="'../hubs/PIHub.php#/rooms?pi='+PI.Key_id'&inspection=true">Add Rooms</a>
                        </p>
                </span>

                   <span ng-if="buildings && PI">
                           <ul class="selectedBuildings">
                               <li ng-repeat="(key, building) in buildings | singleRoom:singleRoom">
                               <div class="span6">
                                   <h4 ><!--<a class="btn btn-danger btn-mini" style="margin-right:5px;"><i class="icon-cancel-2" ng-click="removeBuilding(building)"></i></a>-->{{building.Name}}</h4>
                               </div>
                               <div class="roomsForBuidling span6">
                                   <ul>
                                       <li ng-repeat="(key, room) in building.Rooms | orderBy: 'Name'"><a ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{room.Name}}</a><span ng-if="!room.HasMultiplePIs">{{room.Name}}</span></li>
                                   </ul>
                               </div>
                               </li>
                           </ul>
                   </span>
                </div>
            </div>
            </form>
        </div>

        <div class="loading" ng-if='!PI' >

            <h2 class="alert alert-danger" ng-if="error">{{error}}</h2>

            <span ng-if="piLoading">
              <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
              Getting Selected Principal Investigator...
            </span>

        </div>
        <h3 ng-if="inactive">Principal Investigator is inactive.</h3>
        <form>
        <span ng-if="hazardsLoading" class="loading">
         <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
          Building Hazard List...
        </span>
               <ul class="allHazardList">
                <li class="hazardList" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazards">
                    <h1 class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" ng-if="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
                    <span ng-if="!hazard.hidden">
                    <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id">
                        <span ng-if="hazard.Name == 'Biological Safety'">Biological Hazards</span><span ng-if="hazard.Name == 'Chemical/Physical Safety' || hazard.Name == 'Chemical and Physical Safety'">Chemical/Physical Hazards</span><span ng-if="hazard.Name == 'Radiation Safety'">Radiation Hazards</span>
                    </h1>
                    <hr>
                    <ul class="topChildren">
                        <li>
                            <a style="margin-bottom:15px;" class="btn btn-mini btn-info" ng-click="hazard.hideUnselected = !hazard.hideUnselected">
                                <span ng-if="!hazard.hideUnselected">
                                    <i style="margin-right:8px !important;" class="icon-collapse"></i>View Only Hazards Present
                                </span>
                                <span ng-if="hazard.hideUnselected">
                                    <i style="margin-right:8px !important;" class="icon-full-screen"></i>View All Hazard Categories
                                </span>
                            </a>
                        </li>
                        <li ng-repeat="(key, child) in hazard.ActiveSubHazards | filter: {Is_equipment: false}" class="hazardLi topChild" id="id-{{hazard.Key_Id}}" ng-if="child.IsPresent || !hazard.hideUnselected">
                            <!--<h4 class="">-->
                            <label class="checkbox inline">
                                <input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
                                <span class="metro-checkbox"></span>
                                <!--<pre>{{child | json}}</pre>-->
                            </label>
                            <span style="font-size: 14px;font-weight: normal;line-height: 20px;">
                                <a class="metro-checkbox targetHaz" ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{child.Name}}</a>                                         <span class="metro-checkbox targetHaz" ng-if="!room.HasMultiplePIs">{{child.Name}}</span>

                                    <!--<span once-text="child.Name" class="nudge-up"></span>-->

                                    <img ng-if="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
                            </span>
                            <!--</h4>-->
                            <span ng-if="child.ActiveSubHazards.length || child.HasChildren&& child.IsPresent ">
                                <i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child, $element)"></i>
                            </span>
                            <span ng-if="child.InspectionRooms.length > 1 && child.IsPresent">
                                <i class="icon-enter" ng-click="showRooms($event, child, $element)"></i>
                            </span>

                            <span ng-if="child.HasMultiplePIs && child.IsPresent">
                                <i class="icon-info" ng-click="openMultiplePIsModal(child)"></i>
                            </span>

                            <div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal popUp skinny" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px">
                                <h3 class="redBg"><span once-text="child.Name" class="nudge-up"></span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
                                <ul>
                                    <li ng-repeat="(key, child) in child.ActiveSubHazards">
                                        <label class="checkbox inline">
                                            <input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
                                            <span class="metro-checkbox" once-text="child.Name" ></span>
                                        </label>
                                        <div class="clearfix"></div>
                                    </li>
                                </ul>
                            </div>

                            <div class="roomsModal popUp skinny" ng-class="{hidden: !child.showRoomsModal}" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;width:{{child.calculatedOffset.w}}px">
                                <h3 class="redBg"><span once-text="child.Name" class="nudge-up"></span><i class="icon-cancel-2" ng-click="child.showRoomsModal = !child.showRoomsModal"></i></h3>
                                <ul>
                                    <li ng-repeat="(key, room) in child.InspectionRooms">
                                        <label class="checkbox inline">
                                            <input type="checkbox" ng-change="handleRoom(room, child, hazard)" ng-model="room.ContainsHazard"/>
                                            <span class="metro-checkbox" once-text="room.Name"><img ng-if="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
                                        </label>
                                        <div class="clearfix"></div>
                                    </li>
                                </ul>
                            </div>

                            <ul ng-if="getShowRooms(child)" class="subRooms">
                                <li>Rooms:</li>
                                <li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="" ng-class="{'last':$last}">
                                    <a ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{room.Name}}</a><span ng-if="!room.HasMultiplePIs">{{room.Name}}</span>
                                </li>
                            </ul>
                            <ul>
                                <li ng-repeat="child in child.ActiveSubHazards" ng-if="child.IsPresent" class="hazardLi" id="id-{{child.Key_Id}}">
                                    <span data-ng-include="'sub-hazard.html'"></span>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <!-- EQUIPMENT LIST HERE -->
                    <br/><br/><br/>
                    <h1 class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" style="margin-bottom:-12px;">{{hazard.}}<span ng-if="hazard.Name == 'Biological Safety' || hazard.Name == 'Chemical and Physical Safety' || hazard.Name == 'Chemical/Physical Safety'">Safety Equipment</span><span ng-if="hazard.Name == 'Radiation Safety'">Equipment/Device</span></h1>
                    <hr style="margin-bottom:4px;">
                    <ul class="topChildren">
                        <li ng-repeat="(key, child) in hazard.ActiveSubHazards | filter: {Is_equipment: true}" class="hazardLi topChild" id="id-{{hazard.Key_Id}}" ng-if="child.IsPresent || !hazard.hideUnselected">
                            <!--<h4 class="">-->
                            <label class="checkbox inline">
                                <input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
                                        <a class="metro-checkbox targetHaz" ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{child.Name}}</a><span class="metro-checkbox targetHaz" ng-if="!room.HasMultiplePIs">{{child.Name}}</span>

                                    <!--<span once-text="child.Name" class="nudge-up"></span>-->

                                    <img ng-if="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
                                <!--<pre>{{child | json}}</pre>-->

                            </label>

                            </span>
                            <!--</h4>-->
                            <span ng-if="child.ActiveSubHazards.length || child.HasChildren&& child.IsPresent ">
                                <i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child, $element)"></i>
                            </span>
                            <span ng-if="child.InspectionRooms.length > 1 && child.IsPresent">
                                <i class="icon-enter" ng-click="showRooms($event, child, $element)"></i>
                            </span>

                            <span ng-if="child.HasMultiplePIs && child.IsPresent">
                                <i class="icon-info" ng-click="openMultiplePIsModal(child)"></i>
                            </span>

                            <div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal popUp skinny" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px">
                                <h3 class="redBg"><span once-text="child.Name" class="nudge-up"></span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
                                <ul>
                                    <li ng-repeat="(key, child) in child.ActiveSubHazards">
                                        <label class="checkbox inline">
                                            <input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
                                            <span class="metro-checkbox" once-text="child.Name" ></span>
                                        </label>
                                        <div class="clearfix"></div>
                                    </li>
                                </ul>
                            </div>

                            <div class="roomsModal popUp skinny" ng-class="{hidden: !child.showRoomsModal}" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;width:{{child.calculatedOffset.w}}px">
                                <h3 class="redBg"><span once-text="child.Name" class="nudge-up"></span><i class="icon-cancel-2" ng-click="child.showRoomsModal = !child.showRoomsModal"></i></h3>
                                <ul>
                                    <li ng-repeat="(key, room) in child.InspectionRooms">
                                        <label class="checkbox inline">
                                            <input type="checkbox" ng-change="handleRoom(room, child, hazard)" ng-model="room.ContainsHazard"/>
                                            <span class="metro-checkbox" once-text="room.Name"><img ng-if="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
                                        </label>
                                        <div class="clearfix"></div>
                                    </li>
                                </ul>
                            </div>

                            <ul ng-if="getShowRooms(child)" class="subRooms">
                                <li>Rooms:</li>
                                <li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="" ng-class="{'last':$last}">
                                    <a ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{room.Name}}</a><span ng-if="!room.HasMultiplePIs">{{room.Name}}</span>
                                </li>
                            </ul>
                            <ul>
                                <li ng-repeat="child in child.ActiveSubHazards" ng-if="child.IsPresent" class="hazardLi" id="id-{{child.Key_Id}}">
                                    <span data-ng-include="'sub-hazard.html'"></span>
                                </li>
                            </ul>
                        </li>
                    </ul>

                    <br/><br/>
                    </span>
                </li>
            </ul>
        </form>

            <div class="span12">
                    <!--<pre><strong>selected with helper function:</strong> {{selectedHazards() | json}}</pre>]-->
                    <h2 data-ng-repeat="hazard in checked_hazards" once-text="hazard.Name"></h2>
            </div>
        </div>
    </div>

</div>
<span ng-controller="footerController">

    <div ng-if="selectedFooter == 'reports'" class="selectedFooter" style="width:auto;">
        <i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
        <h2 style="text-decoration:underline">ARCHIVED REPORTS</h2>
        <span ng-if="!PI">
        <h2 style="min-width:460px;">Please select a principal investigator.</h2>
        </span>
        <span ng-if="PI">
        <h2>Principle Investigator: <span once-text="PI.User.Name"></span></h2>

        <div class="loading" ng-if='!previousInspections' >
        Loading Archived Reports...
          <img class="" src="../../img/loading.gif"/>
        </div>
        <div id="tableContainer" class="tableContainer">
        <table ng-if="previousInspections" class="table table-striped table-bordered" class="scrollTable">
        <thead class="fixedHeader">
                <th style="width:60px;">Year</th>
                <th style="width:170px;">Inspection Date</th>
                <th style="width:216px;">Inspector(s)</th>
                <th style="width:120px;">Hazards</th>
                <th style="width:160px">Inspection Report</th>
                <th style="width:209px">Close Out Date</th>
            </thead>
            <tbody class="scrollContent">
                <tr ng-repeat="(key, inspection) in previousInspections">
                    <td style="width:61px;">{{inspection.year}}</td>
                    <td style="width:173px;">{{inspection.startDate}}</td>
                    <td  style="width:220px;">{{inspection.Inspectors[0].User.Name}}</td>
                    <td style="width:121px;">hazards</td>
                    <td style="width:163px;"><a href="../inspection/InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}">Report</a></td>
                    <td style="width:197px;">
                        <span once-text="inspection.Status"></span>
                        <span ng-if="inspection.Status == 'CLOSED OUT'">
                            <p>
                                (CAP Submitted: {{inspection.Cap_submitted_date | dateToISO}})
                                <a target="_blank" style="margin-top: -4px; margin-left: 6px;padding: 4px 7px 6px 0px;" class="btn btn-info" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i></a>
                            </p>
                        </span>

                        <span ng-if="inspection.Status == 'PENDING CLOSEOUT'">
                            <p>
                                (CAP Submitted: {{inspection.Cap_submitted_date | dateToISO}})
                                <a target="_blank" style="margin-top: -4px; margin-left: 6px;padding: 4px 7px 6px 0px;" class="btn btn-info" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i></a>
                            </p>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
        </span>
    </div>


    <div style="margin-left:25%;" ng-if="selectedFooter == 'contacts'" class="selectedFooter">
    <i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
        <h2 style="text-decoration:underline">Lab Contacts</h2>
        <span ng-if="!PI">
            <h2>Please select a principal investigator.</h2>
        </span>
        <span ng-if="PI">
        <h2>Principle Investigator: {{PI.User.Name}}</h2>

        <div class="loading" ng-if='!PI' >
        Loading Archived Reports...
          <img class="" src="../../img/loading.gif"/>
        </div>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Lab Phone</th>
                    <th>Emergency Phone</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="(key, contact) in PI.LabPersonnel">
                    <td>{{contact.Name}}</td>
                    <td>{{contact.Lab_phone}}</td>
                    <td>{{contact.Emergency_phone}}</td>
                    <td>{{contact.Email}}</td>
                </tr>
            </tbody>
        </table>
        </span>
    </div>

    <div ng-if="selectedFooter == 'comments'" class="selectedFooter" style="margin-left:48%; width:19%;">
        <i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
        <h3 style="text-decoration:underline; margin-bottom:5px;">INSPECTION COMMENTS</h3>
        <span ng-if="!inspection.Note || noteEdited">
            <textarea ng-model="newNote" rows="4" style="width:100%"></textarea>
            <a ng-click="saveNoteForInspection(newNote)" class="btn btn-success"><i class="icon-checkmark"></i>Save</a>
            <a ng-click="cancelSaveNote(); editNote = false;" class="btn btn-danger"><i class="icon-cancel"></i>Cancel</a>
            <img ng-if="newNoteIsDirty" class="smallLoading" src="../../img/loading.gif"/>
        </span>
        <span ng-if="inspection.Note && !noteEdited">
            <h4>{{inspection.Note}}<a style="margin-left:5px;" class="btn btn-mini btn-primary" ng-click="editNote()" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a></h4>
        </span>
    </div>

<div id="footer" style="position:fixed; bottom:0; width:100%; background:white; left:0; z-index:1040; box-shadow:0 0 20px rgba(0,0,0,.5)" ng-if="PI">
    <ul class="container-fluid whitebg" style="padding:0 70px !Important">
        <li><a ng-click="getArchivedReports(pi)"><img src="../../img/clipboard.png"/><span>Archived Reports</span></a></li>
        <li><a href="../hubs/PIHub.php#/personnel?pi={{PI.Key_id}}&inspection=true" target="_blank"><img src="../../img/phone.png"/><span>Laboratory Personnel</span></a></li>
        <li><a ng-click="openNotes()"><img src="../../img/speechBubble.png"/><span>Inspection Comments</span></a></li>
        <li><a ng-click="startInspection()"><img src="../../img/checkmarkFooter.png"/><span>Inspect Labs</a></span></li>
    </ul>
</div>
</span>
</div>
