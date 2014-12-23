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

.factory('checklistFactory', function(convenienceMethods,$q,$rootScope,$timeout){

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
	    /*function filter() {
        var i=0, filtered = [];
        innerFilter();
        
        function innerFilter() {
            var counter;
            for( counter=0; i < $scope.data.length && counter < 5; counter++, i++ ) {
                // REAL FILTER LOGIC; BETTER SPLIT TO ANOTHER FUNCTION //
                if( $scope.data[i].indexOf($scope.filter) >= 0 ) {
                    filtered.push($scope.data[i]);
                }
                /////////////////////////////////////////////////////////
            }
            if( i === $scope.data.length ) {
                $scope.filteredData = filtered;
                $scope.filtering = false;
            }
            else {
                $timeout(innerFilter, 10);
            }
        }
    }*/

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
	    				console.log(counter);
	    				console.log('i: '+i);
	    				console.log(checklist);

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
	    		if(!response.Inspection_id)response.Inspection_id = this.inspection.Key_id;
	    		if(!response.Question_id)response.Question_id = question.Key_id;

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

	    factory.evaluateDeficieny = function( id ){
	    		var i = this.inspection.Deficiency_selections[0].length;

	    		while(i--){
	    			if( id == this.inspection.Deficiency_selections[0][i] )return true;
	    		}
	    		return false;

	    }

	    factory.saveDeficiencySelection = function( deficiency, question, checklist )
	    {
	    		deficiency.IsDirty = true;
	    		question.error =  null;

				if( !deficiency.InspectionRooms ) deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );

					//grab a collection of room ids
					var i = deficiency.InspectionRooms.length;
					var roomIds = [];
					while(i--){
						roomIds.push( deficiency.InspectionRooms[i].Key_id );
					}

				var defDto = {
			        Class: "DeficiencySelection",
			        RoomIds: roomIds,
			        Deficiency_id:  deficiency.Key_id,
			        Response_id: question.Responses.Key_id,
			        Inspection_id: this.inspection.Key_id
		      	}
	    		if(deficiency.selected){
	    				
				      	var url = '../../ajaxaction.php?action=saveDeficiencySelection'; 
						convenienceMethods.saveDataAndDefer(url, defDto)
							.then(
								function(returnedDeficiency){
									deficiency.IsDirty = false;
									deficiency.selected = true;
									factory.inspection.Deficiency_selections[0].push( deficiency.Key_id );
									question.Responses.DeficiencySelections.push( returnedDeficiency );
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

	    return factory;
});

function ChecklistController($scope,  $location, $anchorScroll, convenienceMethods, $window, checklistFactory) {

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

	//watcher for selected category
	$scope.$watch('category', function() {

	});


}
