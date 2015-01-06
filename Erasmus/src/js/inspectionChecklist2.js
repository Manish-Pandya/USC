var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodModule','once'])
.filter('categoryFilter', function () {
	return function (items, category ) {
			if( !category ) return false;
			var i = items.length;
			var filtered = [];
			while(i--){
				var item = items[i];
				if( item.Master_hazard.indexOf(category) > -1 )	filtered.unshift( item );
			}
			return filtered;
			
	}
})
.filter('evaluateChecklist', function () {
	return function (questions, checklist) {
		
			checklist.completedQuestions = 0;
			if(!checklist.Questions) return questions;
			var i = checklist.Questions.length;

			while(i--){
				var question = checklist.Questions[i]
				if( !question.Responses ){
					question.isComplete = false;
				}
				else if( !question.Responses.Answer ){
					question.isComplete = false;
				}
				else if( question.Responses.Answer.toLowerCase() == "yes" || question.Responses.Answer.toLowerCase() == "n/a" ){
					question.isComplete = true;
					checklist.completedQuestions++;
				}
				//question is answered "no"
				else{
					//question has no deficiencies to select
					if( !question.Responses.DeficiencySelections ){
						question.isComplete = false;
					}
					//question has no deficiencies selected
					else if( !question.Responses.DeficiencySelections.length ){
						question.isComplete = false;
					}
					//question has one or more deficiencies selected
					else{
						question.isComplete = true;
						checklist.completedQuestions++;
					}
				}
			}
			return checklist.Questions;
	}
})

.factory('checklistFactory', function(convenienceMethods,$q,$rootScope,$timeout,$location,$anchorScroll){

	    var factory = {};
	    factory.inspection = [];

	    factory.getInspection = function( id )
	    {

	    	var deferred = $q.defer();
			//lazy load
			if(this.inspection.length){
				deferred.resolve(this.inspection);
			}else{
				var url = '../../ajaxaction.php?action=resetChecklists&id='+id+'&callback=JSON_CALLBACK';
		    	convenienceMethods.getDataAsDeferredPromise(url).then(
					function(promise){
						deferred.resolve(promise);
					},
					function(promise){
						deferred.reject();
					}
				);
			}
			deferred.promise.then(
				function(inspection){
					factory.inspection = inspection;
				}
			)
			return deferred.promise;

	    }

	    factory.setImage = function( category )
	    {
	    		if( category == 'Biological Safety' ){
	    				return 'biohazard-largeicon.png';
	    		}else if( category == 'Chemical Safety' ){
	    				return 'chemical-safety-large-icon.png';
	    		}else if( category == 'General Safety' ){
	    				return 'gen-hazard-large-icon.png';
	    		}else{
	    				return 'radiation-large-icon.png';
	    		}
	    }

	    factory.selectCategory = function( category )
	    {		
	    		$rootScope.loading = true;
	    		$rootScope.image = factory.setImage( category );	
				$rootScope.category = category;
				$rootScope.inspection = factory.inspection
				$rootScope.inspection.selectedChecklists = [];

	    		var len = factory.inspection.Checklists.length;
	    		var counter;
	    		var i = 0;
	    		var selectedChecklists = [];

	    		innerFilter();

	    		function innerFilter(){
	    			for( counter = 0; i < len && counter < 3; counter++, i++){
	    				var checklist = factory.inspection.Checklists[i];
		    			if( checklist.Master_hazard == category )selectedChecklists.push( checklist );
	    			}

	    			if(i == len){
	    				factory.inspection.selectedCategory = selectedChecklists;
			    		$rootScope.loading = false;
	    			}
	    			else{
	    				$timeout(innerFilter, 10);
	    			}

	    		}	    		
	    }

	    factory.evaluateCategories = function () 
	    {
	    		var i = this.inspection.Checklists.length;
	    		this.selectCategory( this.inspection.Checklists[0].Master_hazard );
	    		while(i--){
	    			var list = this.inspection.Checklists[i].Master_hazard;
	    			$rootScope[list.substring(0, list.indexOf(' ')).toLowerCase()] = true;
	    		}
	    }

	    factory.saveResponse = function(  question )
	    {
	    		var response = question.Responses;
	    		question.IsDirty = true;

	    		var url = '../../ajaxaction.php?action=saveResponse';

	    		responseDto = convenienceMethods.copyObject(response);
	    		if(!response.Inspection_id)responseDto.Inspection_id = this.inspection.Key_id;
	    		if(!response.Question_id)responseDto.Question_id = question.Key_id;
	    		responseDto.Class = "Response";

	    		if(!responseDto.Answer)responseDto.Answer = '';

	    		console.log(responseDto)

	    		var deferred = $q.defer();
				convenienceMethods.saveDataAndDefer(url, responseDto).then(
					function(promise){
						deferred.resolve(promise);
						deferred.promise
							.then(
								function(returnedResponse){
									question.IsDirty = false;
									response = convenienceMethods.copyObject( returnedResponse );
								}
							)
					},
					function(promise){
						question.IsDirty = false;
						deferred.reject();
						question.error = "The response could not be saved.  Please check your internet connection and try again."
					}
				);	

	    }

	    factory.evaluateDeficiency = function( id ){
	    		var i = this.inspection.Deficiency_selections[0].length;

	    		while(i--){
	    			if( id == this.inspection.Deficiency_selections[0][i] )return true;
	    		}
	    		return false;

	    }

	    factory.evaluateDeficienyShowRooms = function( id ){
	    		var i = this.inspection.Deficiency_selections[2].length;
	    		console.log(i);
	    		while(i--){
	    			console.log(this.inspection.Deficiency_selections[2][i] + ' | ' + id)
	    			if( id == this.inspection.Deficiency_selections[2][i] )return true;
	    		}
	    		return false;

	    }

	    factory.saveDeficiencySelection = function( deficiency, question, checklist, room )
	    {
	    		console.log(deficiency);
	    		deficiency.IsDirty = true;
	    		question.error =  null;

				if( !deficiency.InspectionRooms ) deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
				//grab a collection of room ids
				var i = deficiency.InspectionRooms.length;
				var roomIds = [];
				if(!room){
					//we haven't passed in a room, so we should set relationships for all possible rooms
					while(i--){
						roomIds.push( deficiency.InspectionRooms[i].Key_id );
					}
				}
				else{
					this.room = room;
					while(i--){
						if( deficiency.InspectionRooms[i].checked )roomIds.push( deficiency.InspectionRooms[i].Key_id );
					}
				}
				console.log(roomIds)

				var defDto = {
			        Class: "DeficiencySelection",
			        RoomIds: roomIds,
			        Deficiency_id:  deficiency.Key_id,
			        Response_id: question.Responses.Key_id,
			        Inspection_id: this.inspection.Key_id,
			        Show_rooms:  deficiency.Show_rooms
		      	}

	    		if( deficiency.selected || this.evaluateDeficiency( deficiency.Key_id ) ){

	    				if(question.Responses && question.Responses.DeficiencySelections){
	    					var j = question.Responses.DeficiencySelections.length;
	    					while(j--){
	    						var ds = question.Responses.DeficiencySelections[j];
	    						if(deficiency.Key_id == ds.Deficiency_id)defDto.Key_id = ds.Key_id;
	    					}
	    				}

				      	var url = '../../ajaxaction.php?action=saveDeficiencySelection'; 
						convenienceMethods.saveDataAndDefer(url, defDto)
							.then(
								function(returnedDeficiency){
									deficiency.IsDirty = false;
									deficiency.selected = true;
									//console.log(returnedDeficiency);
									factory.inspection.Deficiency_selections[0].push( deficiency.Key_id );
									if(!question.Responses.DeficiencySelections)question.Responses.DeficiencySelections = [];
									question.Responses.DeficiencySelections.push( returnedDeficiency );

									if(factory.room){
										var l = deficiency.InspectionRooms.length;
										var m = 0;
										while(l--){
											var room = deficiency.InspectionRooms.length[l];
											if( roomIds.indexOf( factory.room.Key_id ) > -1 ){
												factory.room.checked = true;
												m++
											}else{
												factory.room.checked = false;
											}
											//if no rooms are left checked for this deficiency, we remove it's key id from the Inspection's array of deficiency_selection ids
											if(m == 0)factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections.indexOf(deficiency.Key_id, 1 ) )

										}
									}

								},
								function(promise){
									question.IsDirty = false;
									deferred.reject();
									deficiency.selected = false;
									question.error = "The response could not be saved.  Please check your internet connection and try again."
								}
							);	
	    		}else{
					var j = question.Responses.DeficiencySelections.length;
					//get the key_id for our DeficiencySelection
					while(j--){
					if( question.Responses.DeficiencySelections[j].Deficiency_id == defDto.Deficiency_id ){
						  defDto.Key_id = question.Responses.DeficiencySelections[j].Key_id;
						  var defSelectIdx = j;
						} 
					}
	    			var url = '../../ajaxaction.php?action=removeDeficiencySelection';
      				convenienceMethods.saveDataAndDefer( url, defDto )
      					.then(
      						function(returnedBool){
      							deficiency.IsDirty = false;
								deficiency.selected = false;
								factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections[0].indexOf( deficiency.Key_id ), 1 );
				             	question.Responses.DeficiencySelections.splice( defSelectIdx, 1 );
      						},
      						function(error){
      							deficiency.IsDirty = false;
								deficiency.selected = true;
								question.error = "The response could not be saved.  Please check your internet connection and try again."
      						}
      					)
	    		}
	    }

	    factory.handleCorrectedDurringInspection = function( deficiency, question )
	    {
	    	deficiency.IsDirty = true;
		    var def_id = deficiency.Key_id;
		    deficiency.correctedDuringInspection = !deficiency.correctedDuringInspection
		    if( this.inspection.Deficiency_selections[1].indexOf(deficiency.Key_id > -1) ){
		      //we set corrected durring inpsection
		      var url = '../../ajaxaction.php?action=addCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+this.inspection.Key_id+'&callback=JSON_CALLBACK';
		    }else{
		      //we unset corrected durring inspection
		      var url = '../../ajaxaction.php?action=removeCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+this.inspection.Key_id+'&callback=JSON_CALLBACK';
		    }

		    convenienceMethods.getDataAsPromise( url )
		      	.then(
		      		function(){
		      			deficiency.correctedDuringInspection = !deficiency.correctedDuringInspection;
		      			deficiency.IsDirty = false;
		      		},
		      		function(){
		      			question.error = 'The deficiency could not be saved.  Please check your internet connection and try again.';
		      			deficiency.IsDirty = false;
		      		}
		      	);
	    }

	    factory.changeChecklist = function( checklist )
	    {
	    	checklist.currentlyOpen = !checklist.currentlyOpen;
	    	var insp = $location.search().inspection;
	    	//$location.hash(checklist.Key_id);
	    	$location.search('inspection',insp);
		    $anchorScroll();
	    }

	    factory.evaluateDeficiencyRoomChecked = function( room, question, deficiency )
	    {
	    	//console.log(deficiency);
	    	var i = question.Responses.DeficiencySelections.length;
    		while(i--){
    			if( question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id ){
    				var j = question.Responses.DeficiencySelections[i].Rooms.length;
    				while(j--){
    					if( question.Responses.DeficiencySelections[i].Rooms[j].Key_id == room.Key_id ){
    						return true;
    					}
    				}
    			}
    		}
    		return false;
	    }

	    return factory;
});

function checklistController($scope,  $location, $anchorScroll, convenienceMethods, $window, checklistFactory) {

	$scope.cf = checklistFactory;

	if($location.search().inspection){
      $scope.inspId = $location.search().inspection;
      checklistFactory.getInspection( $scope.inspId )
      	.then(
      		function( inspection ){
      			checklistFactory.evaluateCategories();
      		},
      		function( error ){
      			$scope.error = "The system couldn't find the selected inspeciton.  Please check your internet connection and try again."
      		}
      	)
  	}else{
  		$scope.error = "No inspection specified."
  	}

	$scope.showRooms = function( event, deficiency, element, checklist, question ){
	    if(!deficiency.InspectionRooms){
	        //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
	        deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
	    }
	   // checklistFactory.evaluateDeficiecnyRooms( question, checklist );

	    event.stopPropagation();
	    calculateClickPosition(event,deficiency,element);
	    deficiency.showRoomsModal = !deficiency.showRoomsModal;
  	}

	//get the position of a mouseclick, set a properity on the clicked hazard to position an absolutely positioned div
	function calculateClickPosition(event, deficiency, element){
		console.log(deficiency);
		var x = event.clientX;
		var y = event.clientY+$window.scrollY;

		deficiency.calculatedOffset = {};
		deficiency.calculatedOffset.x = x-110;
		deficiency.calculatedOffset.y = y-185;
	} 


}
