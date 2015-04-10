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
				<a ng-click="cf.selectCategory('General Hazards')" class="checklistListNavHeader" id="generalSafetyHeader"><img src="../../img/gen-hazard-large-icon.png"/><span>GENERAL SAFETY</span></a>
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
			<h2  style="padding: 11px 0 5px 85px;">Inspection Checklist  <span style="margin-left:10px;" ng-if="inspection">({{inspection.PrincipalInvestigator.User.Name}})</span>
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
</div>
<div class="row-fluid">
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
		<li ng-show="general"><a ng-click="cf.selectCategory('General Hazards')" class="btn btn-large checklistNav" id="generalSafetyHeader" ng-class="{selected: category.indexOf('General') > -1}"><img src="../../img/gen-hazard-large-icon.png"/><span>GENERAL SAFETY</span></a></li>
		<li ng-show="radiation"><a ng-click="cf.selectCategory('Radiation Safety')" class="btn btn-large checklistNav"  id="radiationSafetyHeader" ng-class="{selected: category.indexOf('Radiation') > -1}"><img src="../../img/radiation-large-icon.png"/><span>RADIATION SAFETY</span></a></li>
		<li ng-if="inspection" class="pull-right" style="float:right; margin-right:30px"><a ng-click="openNotes()" class="btn btn-large btn-info left checklistNav" ><i class="icon-clipboard-2"></i>Inspection Comments</a></li>
	</ul>
	<div class="loading" ng-show='loading' style="margin-left:11px;">
	  <i class="icon-spinnery-dealie spinner large"></i> 
	  <span>Getting Checklist Category...</span>
	</div>
	<!-- todo:  write function to get image path -->
	<h2 ng-if="category && !loading" style="margin-left:11px; font-weight:bold"><img style="margin: -6px 5px 4px 0; max-width:50px;" src="../../img/{{image}}"/><span>{{category}}</span></h2>
	
    <!-- begin checklist for this inspection -->
		<accordion close-others="true" ng-hide="loading">
			<accordion-group ng-show="checklist.activeQuestions.length"  ng-class="{active:checklist.currentlyOpen}" class="checklist" ng-repeat="checklist in inspection.selectedCategory | activeOnly" is-open="checklist.currentlyOpen" id="{{checklist.Key_id}}">
				<accordion-heading>
					<span style="margin-top:20px;" id="{{checklist.key_id}}"></span>
					<input type="hidden" ng-model="checklist.AnsweredQuestions"/>
					<h2><span once-text="checklist.Name"></span><span style="float:right" ng-class="{'red' : checklist.completedQuestions>0&&checklist.completedQuestions<checklist.activeQuestions.length, 'green' : checklist.completedQuestions==checklist.activeQuestions.length&&checklist.completedQuestions!=0}">{{checklist.completedQuestions}}/{{checklist.activeQuestions.length}}</span></h2>
				</accordion-heading>
		     	<ul style="margin-left:0;">	
		     		<li class="question" ng-repeat="question in checklist.Questions | evaluateChecklist:checklist">
		     			<!--call evaluateDeficiecnyRooms -->
		     			<h3 style="width:30px; float:left">{{$index+1}}.</h3>
		     			<h3 style="width:65%; float:left;">
		     				<i class="icon-spinnery-dealie spinner small" ng-if="question.IsDirty"></i>
		     				<span once-text="question.Text"></span><br>
		     				<span class="checklistQuestionError" ng-if="question.error">{{question.error}}</span>
		     			</h3>
		     			<div class="questionAnswerInputs">
	     					<label class="checkbox inline">
								<input type="checkbox" ng-true-value="yes" ng-model="question.Responses.Answer" ng-change="cf.saveResponse( question )"/>
								<span class="metro-radio">Yes</span>
							</label>
							<label class="checkbox inline" ng-class="{'disabled': !question.Deficiencies.length}">
								<input type="checkbox" ng-disabled="!question.activeDeficiencies.length" ng-true-value="no" ng-model="question.Responses.Answer" ng-change="cf.saveResponse( question )"/>
								<span class="metro-radio">No</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox"  ng-true-value="n/a" ng-model="question.Responses.Answer" ng-change="cf.saveResponse( question )"/>
								<span class="metro-radio">N/A</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox" ng-init="question.showRecommendations = question.Responses.Recommendations.length>0" ng-model="question.showRecommendations" ng-change="cf.showRecommendations(question)" />
								<span class="metro-checkbox">Recommendations</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox" value="true" ng-model="question.showNotes" ng-disabled="!question.Responses.Answer"/>
								<span class="metro-checkbox">Notes</span>
							</label>
						</div>
						<span style="clear:both; display:block; height:0;">&nbsp;</span>
						<span ng-hide="!question.activeDeficiencies.length" ng-switch on="question.Responses.Answer">
							<ul ng-show="question.Responses.Answer == 'no'" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Deficiencies:</h3>
								<li ng-repeat="deficiency in question.activeDeficiencies = ( question.Deficiencies | activeOnly )">
									<label class="checkbox inline">
										<input type="checkbox" ng-model="deficiency.selected" ng-change="cf.saveDeficiencySelection( deficiency, question, checklist )" ng-checked="cf.evaluateDeficiency( deficiency.Key_id )"/>
										<span class="metro-checkbox"><i ng-if="deficiency.IsDirty" class="icon-spinnery-dealie spinner small deficiencySpinner"></i><span style="margin-top:0" once-text="deficiency.Text"></span></span>
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
												</label>
											</li>
											<li ng-repeat="room in deficiency.InspectionRooms">
												<label class="checkbox inline">
													<input type="checkbox" ng-checked="cf.evaluateDeficiencyRoomChecked( room, question, deficiency )" ng-change="cf.saveDeficiencySelection( deficiency, question, checklist, room )" ng-model="room.checked"/>
													<span class="metro-checkbox"><span once-text="room.Name"></span><i ng-if="room.IsDirty" class="icon-spinnery-dealie spinner small"></i></span>
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

						<span>
							<ul ng-if="question.showRecommendations" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;" class="recOrObsList">
								<h3>Recommendations:<a ng-if="!question.addRec" style="margin-left: 5px" class="btn btn-success" ng-click="question.addRec = true"><i class="icon-plus-2"></i></a></h3>
								<li ng-repeat="recommendation in question.Recommendations | activeOnly" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-if="!recommendation.edit">
										<input type="checkbox" value="true" ng-model="recommendation.checked" ng-checked="cf.getRecommendationChecked(question, recommendation)" ng-change="cf.saveRecommendationRelation(question, recommendation)" />
										<span class="metro-checkbox standardRecOrObs" ng-class="{newRecOrObs:recommendation.new}"><span once-text="recommendation.Text"></span><i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small absolute"></i><!--<span ng-show="recommendation.isNew" class="label label-success" style="margin-left:3px;">New Option</span>--><a ng-if="recommendation.new" ng-click="cf.copyForEdit(question, recommendation)" class="btn btn-mini btn-primary" style="margin-left:5px;" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-if="recommendation.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-model="RecommendationCopy.Text" style="width:50%"></textarea><br>
										<a ng-click="cf.saveRecommendation(question, recommendation)" class="btn btn-success">Save</a>
										<a ng-click="cf.objectNullifactor(recommendation, question)" class="btn btn-danger">Cancel</a>
										<i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small"></i>
									</span>
								</li>
								<li ng-repeat="recommendation in question.Responses.SupplementalRecommendations | activeOnly" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-if="!recommendation.edit" >
										<input type="checkbox" value="true" ng-model="recommendation.checked" ng-init="recommendation.checked = recommendation.Is_active" ng-change="cf.supplementalRecommendationChanged(question, recommendation)" />
										<span class="metro-checkbox labSpecific" ng-class="{edit:recommendation.edit}">{{recommendation.Text}}<i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small"></i><!--<span style="margin-left:3px;" class="label label-info">Lab Specific</span>--><a ng-click="cf.copyForEdit(question, recommendation)" class="btn btn-mini btn-primary" style="margin-left:5px;" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-if="recommendation.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-model="SupplementalRecommendationCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="recommendation.edit" ng-click="cf.saveSupplementalRecommendation(question, false, recommendation)" class="btn btn-success">Save</a>
										<a ng-show="recommendation.edit" ng-click="cf.objectNullifactor(recommendation, question)" class="btn btn-danger">Cancel</a><i ng-if="recommendation.IsDirty" class="icon-spinnery-dealie spinner small"></i>
									</span>
								</li><!--editItem = function(item, question)-->
								<li ng-if="question.addRec">
									 <form ng-if="!question.edit">
							        	<textarea ng-model="question.newRecommendationText" rows="2" style="width:100%;"></textarea>
								        <input  class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Recommendation" ng-click="cf.saveSupplementalRecommendation(question, true)"/>
								        <input  class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Recommendation Option" ng-click="cf.createRecommendation(question)"/>
								    	<i ng-if="question.savingNew" class="icon-spinnery-dealie spinner small"></i>
								    	<a class="btn btn-large btn-danger" ng-click="question.addRec = false;">Cancel</a>
								    </form>
								</li>
							</ul>
						</span>

						<span ng-hide="!question.Responses.Answer" ng-switch on="question.showNotes">
							<ul ng-switch-when="true" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;" class="recOrObsList">
								<h3>Notes:</h3>
								<li ng-repeat="note in question.Observations | activeOnly" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-if="!note.edit">
										<input type="checkbox" value="true" ng-if="!note.edit" ng-model="note.checked" ng-checked="cf.getObservationChecked(question, note)" ng-change="cf.saveObservationRelation(question, note)"/>
										<span class="metro-checkbox" ng-class="{newRecOrObs:note.new}">{{note.Text}}<i ng-if="note.IsDirty" class="icon-spinnery-dealie spinner small absolute"></i><!--<span style="margin-left:3px;" ng-show="note.isNew" class="label label-success">New Option</span>--><a ng-if="note.new" ng-click="cf.copyForEdit(question, note)" class="btn btn-mini btn-primary" style="margin-left:5px;" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-if="note.edit" style="margin: 20px 0 ;display: block;">
										<textarea ng-show="note.edit" ng-model="ObservationCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="note.edit" ng-click="cf.saveObservation(question, note)" class="btn btn-success">Save</a>
										<a ng-show="note.edit" ng-click="cf.objectNullifactor(note, question)" class="btn btn-danger">Cancel</a>
									</span>
								</li>
								<li ng-repeat="note in question.Responses.SupplementalObservations | activeOnly" style="margin-bottom:3px;">
									<label class="checkbox inline" ng-show="!note.edit">
										<input type="checkbox" value="true" ng-model="note.checked" ng-init="note.checked = note.Is_active" ng-change="cf.supplementalObservationChanged(question, note)"/>
										<span class="metro-checkbox labSpecific">{{note.Text}}|{{note.Class}}<i ng-if="note.IsDirty" class="icon-spinnery-dealie spinner small"></i><!--<span style="margin-left:3px;" class="label label-info">Lab Specific</span>--><a ng-click="cf.copyForEdit(question, note)" class="btn btn-mini btn-primary" style="margin-left:5px;" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i></a></span>
									</label>
									<span ng-if="note.edit" style="margin: 20px 0 ;display: block;">
										<textarea  ng-model="SupplementalObservationCopy.Text" style="width:50%"></textarea><br>
										<a ng-show="note.edit" ng-click="cf.saveSupplementalObservation(question, false, note)" class="btn btn-success">Save</a>
										<a ng-show="note.edit" ng-click="cf.objectNullifactor(note, question)" class="btn btn-danger">Cancel</a><i ng-if="note.IsDirty" class="icon-spinnery-dealie spinner small"></i>
									</span>
								</li>
								<li>		
									<form ng-if="!question.edit">
									 	<input type="hidden" value="note" name="question.TextType" ng-model="question.TextType" ng-update-hidden />
							        	<textarea ng-model="question.newObservationText" rows="2" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Note" ng-click="cf.saveSupplementalObservation(question, true)"/>
								        <input class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Note Option" ng-click="cf.createObservation(question)"/>
								        <i ng-if="question.savingNew" class="icon-spinnery-dealie spinner small"></i>
								    </form>					
								</li>
							</ul>
						</span>
		     		</li>
		     		<div style="clear:both"></div>
		     	</ul>
		    </accordion-group>
		    <a class="btn btn-large btn-success" ng-if="Inspection || inspection" style="margin:0 10px 10px" href="InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}">View Interim Report</a>
		</accordion>
	</div>
	</div>
</div>
</div>