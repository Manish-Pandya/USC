var hazardInventory = angular.module('hazardAssesment', ['ui.bootstrap','convenienceMethodModule','once']);

hazardInventory.directive('hazardLi', ['$window', function($window) {
    return {
      restrict: 'C',
      link: function(scope, elem, attrs) {

        scope.onResize = function() {
          w = elem.width();
          checkbox = $(elem).find($('.targetHaz span'));
          label = $(elem).find($('label'));
        }

        scope.$watch(
          function(){
              return scope.onResize();
          }
        )

        angular.element($window).bind('resize', function() {
          scope.onResize();
        });
      }
    }
}])

hazardInventory.factory('hazardInventoryFactory', function(convenienceMethods,$q){
	var factory = {};
	var PI = {};
	var allPis = [];
	factory.getAllPis = function(){

		//lazy load
		if(this.allPis)return this.allPis;

		//if we don't have a the list of pis, get it from the server
		var deferred = $q.defer();
		var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
    	convenienceMethods.getDataAsDeferredPromise(url).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);
		return deferred.promise;
    }

	factory.getPi = function(id){
    console.log('id passed to getPi in factory ' + id);

		//if we don't have a pi, get one from the server
		var deferred = $q.defer();

    //lazy load
    if(this.PI && this.PI.Key_id == id){
      deferred.resolve( this.PI );
		}else{
      //eager load
			var url = '../../ajaxaction.php?action=getPIById&id='+id+'&callback=JSON_CALLBACK';
	    	convenienceMethods.getDataAsDeferredPromise(url).then(
				function(promise){
					this.PI = promise;
					deferred.resolve(promise);
				},
				function(promise){
					deferred.reject();
				}
			);
	  }
		return deferred.promise;
	}

  factory.getHazards = function(rooms){
    //on page load, get the collection of hazards for all of the PI's rooms 
    var deferred = $q.defer();
    var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';
    var temp = this;
    convenienceMethods.getDataAsDeferredPromise(url).then(
      function(promise){
        deferred.resolve(promise);
      },
      function(promise){
        deferred.reject();
      }
    );  
    return deferred.promise;
  }

  factory.resetInspectionRooms = function(roomIds, inspectionId){

    //we have changed the room collection for this inspection, so we set the new relationships on the server and get back and new collection of hazards  
    var deferred = $q.defer();
    
    var url = '../../ajaxaction.php?action=resetInspectionRooms&inspectionId='+inspectionId+'&'+$.param({roomIds:roomIds})+'&callback=JSON_CALLBACK';
    
    var temp = this;
    convenienceMethods.getDataAsDeferredPromise(url).then(
      function(promise){
        deferred.resolve(promise);
      },
      function(promise){
        deferred.reject();
      }
    );  
    return deferred.promise;
  }

	factory.setAllPis = function(PIs){
		this.PIs = PIs;
	}

	factory.setPi = function(PI){
		this.PI = PI;
	}

	factory.initialiseInspection = function(PIKeyID, inspectorIds, inspectionId){
		//if we don't have a pi, get one from the server
		var deferred = $q.defer();
		
		var url = '../../ajaxaction.php?callback=JSON_CALLBACK&action=initiateInspection&piId='+PIKeyID+'&'+$.param({inspectorIds:inspectorIds})+'&inspectionId='+inspectionId;
		var temp = this;
    	convenienceMethods.getDataAsDeferredPromise(url).then(
			function( inspection ){
        //if the PI doesn't have any rooms, we should reject the promise and let the controller know why
        if(!inspection.Rooms.length){
          deferred.reject(true);
        }else{
          console.log( inspection );
          //we have a good inspection with a collection of rooms
          deferred.resolve(inspection); 
        }
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise;
	}

	factory.getInspection = function(){
		return this.Inspection
	}

	factory.setInspection = function(inspection){
		this.Inspection = inspection;
	}

	factory.setHazarRoomRelations = function(hazard){
		console.log(hazard);
		var url = "../../ajaxaction.php?action=saveHazardRoomRelations";
      var deferred = $q.defer();

      convenienceMethods.saveDataAndDefer(url, hazard).then(
        function(promise){
          deferred.resolve(promise);
        },
        function(promise){
          deferred.reject(promise);
        }
      );
      return deferred.promise
	}

	factory.setSingleHazardRoomRelations = function(hazard, room, add){
		var url = "../../ajaxaction.php?action=saveHazardRelation&roomId="+room.Key_id+"&hazardId="+hazard.Key_id+"&add="+add+'&callback=JSON_CALLBACK';
		var deferred = $q.defer();
		convenienceMethods.getDataAsDeferredPromise(url).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise
	}

	factory.getSubHazards = function(hazard){
		var url = "../../ajaxaction.php?action=getSubHazards";
		var deferred = $q.defer();
		convenienceMethods.saveDataAndDefer(url, hazard).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise
	}
  //invert a collection of room objects with building properties to transform it into a collection of building objects with rooms collection properties
  factory.parseBuildings = function(rooms)
  {
      var buildings = [];
      var roomsLength = rooms.length;
      for(var i = 0; i < roomsLength; i++){
        var room = rooms[i];

        //on page load, all rooms are checked
        room.IsSelected = true;

        if(!convenienceMethods.arrayContainsObject(buildings, room.Building)){
          buildings.push(room.Building);
        }

        var idx = convenienceMethods.arrayContainsObject(buildings, room.Building, null, true);
        var building = buildings[idx];

        building.IsChecked = true;
        
        if(!building.Rooms)building.Rooms = [];
        building.Rooms.push(room);

      }

      return buildings;
  }

  factory.parseHazards = function( hazards )
  {
    angular.forEach( hazards, function( hazard, key ){
      factory.getShowRooms( hazard );
    });

    return hazards;
  }

  factory.getShowRooms = function( hazard )
  {
    //determine whether the hazard is present in some but NOT all of the rooms
    if(hazard.ActiveSubHazards){
     hazard.showRooms = false;
      angular.forEach(hazard.InspectionRooms, function(room, key){
        if(!hazard.InspectionRooms.every( factory.roomDoesNotContainHazard ) && !hazard.InspectionRooms.every( factory.roomContainsHazard )){
          hazard.showRooms = true;
        }else{
          hazard.showRooms = false;
        }
      });

      if(hazard.ActiveSubHazards.length){
        angular.forEach(hazard.ActiveSubHazards, function(child, key){
          factory.getShowRooms(child);
        });
      }
    }
  }

  factory.roomDoesNotContainHazard =  function( room ){
    if(room.ContainsHazard == false) return true;
    return false;
  }

  factory.roomContainsHazard =  function( room ){
    if(room.ContainsHazard == false) return true;
    return false;
  }


	return factory;
});

controllers = {};

//called on page load, gets initial user data to list users
controllers.hazardAssessmentController = function ($scope, $q, hazardInventoryFactory, $location, $filter, convenienceMethods, $window, $element ) {

  var comboBreaker = $q.defer();
  var getAllPis = function()
  {
      return hazardInventoryFactory
              .getAllPis()
                .then(function(pis)
                {
                  hazardInventoryFactory.getAllPis(pis);
                  //we have to set this equal to the promise rather than the getter, because the getter will return a promise, and that breaks the typeahead because of a ui-bootstrap bug
                  return pis;
                },
                function()
                {
                  $scope.error = 'There was a problem getting the list of Principal Investigators.  Please check your internet connection.'
                });
  },
  getPi = function(piKey_id)
  {
        $scope.piLoading = true;
	      var piDefer = $q.defer();
	      hazardInventoryFactory
	              .getPi(piKey_id)
	                .then(function(pi){
                      $scope.piLoading = false;
	                    $scope.PI = pi;
	                    piDefer.resolve( pi );
	                },
	                function(fail){
                      $scope.piLoading = false;
	                    piDefer.reject();
	                    $scope.error = 'There was a problem getting the selected Principal Investigator.  Please check your internet connection.'
	                });
	      return piDefer.promise;
  },
  setInspection = function(pi)
  {
      //fill the PI select field with the selected PI's name
      $scope.customSelected = pi.User.Name;

      //now that we have a PI, we can initialize the inspection
      var PIKeyID = pi.Key_id;

      //todo:  when we do user siloing, give the user a way to add another inspection
      //dummy value for inspector ids
      inspectorIds = [1];

      //if we are accessing an inspection that has already been started, we get it's get ID from the $location.search() property (AngularJS hashed get param)
      if($location.search().inspectionId){
        inspectionId = $location.search().inspectionId
      }else{
        inspectionId = '';
      }

      //set up our $q object so that we can either return a promise on success or break the promise chain on error
      var inspectionDefer = $q.defer();

      hazardInventoryFactory
            .initialiseInspection( PIKeyID, inspectorIds, inspectionId )
              .then(function(inspection)
              {
                  //set our get params so that this inspection can be quickly accessed on page reload
                  $location.search('inspectionId', inspection.Key_id);
                  $location.search("pi", inspection.PrincipalInvestigator.Key_id);

                  //set up our list of buildings
                  $scope.buildings = hazardInventoryFactory.parseBuildings( inspection.Rooms );

                  //set our inspection scope object
                  $scope.inspection = inspection;

                  //we return the inspection's rooms so that we can query for hazards
                  inspectionDefer.resolve(inspection.Rooms);
              },
              function(noRooms)
              {
                  if(noRooms){
                    //there was no error, but this PI doesn't have any rooms, so we can't inspect
                    $scope.noRoomsAssigned = true;
                  }else{
                    $scope.error = "There was a problem creating the Inspection.  Please check your internet connection and try selecting a Principal Investigator again.";
                  }
                  //call our $q object's reject method to break the promise chain
                  inspectionDefer.reject();
              });

      return inspectionDefer.promise;
  },
  resetInspectionRooms = function( roomIds,  inspectionId )
  {
      //set up our $q object so that we can either return a promise on success or break the promise chain on error
      var resetInspectionDefer = $q.defer();
      $scope.hazards = [];
      $scope.hazardsLoading = true;
      hazardInventoryFactory
              .resetInspectionRooms( roomIds,  inspectionId )
                .then(function( hazards )
                {
                  if(!hazards.InspectionRooms)hazards.InspectionRooms = [];
                  $scope.hazards = hazards.ActiveSubHazards;
                  $scope.hazardsLoading = false;
                  $scope.needNewHazards = false;
                  angular.forEach($scope.hazards, function(hazard, key){
                    if(hazard.IsPresent)$scope.getShowRooms(hazard);
                  });

                  resetInspectionDefer.resolve( hazards );
                },
                function(){
                    $scope.error = 'There was a problem getting the new list of hazards.  Please check your internet connection and try again.';
                    resetInspectionDefer.reject();
                });
      return resetInspectionDefer.promise;
  }
  getHazards = function(rooms)
  {     
            //rooms is a collection of the inspection's rooms, so we need to get their key_ids for the server to send us back a hazards collection
            var roomIds = [];
            var roomsLength = rooms.length;
            for(var i = 0; i < roomsLength; i++){
              if(roomIds.indexOf(rooms[i].Key_id) == -1)roomIds.push(rooms[i].Key_id);
            }
            $scope.hazardsLoading = true;

            var hazardDefer = $q.defer();

            hazardInventoryFactory
              .getHazards( roomIds )
                .then(function( hazards )
                {
                  //create our view model for hazards
                  $scope.hazards = hazards.ActiveSubHazards;
                  $scope.hazardsLoading = false;
                  $scope.needNewHazards = false;

                  $scope.hazards = hazardInventoryFactory.parseHazards( $scope.hazards );
                  hazardDefer.resolve( $scope.hazards );
                },
                function(){
                    $scope.hazardsLoading = false;
                    $scope.error = 'There was a problem getting the new list of hazards.  Please check your internet connection and try again.';
                    hazardDefer.reject();
                });
            console.log( hazardDefer );
            return hazardDefer.promise;
  },
  initiateInspection = function(piKey_id)
  {
    //start our inspeciton creation/load process
    //chained promises to get a PI, Inspection, and Hazards
    getPi( piKey_id )
      .then( setInspection )
      .then( getHazards  );
  };


  init();
  
  function init(){

    //are we loading an old inspection?
    if($location.search().hasOwnProperty('inspectionId') && $location.search().hasOwnProperty('pi')){
       $scope.getAll = true;
       initiateInspection( $location.search().pi );
    }else{
      $scope.noPiSet = true;
    }

    //always get a list of all PIs so that a user can change the PI in scope, separate from the promise chain that gets our individual PI, Inspeciton and list of Hazards
    $scope.PIs = getAllPis();

  }


  //callback function called when a PI is selected in the typeahead
  $scope.onSelectPi = function($item, $model, $label){
  	//unset scope.PI so that when we call initiateInspection, we will eager load a new PI from the server
  	$scope.PI = {};
  	$scope.inspection = {};
  	$scope.buildings = [];
  	$location.search('inspectionId','');
	  $location.search("pi",'');

    console.log($item);

    initiateInspection($item.Key_id);
  }

  $scope.removeBuilding = function(building){
   // console.log(building);
    $scope.selectedBuildings.splice($scope.buildings.indexOf(building),1);
  }

  function onGetRoomsByBuilding(data){
    //replace
    //$scope.roomsToSelect = data;
    $scope.roomsToSelect = angular.copy($scope.buildings);
  }

  function onFaileGetRoomsByBuilding(){
    alert('There was a problem trying to get the list of rooms based on the buildings you selected.');
  }

  $scope.selectRoom = function(room,building){
    if($scope.roomsToRequest.indexOf(room) === -1){
       $scope.roomsToRequest.push(room.Key_id);saveInspectionRoomRelation($roomId = NULL,$inspectionId = NULL,$add= NULL)

    }else{
       $scope.roomsToRequest.splice($scope.roomsToRequest.indexOf(room.Key_id), 1);
    }

    selectedRooms = 0;
    angular.forEach(building.Rooms, function(thisRoom, key){
      if(thisRoom.IsSelected){
        building.IsChecked = true;
        selectedRooms ++;
      }
    });
    if(selectedRooms == 0){
      building.IsChecked = false;
    }

  }

  $scope.showSubHazards = function(event, hazard, element){
    event.stopPropagation();
    $scope.selectedHazard = hazard;
    calculateClickPosition(event,hazard, element);

  	//do we have subHazards for this hazard?
  	if(hazard.ActiveSubHazards && hazard.ActiveSubHazards){
  		hazard.showSubHazardsModal = !hazard.showSubHazardsModal;
  	}else if(hazard.HasChildren){
  		//we don't have subhazards for this hazard, but there are some.
  		//get them from the server.
  		hazard.IsDirty = true;
  		hazardInventoryFactory.getSubHazards(hazard).then(
  			function(promise){
  				console.log(promise);
  				hazard.error = "";
  				hazard.IsDirty = false;
  				hazard.ActiveSubHazards = promise;
  				hazard.showSubHazardsModal = !hazard.showSubHazardsModal;
  			},
  			function(){
  				hazard.error = "There was a problem getting the subhazards for " + hazard.Name + "Please check your internet connection and try again.";
  				hazard.IsDirty = false;
  			}
  		)
  	}

  }

  $scope.showRooms = function(event, hazard, element){
    console.log(hazard);
    $scope.walkhazard(hazard);
    event.stopPropagation();
    $scope.selectedHazard = hazard;
    calculateClickPosition(event,hazard,element);
    hazard.showRoomsModal = !hazard.showRoomsModal;
  }
  //get the position of a mouseclick, set a properity on the clicked hazard to position an absolutely positioned div
  function calculateClickPosition(event, hazard, element){
    var x = event.clientX;
    var y = event.clientY+$window.scrollY;
    var w = $(event.target).parent().parent().find('label').width();

    hazard.calculatedOffset = {};
    hazard.calculatedOffset.x = x+10;
    hazard.calculatedOffset.y = y-5;

    hazard.calculatedOffset.w = w + 70;
  }
/*
  function modalHazards(){
    $dialog.dialog({}).open('hazards-modal.html');  
  }
*/
  //set a boolean flag to determine if rooms are shown beneath a hazard
  $scope.getShowRooms = function(hazard){
    if(hazard.IsPresent && hazard.ActiveSubHazards){
      hazardInventoryFactory.getShowRooms( hazard );
    }
  }


  //reset the inspection with a new set of rooms, based on user selection
  $scope.resetInspection = function(){
    //For which rooms do we need hazards?
    var roomIds = [];
    var bldLen = $scope.buildings.length;
    for( var i=0; i < bldLen; i++ ){
      var building = $scope.buildings[i];
      var roomLen = building.Rooms.length;
      for( var j=0; j < roomLen; j++){
        var room = building.Rooms[j];
        if( roomIds.indexOf( room.Key_id )<0 && room.IsSelected) roomIds.push( room.Key_id );
      }
    }

    if(roomIds.length){
      $scope.noRoomsSelected = false;
      resetInspectionRooms( roomIds,  $scope.inspection.Key_id );
    }else{
      $scope.noRoomsSelected = true;
    }
  }

  //get boolean for hazard.ContainsRoom  Used for our hazard.every functions, to determine if any rooms in a hazard's collection contain the hazard
  function roomDoesNotContainHazard(element, index, array){
    if(element.ContainsHazard == false) return true;
    return false;
  }

  //get boolean for hazard.ContainsRoom.  Used for our hazard.every functions, to determine if any rooms in a hazard's collection contain the hazard
  function roomContainsHazard(element, index, array){
    if(element.ContainsHazard) return true;
    return false;
  }

  function childNotPresent(element, index, array){
    if(!element.IsPresent){
      return true;
    }
    return false;
  }

  //watch hazards and set appropriate properties
  $scope.$watch('hazards', function(value, oldValue) {
    angular.forEach($scope.hazards, function(hazard, key){
     // console.log(hazard);
      if(hazard.IsDirty || hazard.showRoomsModal || hazard.showSubHazardsModal){
        //if a hazard has been selected, make sure we only show ITS rooms or hazards, closing all the other modals
        if($scope.selectedHazards){
           if(hazard.Key_Id !== $scope.selectedHazards.Key_Id){
            hazard.showRoomsModal = false;
            hazard.showSubHazardsModal = false;
          }
        }

        if(hazard.ActiveSubHazards.length){
          var releventRooms = hazard.InspectionRooms;
          angular.forEach(hazard.ActiveSubHazards, function(child, key){
              angular.forEach(child.InspectionRooms, function(room, key){
              room.IsAllowed = true;
            });
            if(child.IsDirty){
               $scope.walkhazard(child);
            }
          });
        }
      }
      hazard.IsDirty = false;
    });
  },true);

 
  //recursively step through a hazard and its children
  $scope.walkhazard = function(hazard){

    if(hazard.ActiveSubHazards  && hazard.IsPresent){

      var children = hazard.ActiveSubHazards;

      angular.forEach(children, function(child, key){
        
      //if a hazard has been selected, make sure we only show ITS rooms or hazards, closing all the other modals
      if($scope.selectedHazard){
         if(child.Key_Id !== $scope.selectedHazard.Key_Id){
           child.showRoomsModal = false;
           child.showSubHazardsModal = false;
         }
       } 

        //don't allow hazards whose parents are not present to be present
        if(!hazard.IsPresent){
          child.IsPresent = false;
        }

        //if all rooms are deselected for a hazard, that hazard is not present
        if(child.InspectionRooms && child.InspectionRooms.every(roomDoesNotContainHazard) ){
            child.IsPresent = false;
        }

        //check each of the rooms for each of the child, only rooms that contain the parent hazard are allowed to be checked for the child
        angular.forEach(child.InspectionRooms, function(room, key){

          //get the array index of the room so that we can use it to check the hazard's parent's rooms quickly
          var index = child.InspectionRooms.indexOf(room);
          if(hazard.InspectionRooms[index].ContainsHazard){
            console.log(room);
            room.IsAllowed = true;
          }else{
           // room.IsAllowed = false;
            room.ContainsHazard = false;
          }

        });

        //rooms are finished processing, we can set hazard to clean
        /*if(hazard.InspectionRooms.every(isNotDirty)){
            hazard.IsDirty = false;
        }*/

        //recurse down the tree
        $scope.walkhazard(child);
    });
  } 
    //If at least one room contains the hazard, but not all rooms, set property of hazard so that rooms can be displayed
    $scope.getShowRooms(hazard);
}



  $scope.handleRoom = function(room, hazard, parent){
    console.log(room);
    //room.ContainsHazard = !room.ContainsHazard;

    //did we uncheck the last room?
    if(hazard.InspectionRooms.every(roomDoesNotContainsHazard)){
    	hazard.IsPresent = false;
    	$scope.handleHazardChecked(hazard);
    }else{
    	room.IsDirty = true;
    	var add = room.ContainsHazard;
    	hazardInventoryFactory.setSingleHazardRoomRelations(hazard, room, add).then(
    		function(promise){
    			room.IsDirty = false;
    		},
    		function(){
    			room.ContainsHazard = !room.ContainsHazard;
    			$scope.error = "Room "+room.Name+" could not be removed from "+hazard.Name+".  Please check your internet conneciton and try again.";
    		}
    	);
    }
    
  }

  function roomDoesNotContainsHazard(room){
  	if(!room.ContainsHazard)return true;
  	return false;
  }

  $scope.handleHazardChecked = function(hazard, parent){
  	console.log(hazard);
  	hazard.IsDirty = true;
    hazardInventoryFactory.setHazarRoomRelations(hazard).then(
    	function(promise){
    		hazard.IsDirty = false;
    		hazard.ActiveSubHazards = angular.copy(promise.ActiveSubHazards);
    		hazard.InspectionRooms = angular.copy(promise.InspectionRooms);
    	},
    	function(promise){
    		console.log(promise);
    		hazard.IsPresent = !hazard.IsPresent;
    		$scope.error = 'There was a problem updating '+hazard.Name+' in the system.  Please check your internet connection and try again.'
    	}
    )

  }

  $scope.checkBuilding = function(building){
    angular.forEach(building.Rooms, function(room, key){
      if(building.IsChecked){
          room.IsSelected = true;
      }else{
          room.IsSelected = false;
      }
    });
  }

  $scope.selectRoom = function(room,building){
    building.IsChecked = false;
    angular.forEach(building.Rooms, function(room, key){
      if(room.IsSelected){
        building.IsChecked = true;
      }
    });
  }

};

controllers.footerController = function($scope, $timeout, $filter,convenienceMethods){
  
  init();

  function init(){
    $scope.selectedFooter = '';
  }

  $scope.close = function(){
    $scope.selectedFooter = '';
  }

  $scope.getArchivedReports = function(){
    $scope.selectedFooter = 'reports';
    if(!$scope.previousInspections){
      $scope.waitingForInspections = false;
      var piId = $scope.PI.Key_id;
      var url = '../../ajaxaction.php?action=getInspectionsByPIId&piId='+piId+'&callback=JSON_CALLBACK';
      convenienceMethods.getData( url, onGetInspections, onFailGetInspections);
    }
  } 

  function onGetInspections(data){
    $scope.previousInspections = data;
    $scope.waitingForInspections = false;
  }
  function onFailGetInspections(){
    $scope.waitingForInspections = false;
    alert("They system couldn't find archived reports for "+$scope.PI.User.Name);
  }

  $scope.getLaboratoryContacts = function(){

    $scope.selectedFooter = 'contacts';
    if(!$scope.PI){
      $scope.doneLoading = false;
      var url = '../../ajaxaction.php?action=getPI&id=12&callback=JSON_CALLBACK';
      convenienceMethods.getData( url, onGetLabContacts, onFailGetLabContacts);
    }
  }

  onGetLabContacts = function(data){
    $scope.contacts = data.LabPersonnel;
    $scope.doneLoading = data.doneLoading;
  }

  onFailGetLabContacts = function(){
    alert('Something went wrong when retrieving lab contacts.');
  }

  $scope.openNotes = function(){
    console.log($scope.inspection);
     $scope.newNote = $scope.inspection.Note;
     $scope.selectedFooter = 'comments'
  }

  $scope.saveNoteForInspection = function(){

    $scope.newNoteIsDirty = true;
    console.log($scope.newNote);

    var inspectionDTO = {
      Class: "EntityText",
      Entity_id:  $scope.inspection.Key_id,
      Text:  $scope.newNote
    }

    var url = "../../ajaxaction.php?action=saveNoteForInspection";
    convenienceMethods.updateObject(inspectionDTO, $scope.inspection, onSaveNote, onFailSaveNote, url);
  }

  function onSaveNote(returned){
    $scope.newNoteIsDirty = false;
    $scope.inspection.Note = $scope.newNote;
  }

  function onFailSaveNote(data){
    $scope.newNoteIsDirty = false;
    alert('There was a problem saving the note.');
  }

  $scope.cancelSaveNote = function(){
    $scope.newNoteIsDirty = false;
    $scope.newNote = $scope.inspection.Note;
    $scope.selectedFooter = false;
  }

  $scope.$watch('previousInspections', function(previousInspections, oldValue) {
    angular.forEach($scope.previousInspections, function(inspection, key){
      if(inspection.Date_created){
        var date =  convenienceMethods.getDate(inspection.Date_created);
        inspection.startDate = date.formattedString;
        inspection.year = date.year;
      }
        
      if(inspection.Date_closed){
        inspection.endDate = convenienceMethods.getDate(inspection.Date_closed);
      }
    });
  });

}
//set controller
hazardInventory.controller( controllers );