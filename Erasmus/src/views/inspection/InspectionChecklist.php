
<?php
require_once '../top_view.php';
?>

<div ng-app="inspectionChecklist" ng-controller="ChecklistController">
	<div id="sp-nav" class="span3">
		<a class="menuIcon" ng-click="$spMenu.toggle()">&#9776;</a>
        <ul class="nav nav-list nav nav-pills nav-stacked" id="sideNav">
          <li class="nav-header" style="font-size: 30px;padding: 7px 45px;">Checklists</li>
          <li ng-repeat="checklist in checklists" ><a ng-class="{active:checklist.currentlyOpen}" ng-click="change(checklist.key_id,checklist)" href="#{{checklist.key_id}}"><span style="display:inline-block; width:75%; margin-right:10%;">{{checklist.label}}</span><span ng-class="checklist.countClass" style="width: 15%;float:right; text-align:right;">{{checklist.answeredQuestions}}/{{checklist.questions.length}}</span></a></li>
        </ul>
    </div><!--/span-->
<div class="test">
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
    <!-- begin checklist for this inspection -->
		<accordion >
			<accordion-group ng-class="{active:checklist.currentlyOpen}" class="checklist" ng-repeat="checklist in checklists" is-open="checklist.open">
				<accordion-heading>
					<span style="margin-top:20px;" id="{{checklist.key_id}}"></span>
					<h2>{{checklist.label}}<span style="float:right" ng-class="checklist.countClass">{{checklist.answeredQuestions}}/{{checklist.questions.length}}</span></h2>
				</accordion-heading>
		     	<ul style="margin-left:0;">	
		     		<li class="question" ng-repeat="question in checklist.questions">
		     			<h3 style="width:65%; float:left;">{{question.text}}</h3>
		     			<div class="questionAnswerInputs">
	     					<label class="radio inline">
								<input type="radio" value="true" ng-model="question.userResponse.answer" ng-change="questionAnswered(checklist, question.userResponse, question)" />
								<span class="metro-radio">Yes</span>
							</label>
							<label class="checkbox inline">
								<input type="radio" value="false" ng-model="question.userResponse.answer" ng-change="questionAnswered(checklist, question.userResponse, question)" />
								<span class="metro-radio">No</span>
							</label>
							<label class="radio inline">
								<input type="radio" value="n/a" ng-model="question.userResponse.answer" ng-change="questionAnswered(checklist, question.userResponse, question)" />
								<span class="metro-radio">N/A</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox" value="true" ng-model="question.showRecommendations"  />
								<span class="metro-checkbox">Recommendations</span>
							</label>
							<label class="checkbox inline">
								<input type="checkbox" value="true" ng-model="question.showNotes" />
								<span class="metro-checkbox">Notes</span>
							</label>
						</div>
					<span style="clear:both; display:block; height:0;">&nbsp;</span>

						<span ng-switch on="question.userResponse.answer">
							<ul ng-switch-when="false" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Deficiencies:</h3>
								<li ng-repeat="deficiency in question.deficiencies">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-model="deficiency.checked" ng-change="questionAnswered(checklist, question.userResponse, question)" />
										<span class="metro-checkbox">{{deficiency.text}}</span>
									</label>
									<ul ng-switch on="deficiency.checked">
										<li ng-switch-when="true">
											<label class="checkbox inline">
												<input type="checkbox" value="true" ng-model="deficiency.correctedDuringInspection" ng-change="questionAnswered(checklist, question.userResponse, question)" />
												<span class="metro-checkbox">corrected during inpsection</span>
											</label>
										</li>
									</ul>
								</li>
							</ul>
						</span>

						<span ng-switch on="question.showRecommendations">
							<ul ng-switch-when="true" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Recommendations:</h3>
								<li ng-repeat="recommendation in question.recommendations">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-model="recommendation.checked" ng-change="questionAnswered(checklist, question.userResponse, question)" />
										<span class="metro-checkbox">{{recommendation.text}}</span>
									</label>
								</li>
								<li>
									 <form ng-submit="handleNotesAndRecommendations(question)">
									 	<input type="hidden" value="recommendation" name="question.textType" ng-model="question.textType" ng-update-hidden />
							        	<textarea ng-model="question.recommendationText" rows="6" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-primary" type="submit" style="height:50px" value="Save Recommendation"/>
								    </form>
								</li>
							</ul>
						</span>

						<span ng-switch on="question.showNotes">
							<ul ng-switch-when="true" style="padding: 20px 0px;margin: 20px 0;border-top: 1px solid #ccc;">
								<h3>Notes:</h3>
								<li ng-repeat="note in question.notes">
									<label class="checkbox inline">
										<input type="checkbox" value="true" ng-model="note.checked"/>
										<span class="metro-checkbox">{{note.text}}</span>
									</label>
								</li>
								<li>		
									<form ng-submit="handleNotesAndRecommendations(question)">
										<input type="hidden" value="note" name="question.textType" ng-model="question.textType" ng-update-hidden />
							        	<textarea ng-model="question.noteText" rows="6" style="width:100%;"></textarea>
								        <input class="btn btn-large btn-primary" type="submit" style="height:50px" value="Save Note"/>
								    </form>							
								</li>
							</ul>
						</span>



		     		</li>
		     	</ul>
		    </accordion-group>
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
			$(this).text('Show Recommendations');
		}else{
			$(this).closest('.question').children('.recommendations').show();
			$(this).val('hide');
			$(this).text('Hide Recommendations');
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