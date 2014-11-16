<?php
require_once '../top_view.php';
?>
<div ng-app="inspectionChecklist" ng-controller="ChecklistController">
	<div id="sp-nav" class="span3">
		<a class="menuIcon" ng-click="$spMenu.toggle()">&#9776;</a>
        <ul class="nav nav-list nav nav-pills nav-stacked" id="sideNav">
          <li class="nav-header" style="font-size: 30px;padding: 20px 14px;">Checklists</li>
          	<li ng-repeat="checklist in checklists" ng-if="checklist.checklists.length">

          	<a ng-click="selectChecklistCategory(checklist.uid)" class="{{checklist.uid}}Header checklistListNavHeader">
          		<img ng-if="!checklist.altImg && checklist.img" src="../../img/{{checklist.img}}"/>
          		<img ng-if="checklist.altImg" src="../../img/{{checklist.altImg}}"/>
          		<span style="display:inline-block;">{{checklist.Name}}</span>
          	</a>
          	<ul ng-if="selectedChecklists.checklists[0].Key_id == checklist.checklists[0].Key_id">
          		<li ng-repeat="list in checklist.checklists">
	          		<a ng-class="{active:list.currentlyOpen}" ng-click="change(list.Key_id,list)" href="#{{list.Key_id}}">
	          			<span style="display:inline-block; width:75%; margin-right:10%;">{{list.Name}}</span>
	          			<span ng-class="checklist.countClass" style="width: 15%;float:right; text-align:right;">{{list.AnsweredQuestions}}/{{list.Questions.length}}</span>
	          		</a>
          		</li>
          	</ul>
          </li>
        </ul>
    </div><!--/span-->
<div class="tst">
<div id="sp-page" class="whitebg checklist">
	<div style="position:fixed">
	</div>

<script src="../../js/inspectionChecklist.js"></script>

<div class="navbar">    		
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Inspection Checklist
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
</div>

<div class="row-fluid">
<!--<a ng-click="$spMenu.toggle()" .icon-menu-2 style="background:blue;font-size: 27px !important; color: black; text-decoration:none!important" class="toggles toggledIn"><p class="rotate">Show/Hide Menu<i style="margin-top: 16px; font-size: 50px !important;" class="icon-arrow-down"></i></p></a>  
-->	
	<div class="loading" ng-show='!checklists' >
	  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
	  Getting Checklists...
	</div>
	<div class="alert alert-error" ng-if="error" style="margin-top:10px;">
		<h2>{{error}}</h2>
	</div>
	<ul class="postInspectionNav row" style="margin-left:11px;">
		<li ng-if="checklists.biologicalHazards.checklists.length"><a ng-click="selectChecklistCategory('biologicalHazards')" class="btn btn-large checklistNav" id="biologicalMaterialsHeader" ng-class="{selected: route==confirmation}"><img src="../../img/biohazard-white-con.png"/><span>BIOLOGICAL SAFETY</span></a></li>
		<li ng-if="checklists.chemicalHazards.checklists.length"><a ng-click="selectChecklistCategory('chemicalHazards')" class="btn btn-large checklistNav" id="chemicalSafetyHeader" ng-class="{selected: route==confirmation}"><img src="../../img/chemical-safety-large-icon.png"/><span>CHEMICAL SAFETY</span></a></li>
		<li ng-if="checklists.generalHazards.checklists.length"><a ng-click="selectChecklistCategory('generalHazards')" class="btn btn-large checklistNav" id="generalSafetyHeader" ng-class="{selected: route==confirmation}"><img src="../../img/gen-hazard-large-icon.png"/><span>GENERAL SAFETY</span></a></li>
		<li ng-if="checklists.radiationHazards.checklists.length"><a ng-click="selectChecklistCategory('radiationHazards')" class="btn btn-large checklistNav"  id="radiationSafetyHeader" ng-class="{selected: route==confirmation}"><img src="../../img/radiation-large-icon.png"/><span>RADIATION SAFETY</span></a></li>
	</ul>
	<h2 style="margin-left:11px"><img style="margin: -6px 5px 4px 0; max-width:50px;" ng-if="selectedChecklists.img" src="../../img/{{selectedChecklists.img}}"/><span>{{selectedChecklists.Name}}</span></h2>
    <!-- begin checklist for this inspection -->
		<accordion close-others="true">
			<accordion-group ng-class="{active:checklist.currentlyOpen}" class="checklist" ng-repeat="checklist in selectedChecklists.checklists" is-open="checklist.currentlyOpen">
				<accordion-heading>
					<span style="margin-top:20px;" id="{{checklist.key_id}}"></span>
					<input type="hidden" ng-model="checklist.AnsweredQuestions"/>
					<h2>{{checklist.Name}}<span style="float:right" class="checklist.countClass">{{checklist.AnsweredQuestions}}/{{checklist.Questions.length}}</span></h2>
				</accordion-heading>
		     	<ul style="margin-left:0;">	
		     		<li class="question" ng-repeat="question in checklist.Questions">
		     			<h3 style="width:45%; float:left;"><img ng-show="question.IsDirty" class="smallLoading" src="../../img/loading.gif"/><span once-text="question.Text"></span></h3>
		     			<div class="questionAnswerInputs">
	     					<label class="radio inline">
								<input type="radio" value="yes" ng-model="question.Responses.Answer" ng-change="questionAnswered(checklist, question.Responses, question)"  ng-click="setUnchecked(question.Responses.previous,'yes',question,checklist)"/>
								<span class="metro-radio">Yes</span>
							</label>
							<label class="radio inline">
								<input type="radio" value="no" ng-model="question.Responses.Answer" ng-change="questionAnswered(checklist, question.Responses, question)" ng-click="setUnchecked(question.Responses.previous,'no',question,checklist)"/>
								<span class="metro-radio">No</span>
							</label>
							<label class="radio inline">
								<input type="radio" value="n/a" ng-model="question.Responses.Answer" ng-change="questionAnswered(checklist, question.Responses, question)" ng-click="setUnchecked(question.Responses.previous,'n/a',question,checklist)"/>
								<span class="metro-radio">N/A</span>
							</label>
							<label class="checkbox inline" class="disabled">
								<input type="checkbox" value="true" ng-model="question.showRecommendations"  ng-disabled="!question.Responses.Answer" />
								<span class="metro-checkbox">Recommendations</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox" value="true" ng-model="question.showNotes" ng-disabled="!question.Responses.Answer"/>
								<span class="metro-checkbox">Notes</span>
							</label>
						</div>
						<span style="clear:both; display:block; height:0;">&nbsp;</span>
						<span ng-hide="!question.Deficiencies.length" ng-switch on="question.Responses.Answer">
							<ul ng-switch-when="no" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Deficiencies:</h3>
								<li ng-repeat="deficiency in question.Deficiencies">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-model="deficiency.selected" ng-change="deficiencySelected(question, deficiency, deficiency.rooms, checklist)" />
										<span class="metro-checkbox"><img ng-show="deficiency.IsDirty" class="smallLoading" src="../../img/loading.gif"/><span style="margin-top:0" once-text="deficiency.Text"></span></span>
									</label>
									<pre>{{deficiency.InspectionRooms | json}}</pre>
									<span ng-show="deficiency.selected">
											<i class="icon-enter checklistRoomIcon" ng-click="showRooms($event, deficiency, $element, checklist)"></i>
									</span>

									<div class="roomsModal popUp" ng-if="deficiency.showRoomsModal && deficiency.InspectionRooms" style="width:200px;margin-left:{{deficiency.calculatedOffset.x}};margin-top:-20px;padding:0;border:none;">
										<div class="alert alert-danger" style="margin-bottom:0; padding:5px;"><h3>Rooms<i class="icon-cancel-2" style="margin:5px 2px;; float:right" ng-click="deficiency.showRoomsModal = !deficiency.showRoomsModal"></i></h3></div>
										<ul style="margin-left:13px;">
											<li ng-repeat="room in deficiency.InspectionRooms">
												<label class="checkbox inline">
													<input type="checkbox" ng-change="selectRoom(question, deficiency, room, checklist)" ng-model="room.checked"/>
													<span class="metro-checkbox"><span once-text="room.Name"></span><img ng-if="room.IsDirty" class="" src="../../img/loading.gif"/></span>
												</label>
											</li>
										</ul>
									</div>

									<ul style="margin:10px" ng-switch on="deficiency.selected">
										<li ng-switch-when="true">
											<label class="checkbox inline">
												<input type="checkbox" value="true" ng-model="deficiency.correctedDuringInspection" ng-change="handleCorrectedDurringInspection(deficiency)" />
												<span class="metro-radio">corrected during inpsection</span>
											</label>
										</li>
									</ul>
								</li>
							</ul>
						</span>

						<span ng-if="question.Responses.Answer">
							<ul ng-if="question.showRecommendations"style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Recommendations:</h3>
								<li ng-repeat="recommendation in question.Recommendations" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-show="!recommendation.edit">
										<input type="checkbox" value="true" ng-model="recommendation.checked" ng-change="handleNotesAndRecommendations(question, recommendation)" />
										<span class="metro-checkbox standardRecOrObs" ng-class="{newRecOrObs:recommendation.isNew}"><span once-text="recommendation.Text"></span><img ng-show="recommendation.IsDirty" class="smallLoading" src="../../img/loading.gif"/><!--<span ng-show="recommendation.isNew" class="label label-success" style="margin-left:3px;">New Option</span>--><a ng-show="recommendation.isNew" ng-click="editItem (question, recommendation)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-show="recommendation.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-model="recommendationCopy.Text" style="width:50%"></textarea><br>
										<a ng-click="saveEdit(question, recommendationCopy, recommendation)" class="btn btn-success">Save</a>
										<a ng-click="cancelEdit(recommendation)" class="btn btn-danger">Cancel</a>
									</span>
								</li>
								<li ng-repeat="recommendation in question.Responses.SupplementalRecommendations" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-if="!recommendation.edit" >
										<input type="checkbox" value="true" ng-model="recommendation.Is_active" ng-change="setNoteOrObsActiveOrInactive(question, recommendation)" />
										<span class="metro-checkbox labSpecific" ng-class="{edit:recommendation.edit}">{{recommendation.Text}}<img ng-show="recommendation.IsDirty" class="smallLoading" src="../../img/loading.gif"/><!--<span style="margin-left:3px;" class="label label-info">Lab Specific</span>--><a ng-click="editItem (question, recommendation)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-show="recommendation.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-model="recommendationCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="recommendation.edit" ng-click="saveEdit(question, recommendationCopy, recommendation)" class="btn btn-success">Save</a>
										<a ng-show="recommendation.edit" ng-click="cancelEdit(recommendation)" class="btn btn-danger">Cancel</a><img ng-show="recommendation.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
									</span>
								</li><!--editItem = function(item, question)-->
								<li>
									 <form ng-if="!recommendationCopy">
									 	<input type="hidden" value="recommendation" name="question.TextType" ng-model="question.TextType" ng-update-hidden />
							        	<textarea ng-model="question.recommendationText" rows="2" style="width:100%;"></textarea>
								        <input  class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Recommendation" ng-click="createNewNoteOrRec(question,question.Responses,false,'recommendation')"/>
								        <input  class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Recommendation Option" ng-click="createNewNoteOrRec(question,question.Responses,true,'recommendation')"/>
								    	<img ng-show="question.savingNew" class="smallLoading" src="../../img/loading.gif"/>
								    </form>
								</li>
							</ul>
						</span>

						<span ng-hide="!question.Responses.Answer" ng-switch on="question.showNotes">
							<ul ng-switch-when="true" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Notes:</h3>
								<li ng-repeat="note in question.Observations" style="margin-bottom:3px;">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-show="!note.edit" ng-model="note.checked" ng-change="handleNotesAndRecommendations(question, note)"/>
										<span class="metro-checkbox" ng-class="{newRecOrObs:note.isNew}">{{note.Text}}<img ng-show="note.IsDirty" class="smallLoading" src="../../img/loading.gif"/><!--<span style="margin-left:3px;" ng-show="note.isNew" class="label label-success">New Option</span>--><a ng-show="note.isNew" ng-click="editItem (question, note)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-show="note.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-show="note.edit" ng-model="noteCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="note.edit" ng-click="saveEdit(question, noteCopy, note)" class="btn btn-success">Save</a>
										<a ng-show="note.edit"  ng-click="cancelEdit(note)" class="btn btn-danger">Cancel</a>
									</span>
								</li>
								<li ng-repeat="note in question.Responses.SupplementalObservations" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-show="!note.edit">
										<input type="checkbox" value="true" ng-model="note.Is_active" ng-change="setNoteOrObsActiveOrInactive(question, note)"/>
										<span class="metro-checkbox labSpecific">{{note.Text}}<img ng-show="note.IsDirty" class="smallLoading" src="../../img/loading.gif"/><!--<span style="margin-left:3px;" class="label label-info">Lab Specific</span>--><a ng-click="editItem (question, note)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-show="note.edit" style="margin: 20px 0 ;display: block;">
										<textarea  ng-model="noteCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="note.edit" ng-click="saveEdit(question, noteCopy, note)" class="btn btn-success">Save</a>
										<a ng-show="note.edit" ng-click="cancelEdit(note)" class="btn btn-danger">Cancel</a><img ng-show="note.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
									</span>
								</li>
								<li>		
									<form ng-if="!noteCopy">
									 	<input type="hidden" value="note" name="question.TextType" ng-model="question.TextType" ng-update-hidden />
							        	<textarea ng-model="question.noteText" rows="2" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Note" ng-click="createNewNoteOrRec(question,question.Responses,false,'observation')"/>
								        <input class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Note Option" ng-click="createNewNoteOrRec(question,question.Responses,true,'observation')"/>
								        <img ng-show="questionquestion.savingNew" class="smallLoading" src="../../img/loading.gif"/>
								    </form>					
								</li>
							</ul>
						</span>
		     		</li>
		     		<div style="clear:both"></div>
		     	</ul>
		    </accordion-group>
		    <a class="btn btn-large btn-success" ng-if="selectedChecklists" style="margin:0 10px 10px" href="InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}">View Interim Report</a>
		</accordion>
	</div>
	</div>
</div>
</div>