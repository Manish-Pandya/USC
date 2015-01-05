<?php
require_once '../top_view.php';
?>
<script src="../../js/inspectionChecklist2.js"></script>

<div ng-app="inspectionChecklist" ng-controller="checklistController" ng-cloak>
	<div id="sp-nav" class="span3">
		<a class="menuIcon" ng-click="$spMenu.toggle()">&#9776;</a>
        <ul class="nav nav-list nav nav-pills nav-stacked" id="sideNav">
          	<li class="nav-header" style="font-size: 30px;padding: 20px 14px;">Checklists</li>
          	<li ng-show="biological">
          		<a ng-click="cf.selectCategory('Biological Safety')" class="checklistListNavHeader" id="biologicalMaterialsHeader"><img src="../../img/biohazard-white-con.png"/><span>BIOLOGICAL SAFETY</span></a>
      			<ul ng-if="category.indexOf('Biological') > -1 && !loading">
      				<li ng-include="'checklist-subnav.html'" ng-repeat="list in inspection.selectedCategory"></li>
      			</ul>
          	</li>
			<li ng-show="chemical">
				<a ng-click="cf.selectCategory('Chemical Safety')" class="checklistListNavHeader" id="chemicalSafetyHeader"><img src="../../img/chemical-safety-large-icon.png"/><span>CHEMICAL SAFETY</span></a>
				<ul ng-if="category.indexOf('Chemical') > -1 && !loading">
      				<li ng-include="'checklist-subnav.html'" ng-repeat="list in inspection.selectedCategory"></li>
      			</ul>			
      		</li>
			<li>
				<a ng-click="cf.selectCategory('General Safety')" class="checklistListNavHeader" id="generalSafetyHeader"><img src="../../img/gen-hazard-large-icon.png"/><span>GENERAL SAFETY</span></a>
				<ul ng-if="category.indexOf('General') > -1 && !loading">
      				<li ng-include="'checklist-subnav.html'" ng-repeat="list in inspection.selectedCategory"></li>
      			</ul>			
      		</li>
			<li ng-show="radiation">
				<a ng-click="cf.selectCategory('Radiation Safety')" class="checklistListNavHeader" id="radiationSafetyHeader"><img src="../../img/radiation-large-icon.png"/><span>RADIATION SAFETY</span></a>
				<ul ng-if="category.indexOf('Radiation') > -1 && !loading">
      				<li ng-include="'checklist-subnav.html'" ng-repeat="list in inspection.selectedCategory"></li>
      			</ul>	
			</li>
          	
          	<!--
          	<li ng-repeat="checklist in inspection.Checklists">
          	<a ng-click="selectChecklistCategory(checklist.uid)" class="{{checklist.uid}}Header checklistListNavHeader">
          		<img ng-if="!checklist.altImg && checklist.img" src="../../img/{{checklist.img}}"/>
          		<img ng-if="checklist.altImg" src="../../img/{{checklist.altImg}}"/>
          		<span style="display:inline-block;">{{checklist.Name}}</span>
          	</a>
          	-->
        </ul>
    </div><!--/span-->
<div class="tst">
<div id="sp-page" class="whitebg checklist">
	<div style="position:fixed">
	</div>


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
<!--\<pre>{{inspection | json}}</pre>-->
<div class="row-fluid">
<!--<a ng-click="$spMenu.toggle()" .icon-menu-2 style="background:blue;font-size: 27px !important; color: black; text-decoration:none!important" class="toggles toggledIn"><p class="rotate">Show/Hide Menu<i style="margin-top: 16px; font-size: 50px !important;" class="icon-arrow-down"></i></p></a>  
-->
	<div class="loading" ng-show='!inspection && !error' >
	  <i class="icon-spinnery-dealie spinner large"></i>  
	  <span>Getting Checklists...</span>
	</div>
	
	<div class="alert alert-error" ng-if="error" style="margin-top:10px;">
		<h2>{{error}}</h2>
	</div>
	<ul class="postInspectionNav row" style="margin-left:11px;">
		<li ng-show="biological"><a ng-click="cf.selectCategory('Biological Safety')" class="btn btn-large checklistNav" id="biologicalMaterialsHeader" ng-class="{selected: category.indexOf('Biological') > -1}"><img src="../../img/biohazard-white-con.png"/><span>BIOLOGICAL SAFETY</span></a></li>
		<li ng-show="chemical"><a ng-click="cf.selectCategory('Chemical Safety')" class="btn btn-large checklistNav" id="chemicalSafetyHeader" ng-class="{selected: category.indexOf('Chemical') > -1}"><img src="../../img/chemical-safety-large-icon.png"/><span>CHEMICAL SAFETY</span></a></li>
		<li ng-show="general"><a ng-click="cf.selectCategory('General Safety')" class="btn btn-large checklistNav" id="generalSafetyHeader" ng-class="{selected: category.indexOf('General') > -1}"><img src="../../img/gen-hazard-large-icon.png"/><span>GENERAL SAFETY</span></a></li>
		<li ng-show="radiation"><a ng-click="cf.selectCategory('Radiation Safety')" class="btn btn-large checklistNav"  id="radiationSafetyHeader" ng-class="{selected: category.indexOf('Radiation') > -1}"><img src="../../img/radiation-large-icon.png"/><span>RADIATION SAFETY</span></a></li>
	</ul>
	<div class="loading" ng-show='loading' style="margin-left:11px;">
	  <i class="icon-spinnery-dealie spinner large"></i> 
	  <span>Getting Checklist Category...</span>
	</div>
	<!-- todo:  write function to get image path -->
	<h2 ng-if="category && !loading" style="margin-left:11px; font-weight:bold"><img style="margin: -6px 5px 4px 0; max-width:50px;" src="../../img/{{image}}"/><span>{{category}}</span></h2>
	
    <!-- begin checklist for this inspection -->
		<accordion close-others="true" ng-hide="loading">
			<accordion-group ng-class="{active:checklist.currentlyOpen}" class="checklist" ng-repeat="checklist in inspection.selectedCategory" is-open="checklist.currentlyOpen" id="{{checklist.Key_id}}">
				<accordion-heading>
					<span style="margin-top:20px;" id="{{checklist.key_id}}"></span>
					<input type="hidden" ng-model="checklist.AnsweredQuestions"/>
					<h2><span once-text="checklist.Name"></span><span style="float:right" class="checklist.countClass">{{checklist.completedQuestions}}/{{checklist.Questions.length}}</span></h2>
				</accordion-heading>
		     	<ul style="margin-left:0;">	
		     		<li class="question" ng-repeat="question in checklist.Questions | evaluateChecklist:checklist">
		     			<!--call evaluateDeficiecnyRooms -->

		     			<h3 style="width:45%; float:left;"><i class="icon-spinnery-dealie spinner small" ng-if="question.IsDirty"></i><span once-text="question.Text"></span></h3>
		     			<h3 ng-if="question.error" class="alert danger">{{question.error}}</h3>
		     			<div class="questionAnswerInputs">
	     					<label class="checkbox inline">
								<input type="checkbox" ng-true-value="yes" ng-model="question.Responses.Answer" ng-change="cf.saveResponse( question )"/>
								<span class="metro-radio">Yes</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox"  ng-true-value="no" ng-model="question.Responses.Answer" ng-change="cf.saveResponse( question )"/>
								<span class="metro-radio">No</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox"  ng-true-value="n/a" ng-model="question.Responses.Answer" ng-change="cf.saveResponse( question )"/>
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
										<input type="checkbox" ng-model="deficiency.selected" ng-change="cf.saveDeficiencySelection( deficiency, question, checklist )" ng-checked="cf.evaluateDeficiency( deficiency.Key_id )"/>
										<span class="metro-checkbox"><i ng-if="deficiency.IsDirty" class="icon-spinnery-dealie spinner small"></i><span style="margin-top:0" once-text="deficiency.Text"></span></span>
										{{deficiency.Key_id}}
									</label>
									<span ng-if="cf.evaluateDeficiency( deficiency.Key_id )">
										<i class="icon-enter checklistRoomIcon" ng-click="showRooms($event, deficiency, $element, checklist, question)"></i>
									</span>

									<div class="roomsModal popUp" ng-if="deficiency.showRoomsModal && deficiency.InspectionRooms" style="width:200px;margin-left:{{deficiency.calculatedOffset.x}};margin-top:-20px;padding:0;border:none;">
										<div class="alert alert-danger" style="margin-bottom:0; padding:5px;"><h3>Rooms<i class="icon-cancel-2" style="margin:5px 2px;; float:right" ng-click="deficiency.showRoomsModal = !deficiency.showRoomsModal"></i></h3></div>
										<ul>
											<li class="show-rooms">
												<label class="checkbox inline">
													<input type="checkbox" ng-change="cf.saveDeficiencySelection( deficiency, question, checklist )" ng-model="deficiency.Show_rooms" ng-checked="cf.evaluateDeficienyShowRooms(deficiency.Key_id)"/>
													<span class="metro-checkbox">Show rooms in report<i ng-if="room.IsDirty" class="icon-spinnery-dealie spinner small"></i></span>
													{{deficiency.Key_id}}
												</label>
											</li>
											<li ng-repeat="room in deficiency.InspectionRooms | evaluateDeficiencySelectionRooms:question:deficiency">
												<label class="checkbox inline">
													<input type="checkbox" ng-change="cf.saveDeficiencySelection( deficiency, question, checklist, room )" ng-model="room.checked"/>
													<span class="metro-checkbox"><span once-text="room.Name"></span><i ng-if="room.IsDirty" class="icon-spinnery-dealie spinner small"></i></span>
													{{room.checked}}
												</label>
											</li>
										</ul>
									</div>

									<ul style="margin:10px" ng-if="cf.evaluateDeficiency( deficiency.Key_id )">
										<li>
											<label class="checkbox inline">
												<input type="checkbox" value="true" ng-model="deficiency.correctedDuringInspection" ng-checked="inspection.Deficiency_selections[1].indexOf(deficiency.Key_id) > -1" ng-change="cf.handleCorrectedDurringInspection(deficiency, question)" />
												<span class="metro-radio">corrected during inpsection</span>
											</label>
										</li>
									</ul>

								</li>
							</ul>
						</span>

						<span ng-if="question.Responses.Answer.length">
							<ul ng-if="question.showRecommendations"style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Recommendations:</h3>
								<li ng-repeat="recommendation in question.Recommendations" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-show="!recommendation.edit">
										<input type="checkbox" value="true" ng-model="recommendation.checked" ng-change="handleNotesAndRecommendations(question, recommendation)" />
										<span class="metro-checkbox standardRecOrObs" ng-class="{newRecOrObs:recommendation.isNew}"><span once-text="recommendation.Text"></span><i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small"></i><!--<span ng-show="recommendation.isNew" class="label label-success" style="margin-left:3px;">New Option</span>--><a ng-show="recommendation.isNew" ng-click="editItem (question, recommendation)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
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
										<span class="metro-checkbox labSpecific" ng-class="{edit:recommendation.edit}">{{recommendation.Text}}<i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small"></i><!--<span style="margin-left:3px;" class="label label-info">Lab Specific</span>--><a ng-click="editItem (question, recommendation)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-show="recommendation.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-model="recommendationCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="recommendation.edit" ng-click="saveEdit(question, recommendationCopy, recommendation)" class="btn btn-success">Save</a>
										<a ng-show="recommendation.edit" ng-click="cancelEdit(recommendation)" class="btn btn-danger">Cancel</a><i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small"></i>
									</span>
								</li><!--editItem = function(item, question)-->
								<li>
									 <form ng-if="!recommendationCopy">
									 	<input type="hidden" value="recommendation" name="question.TextType" ng-model="question.TextType"/>
							        	<textarea ng-model="question.recommendationText" rows="2" style="width:100%;"></textarea>
								        <input  class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Recommendation" ng-click="createNewNoteOrRec(question,question.Responses,false,'recommendation')"/>
								        <input  class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Recommendation Option" ng-click="createNewNoteOrRec(question,question.Responses,true,'recommendation')"/>
								    	<i ng-if="question.savingNew" class="icon-spinnery-dealie spinner small"></i>
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
										<span class="metro-checkbox" ng-class="{newRecOrObs:note.isNew}">{{note.Text}}<i ng-if="note.IsDirt" class="icon-spinnery-dealie spinner small"></i><!--<span style="margin-left:3px;" ng-show="note.isNew" class="label label-success">New Option</span>--><a ng-show="note.isNew" ng-click="editItem (question, note)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
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
										<span class="metro-checkbox labSpecific">{{note.Text}}<i ng-if="note.IsDirty" class="icon-spinnery-dealie spinner small"></i><!--<span style="margin-left:3px;" class="label label-info">Lab Specific</span>--><a ng-click="editItem (question, note)" class="btn btn-mini btn-primary" style="margin-left:5px;"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-show="note.edit" style="margin: 20px 0 ;display: block;">
										<textarea  ng-model="noteCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="note.edit" ng-click="saveEdit(question, noteCopy, note)" class="btn btn-success">Save</a>
										<a ng-show="note.edit" ng-click="cancelEdit(note)" class="btn btn-danger">Cancel</a><i ng-if="note.IsDirty" class="icon-spinnery-dealie spinner small"></i>
									</span>
								</li>
								<li>		
									<form ng-if="!noteCopy">
									 	<input type="hidden" value="note" name="question.TextType" ng-model="question.TextType" ng-update-hidden />
							        	<textarea ng-model="question.noteText" rows="2" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Note" ng-click="createNewNoteOrRec(question,question.Responses,false,'observation')"/>
								        <input class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Note Option" ng-click="createNewNoteOrRec(question,question.Responses,true,'observation')"/>
								        <i ng-if="question.savingNew" class="icon-spinnery-dealie spinner small"></i>
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