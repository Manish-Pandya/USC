<?php
require_once '../top_view.php';
?>

<div ng-app="inspectionChecklist" ng-controller="ChecklistController">
	<div id="sp-nav" class="span3">
		<a class="menuIcon" ng-click="$spMenu.toggle()">&#9776;</a>
        <ul class="nav nav-list nav nav-pills nav-stacked" id="sideNav">
          <li class="nav-header" style="font-size: 30px;padding: 7px 45px;">Checklists</li>
          <li ng-repeat="checklist in checklists" ><a ng-class="{active:checklist.currentlyOpen}" ng-click="change(checklist.key_id,checklist)" href="#{{checklist.key_id}}"><span style="display:inline-block; width:75%; margin-right:10%;">{{checklist.Name}}</span><span ng-class="checklist.countClass" style="width: 15%;float:right; text-align:right;">{{checklist.AnsweredQuestions}}/{{checklist.Questions.length}}</span></a></li>
        </ul>
    </div><!--/span-->
<div class="tst">
<div id="sp-page" class="whitebg">
	<div style="position:fixed">
	</div>

<script src="../../js/inspectionChecklist.js"></script>

<div class="navbar">    		
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="span12">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Inspection Checklist</h2>	
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
    <!-- begin checklist for this inspection -->
		<accordion close-others="true" ng-hide="!checklists">
			<accordion-group ng-class="{active:checklist.currentlyOpen}" class="checklist" ng-repeat="checklist in checklists" is-open="checklist.open">
				<accordion-heading>
					<span style="margin-top:20px;" id="{{checklist.key_id}}"></span>
					<input type="hidden" ng-model="checklist.AnsweredQuestions" ng-init="checklist.AnsweredQuestions = '0'"/>
					<h2>{{checklist.Name}}<span style="float:right" ng-class="checklist.countClass">{{checklist.AnsweredQuestions}}/{{checklist.Questions.length}}</span></h2>
				</accordion-heading>
		     	<ul style="margin-left:0;">	
		     		<li class="question" ng-repeat="question in checklist.Questions">
		     			<h3 style="width:45%; float:left;"><img ng-show="question.IsDirty" class="smallLoading" src="../../img/loading.gif"/>{{question.Text}}</h3>
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
										<input type="checkbox" value="true" ng-model="deficiency.checked" ng-change="deficiencySelected(question.Responses, deficiency)" />
										<span class="metro-checkbox"><img ng-show="deficiency.IsDirty" class="smallLoading" src="../../img/loading.gif"/>{{deficiency.Text}}</span>
									</label>
									<ul style="margin:10px" ng-switch on="deficiency.checked">
										<li ng-switch-when="true">
											<label class="checkbox inline">
												<input type="checkbox" value="true" ng-model="deficiency.correctedDuringInspection" ng-change="def(checklist, question.Responses, question)" />
												<span class="metro-radio">corrected during inpsection</span>
											</label>
										</li>
									</ul>
								</li>
							</ul>
						</span>

						<span ng-hide="!question.Responses.Answer" ng-switch on="question.showRecommendations">
							<ul ng-switch-when="true" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Recommendations:</h3>
								<li ng-repeat="recommendation in question.Recommendations" style="margin-bottom:3px;">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-model="recommendation.checked" ng-change="handleNotesAndRecommendations(question, recommendation)" />
										<span class="metro-checkbox">{{recommendation.Text}}<img ng-show="recommendation.IsDirty" class="smallLoading" src="../../img/loading.gif"/><span ng-show="recommendation.persist" class="label label-success" style="margin-left:3px;">New Option</span><span style="margin-left:3px;" ng-hide="recommendation.persist || !recommendation.isNew" class="label label-info">Lab Specific</span></span>
									</label>
								</li>
								<li>
									 <form>
									 	<input type="hidden" value="recommendation" name="question.TextType" ng-model="question.TextType" ng-update-hidden />
							        	<textarea ng-model="question.recommendationText" rows="6" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Recommendation" ng-click="createNewNoteOrRec(question,question.Responses,false,'recommendation')"/>
								        <input class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Recommendation Option" ng-click="createNewNoteOrRec(question,question.Responses,true,'recommendation')"/>
								    </form>
								</li>
							</ul>
						</span>

						<span ng-hide="!question.Responses.Answer" ng-switch on="question.showNotes">
							<ul ng-switch-when="true" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Notes:</h3>
								<li ng-repeat="note in question.Observations" style="margin-bottom:3px;">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-model="note.checked" ng-change="handleNotesAndRecommendations(question, note)"/>
										<span class="metro-checkbox">{{note.Text}}<img ng-show="note.IsDirty" class="smallLoading" src="../../img/loading.gif"/><span style="margin-left:3px;" ng-show="note.persist" class="label label-success">New Option</span><span style="margin-left:3px;" ng-hide="note.persist  || !note.isNew" class="label label-info">Lab Specific</span></span>
									</label>
								</li>
								<li>		
									<form>
									 	<input type="hidden" value="note" name="question.TextType" ng-model="question.TextType" ng-update-hidden />
							        	<textarea ng-model="question.noteText" rows="6" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-info" type="submit" style="height:50px" value="Save as Lab-Specific Note" ng-click="createNewNoteOrRec(question,question.Responses,false,'observation')"/>
								        <input class="btn btn-large btn-success" type="submit" style="height:50px" value="Save as Note Option" ng-click="createNewNoteOrRec(question,question.Responses,true,'observation')"/>
								    </form>					
								</li>
							</ul>
						</span>
		     		</li>
		     	</ul>
		    </accordion-group>
			<a class="btn btn-large btn-success" href="InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}">View Interim Report</a>
		</accordion>

	</div>

	</div>
</div>
<div style="clear:both"></div>
</div>
<script>
/*
//SHOW/HIDE DEFICIENCIES FOR A QUESTION
$(':radio').on('click', function() {
   val = $(this).val();
   if(val == "No"){
		$(this).closest('.question').children('.deficiencies').show();
	}else{
		$(this).closest('.question').children('.deficiencies').hide();
	}
});
//SHOW/HIDE RECOMMENDATIONS FOR A QUESTION
$('.showHideRecommendations').on('click', function() {
	   val = $(this).val();
	   if(val == "hide"){
			$(this).closest('.question').children('.recommendations').hide();
			$(this).val('show');
			$(this).Text('Show Recommendations');
		}else{
			$(this).closest('.question').children('.recommendations').show();
			$(this).val('hide');
			$(this).Text('Hide Recommendations');
		}
});

//HIDE LEFT NAV, DEFICIENCIES AND RECOMMENDATIONS
$(document).ready(function(){
	$('#sidebar').hide();
	$('.deficiencies').hide();	
	$('.recommendations').hide();
	$('.toggles').offset({top: 361});
	offset = $('.whitebg').offset();
	$('.toggles').offset({left: offset.left - 10});
});
//SHOW LEFT NAV, CHANGE DISPLAY OF LEFT NAVE HIDE/REVEAL ICON ICON
$(document.body).on("click", "a.toggledIn", function(){
    $('a.toggles i').addClass('icon-arrow-up');
    $('a.toggles i').removeClass('icon-arrow-down');
    $('a.toggles').addClass('toggledOut');
    $('a.toggles').removeClass('toggledIn');

    $('#sidebar').animate({
        width: '22%',
        marginRight:  '1.5%',
        marginLeft:	'1.5%'
    }, 10)
    .show();

    $('#inspectionChecklist').animate({
        width: '72%',
        marginRight:  '3%',
        marginLeft:	'0px'
    }, 10);

    $('#sidebar').offset({top: 161});
    $('.toggles').offset({top: 361});
});

//HIDE LEFT NAV, CHANGE DISPLAY OF LEFT NAVE HIDE/REVEAL ICON ICON
$(document.body).on("click", "a.toggledOut", function(){
    $('a.toggles i').addClass('icon-arrow-down');
    $('a.toggles i').removeClass('icon-arrow-up');
    $('a.toggles').addClass('toggledIn');
    $('a.toggles').removeClass('toggledOut');
    
    $('#sidebar').animate({
        width: '0px',
        marginRight:  '0px',
        marginLeft:	'0px'
    }, 10,
    function() {
    	 $('#sidebar').hide();
      });
 
    $('#inspectionChecklist').animate({
        width: '98.5%',
        marginRight:  '0px',
        marginLeft:	'1.5%'
    },10);
});
//KEEP SIDEBAR IN WINDOW
$(window).scroll(function(){ // scroll event
    var windowTop = $(window).scrollTop(); // returns number 
    console.log(windowTop);
    $('#sidebar').offset({top: windowTop + 161});
    $('.toggles').offset({top: windowTop + 361});
  });
$(window).resize(function() {
	offset = $('.whitebg').offset();
	$('.toggles').offset({left: offset.left - 10});
});
$("#sideNav li").click(function(){
	$("#sideNav li").removeClass('active');
	$(this).addClass('active');
})
$(document.body).on("change", "input.toggler", function(){	
	$(this).closest('.checkbox').find('div:first').toggleClass('hide shadow');
});
*/
</script>