var hazardInventory = angular.module('hazardAssesment', ['ui.bootstrap','convenienceMethodModule','once']);

hazardInventory.directive('hazardLi', ['$window', function($window) {
    return {
        restrict: 'C',
        link: function(scope, elem, attrs) {

            scope.onResize = function() {
                w = elem.width();
                checkbox = $(elem).find($('.targetHaz span'));
                label = $(elem).find($('label'));
                console.log(w);
                //label.width(w);
               // checkbox.width(w-27);

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
		//lazy load
		if(this.PI)return this.PI;

		//if we don't have a pi, get one from the server
		var deferred = $q.defer();
		if(id){
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
			function(promise){
				deferred.resolve(promise);
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
		var url = "../../ajaxaction.php?action=saveHazardRelation&roomId="+room.Key_id+"&hazardId="+hazard.Key_id+"&add="+add;
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

	return factory;
});

controllers = {};

//called on page load, gets initial user data to list users
controllers.hazardAssessmentController = function ($scope, $q, hazardInventoryFactory, $location, $filter, convenienceMethods, $window, $element ) {
 
  function camelCase(input) {
    return input.toLowerCase().replace(/ (.)/g, function(match, group1) {
      return group1.toUpperCase();
    });
  }

  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  function init(){

    if($location.search().hasOwnProperty('inspectionId')){
       $scope.getAll = true;
       //getPI if there is a "pi" index in the GET
       initiateInspection($location.search().pi);
       //$scope.needNewHazards = true;
    }else{
      $scope.noPiSet = true;
    }

    //always get a list of all PIs so that a user can change the PI in scope
    getAllPis();

  }

  function getAllPis(){
  	hazardInventoryFactory.getAllPis().then(
  		function(pis){
  			hazardInventoryFactory.getAllPis(pis);
  			//we have to set this equal to the promise rather than the getter, because the getter will return a promise, and that breaks the typeahead because of a ui-bootstrap bug
  			$scope.PIs = pis;
  		},
  		function(fail){
  			$scope.error = 'There was a problem getting the list of Principal Investigators.  Please check your internet connection.'
  		}
  	);
  }

  //once we have a pi, we start a chain of promises to create/load an inspection on/from the server and pass it to the view
  function initiateInspection(PIKeyID){
  	 //get rid of the previous

	 //get our PI
	 getPi(PIKeyID)
  	.then(
  		//now that we have a PI, we know their rooms and can set an inspection and get hazards
  		function(pi){
  			setInspection(pi);
  		}
  	)
  }

  function getPi(id){
	piDefer = $q.defer();
  	console.log(id);

	//get our PI from the sever   
    hazardInventoryFactory.getPi(id).then(
  		function(pi){
 			inspectionDefer = $q.defer();
  			$scope.PI = pi;
  			piDefer.resolve(pi);
  		},
  		function(fail){
  			$scope.error = 'There was a problem getting the selected of Principal Investigator.  Please check your internet connection.'
  			piDefer.reject();
  		}
  	);
  	return piDefer.promise;
  }

  function setInspection(pi){
  	console.log(pi);
	$scope.customSelected = pi.User.Name;
	//now that we have a PI, we can initialize the inspection
	var PIKeyID = pi.Key_id;

	//todo:  when we do user siloing, give the user a way to add another inspection
	//dummy value for inspector ids
	inspectorIds = [1];

	//if this is a new inspection, call without passing a key id
	if($location.search().inspectionId){
		inspectionId = $location.search().inspectionId
	}else{
		inspectionId = '';
	}

	//get our factory to tell the server to initiate an inspection
	hazardInventoryFactory.initialiseInspection(PIKeyID, inspectorIds, inspectionId).then(
		function(inspection){
			console.log(inspection);
			$scope.inspection = inspection;
			$scope.PI = inspection.PrincipalInvestigator;
			$scope.selectBuildings();
			$location.search('inspectionId', $scope.inspection.Key_id);
			$location.search("pi", $scope.PI.Key_id);

			//resolve the promise
			inspectionDefer.resolve(inspection);
			return inspectionDefer.promise;
		},
		function(){
			inspectionDefer.reject();
			return inspectionDefer.promise;
		}
	)
  }


  //callback function called when a PI is selected in the typeahead
  $scope.onSelectPi = function($item, $model, $label){
  	//unset scope.PI so that when we call initiateInspection, we will eager load a new PI from the server
  	$scope.PI = {};
  	$scope.inspection = {};
  	$scope.buildings = [];
  	$location.search('inspectionId','');
	$location.search("pi",'');

    setInspection($item);
  }

  $scope.selectedBuildings = [];
  $scope.selectBuildings = function($item, $model, $label){
   
    //replace with a request to get rooms by building
    $scope.buildings = [];
    var bldgCount = 0;
    console.log($scope.PI.Rooms);
    angular.forEach($scope.PI.Rooms, function(room, key){    
      room.IsSelected = true;
      if(!convenienceMethods.arrayContainsObject($scope.buildings, room.Building)){
        $scope.buildings.push(room.Building);
        bldgCount++;
      }
      var iterator = bldgCount-1;
      var building = $scope.buildings[iterator];
      building.IsChecked = true;
      if(!building.Rooms) building.Rooms = [];
      building.Rooms.push(room);
    });
    $scope.getHazards();
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


  //get our hazards
  $scope.getHazards = function( rooms ){
 
  rooms = [];
  if(rooms.length){
	  	while(rooms.length > 0) {
	    rooms.pop();
	}
  }
  console.log(rooms);
  angular.forEach($scope.buildings, function(building, key){
    angular.forEach(building.Rooms, function(room, key){
      rooms.push(room.Key_id);
    });
  });
    console.log(rooms);
    $scope.selectRooms = false;
    
    var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';
    if(rooms.length){
      $scope.hazardsLoading = true;
      $scope.noRoomsAssigned = false;;
      convenienceMethods.getData( url, onGetHazards, onFailGetHazards );
    }else{
      $scope.noRoomsAssigned = true;;
    }
  }

  $scope.select

  //grab set user list data into the $scope object
  function onGetHazards (data) {
    console.log(data);
    if(!data.InspectionRooms)data.InspectionRooms = [];
    $scope.hazards = data.ActiveSubHazards;
  //  console.log(data);
    /*
    angular.forEach($scope.hazards, function(hazard, key){
     // console.log(hazard);
      hazard.cssId = camelCase(hazard.Name);
    });
    */
    $scope.hazardsLoading = false;
    $scope.needNewHazards = false;
    angular.forEach($scope.hazards, function(hazard, key){
      if(hazard.IsPresent)getShowRooms(hazard);
    });
  }


  function onFailGetHazards(){
    alert("There was a problem when the system tried to get the hazards.");
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
     hazard.showRooms = false;
      angular.forEach(hazard.InspectionRooms, function(room, key){
        if(!hazard.InspectionRooms.every(roomDoesNotContainHazard) && !hazard.InspectionRooms.every(roomContainsHazard)){
          console.log(hazard.Name);
          hazard.showRooms = true;
        }else{
          //console.log(hazard.Name);
          hazard.showRooms = false;
        }
      });
      if(hazard.ActiveSubHazards.length){
        angular.forEach(hazard.ActiveSubHazards, function(child, key){
          $scope.getShowRooms(child);
        });
      }
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
        if(hazard.InspectionRooms.every(isNotDirty)){
            hazard.IsDirty = false;
        }

        //recurse down the tree
        $scope.walkhazard(child);
    });
  } 
    //If at least one room contains the hazard, but not all rooms, set property of hazard so that rooms can be displayed
    $scope.getShowRooms(hazard);
}
    
  /*
   * HAZARD SAVE METHODS
   * used for creating and updating users
   * 
   */

  function isNotDirty(element, index, array){
    if(!element.IsDirty){
      return true;
    }
    return false;
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