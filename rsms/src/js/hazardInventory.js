var hazardInventory = angular.module('hazardAssesment', ['ui.bootstrap','convenienceMethodWithRoleBasedModule','once']);

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
}]);

hazardInventory.filter('openInspections', function () {
  return function (inspections) {
      if(!inspections)return;
      var openInspections = [];
      var i = inspections.length;
      while(i--){
          if(!inspections[i].Status || inspections[i].Status.toLowerCase().indexOf('close') < 0)openInspections.push(inspections[i]);
      }
      return openInspections;
  };
})

hazardInventory.factory('hazardInventoryFactory', function(convenienceMethods,$q){
  var factory = {};
  factory.PI = {};
  var allPis = [];
  factory.previousInspections = [];
  factory.openInspections = [];
  factory.buildings = [];

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
    //if we don't have a pi, get one from the server
    var deferred = $q.defer();

    //lazy load
    if(this.PI && this.PI.Key_id == id){
      deferred.resolve( this.PI );
    }else{
      //eager load
      var url = '../../ajaxaction.php?action=getPIById&id='+id+'&getRooms=true&callback=JSON_CALLBACK';
        convenienceMethods.getDataAsDeferredPromise(url).then(
        function(promise){
          factory.PI = promise;
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

  factory.initialiseInspection = function(PIKeyID, inspectorIds, inspectionId, rad){
    //if we don't have a pi, get one from the server
    var deferred = $q.defer();
    if(!inspectorIds)inspectorIds=[10];
    var url = '../../ajaxaction.php?callback=JSON_CALLBACK&action=initiateInspection&piId='+PIKeyID+'&'+$.param({inspectorIds:inspectorIds});
    if(rad)url = url+"&rad=true";

    if(inspectionId) url+='&inspectionId='+inspectionId;
    var temp = this;
      convenienceMethods.getDataAsDeferredPromise(url).then(
      function( inspection ){
        //if the PI doesn't have any rooms, we should reject the promise and let the controller know why
        if(!inspection.Rooms.length){
          deferred.reject(true);
        }else{
          factory.setInspection(inspection);
          //we have a good inspection with a collection of rooms
          var j = factory.PI.Rooms.length;
          inspection.piRooms = [];
          while(j--){
            inspection.piRooms[j] = {Class:"Room", Key_id:factory.PI.Rooms[j].Key_id,Name:factory.PI.Rooms[j].Name};
          }
          inspection.Is_new = true;
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
    var url = "../../ajaxaction.php?action=saveHazardRelation&recurse=true&roomId="+room.Key_id+"&hazardId="+hazard.Key_id+"&add="+add+'&callback=JSON_CALLBACK';
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
      buildings = [];
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
      factory.buildings = buildings;
      return factory.buildings;
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
    hazard.showRooms = false;

    if(!hazard.InspectionRooms.every( factory.roomDoesNotContainHazard ) && !hazard.InspectionRooms.every( factory.roomContainsHazard )){
      return true;
    }else{
      return false;
    }

  }

  factory.roomDoesNotContainHazard = function( room ){

    if(room.ContainsHazard != true){
      //console.log( room );
      return true;
    }
    //console.log( room );
    return false;
  }

  factory.roomContainsHazard = function( room ){
    if(room.ContainsHazard == true) return true;
    return false;
  }

  factory.getPreviousInspections = function(pi)
  {
      var deferred = $q.defer();

      if(factory.previousInspections.length){
        deferred.resolve( factory.previousInspections );
        return deferred.promise;
      }

      var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getInspectionsByPIId&piId="+pi.Key_id;
      convenienceMethods.getDataAsDeferredPromise(url).then(
        function(promise){
          factory.previousInspections = promise;
          deferred.resolve(promise);
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise
  }

  factory.getOpenInspections = function(pi)
  {
      var deferred = $q.defer();

      if(factory.openInspections.length){
        deferred.resolve( factory.openInspections );
        return deferred.promise;
      }

      var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getOpenInspectionsByPIId&id="+pi.Key_id;
      convenienceMethods.getDataAsDeferredPromise(url).then(
        function(promise){
          factory.openInspections = promise;
          var i = promise.length;
          if(i==0)deferred.resolve();
          while(i--){
            var inspection = factory.openInspections[i];
            inspection.piRooms = [];
            var j = factory.PI.Rooms.length;
            while(j--){
              inspection.piRooms[j] = {Class:"Room", Key_id:factory.PI.Rooms[j].Key_id,Name:factory.PI.Rooms[j].Name};
            }
            deferred.resolve(promise);
          }
        },
        function(promise){
          deferred.reject();
        }
      );
      return deferred.promise
  }

  factory.saveInspectionRoomRelationship = function(inspection, room)
  {
    if(typeof room.checked == 'undefined')room.checked = false;
    room.userChecked = room.checked;
    var deferred = $q.defer();
    var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=saveInspectionRoomRelation&roomId="+room.Key_id+"&inspectionId="+inspection.Key_id+"&add="+room.checked;
    room.IsDirty = true;

    convenienceMethods.getDataAsDeferredPromise(url).then(
      function(promise){
        room.IsDirty = false;
        deferred.resolve(room);
      },
      function(promise){
        room.IsDirty = false;
        room.checked = !room.checked;
        deferred.reject();
      }
    );
    return deferred.promise
  }

  factory.evalInspectionRoomChecked = function(inspection, room)
  {
      if(room.userChecked)return room.userChecked;
      var i = inspection.Rooms.length;
      while(i--){
        if(inspection.Rooms[i].Key_id == room.Key_id){
          return true;
        }
      }
      return false;
  }

  factory.savePi = function(pi)
  {
    var url = "../../ajaxaction.php?action=savePI";
    var deferred = $q.defer();
      convenienceMethods.saveDataAndDefer(url, pi)
        .then(
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
controllers.hazardAssessmentController = function ($scope, $rootScope, $q, hazardInventoryFactory, $location, $filter, convenienceMethods, $window, $element, $modal) {

  var comboBreaker = $q.defer();
  var getAllPis = function()
  {
      return hazardInventoryFactory
              .getAllPis()
                .then(function(pis)
                {
                  hazardInventoryFactory.setAllPis(pis);
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
        $scope.error='';
        $scope.piLoading = true;
        var piDefer = $q.defer();
        hazardInventoryFactory
                .getPi(piKey_id)
                  .then(function(pi){
                      $scope.piLoading = false;
                      $rootScope.PI = pi;
                      if(!pi.Is_active){
                        $scope.inactive = true;
                      }else{
                        $scope.inactive = false;
                      }
                      $scope.selectPI = false;
                      $scope.buildings = hazardInventoryFactory.parseBuildings( pi.Rooms );
                      $location.search("pi", pi.Key_id);
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
      inspectorIds = [10];

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
                  //angular.forEach($scope.hazards, function(hazard, key){
                    //if(hazard.IsPresent)$scope.getShowRooms(hazard);
                  //});

                  resetInspectionDefer.resolve( hazards );
                },
                function(){
                    $scope.error = 'There was a problem getting the new list of hazards.  Please check your internet connection and try again.';
                    resetInspectionDefer.reject();
                });
      return resetInspectionDefer.promise;
  },
  getHazards = function( pi )
  {
            $scope.hazards = null;
            if(pi.Is_active != true)return;
            //rooms is a collection of the inspection's rooms, so we need to get their key_ids for the server to send us back a hazards collection
            var rooms = pi.Rooms;
            var roomIds = [];
            var roomsLength = rooms.length;
            for(var i = 0; i < roomsLength; i++){
              if(roomIds.indexOf(rooms[i].Key_id) == -1)roomIds.push(rooms[i].Key_id);
            }

            if(!rooms || !rooms.length){
              $scope.noRoomsAssigned = true;
              return
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
            return hazardDefer.promise;
  },
  initiateInspection = function(piKey_id)
  {
    //start our inspeciton creation/load process
    //chained promises to get a PI, Inspection, and Hazards
    getPi( piKey_id )
      .then( setInspection )
      .then( getHazards  );
  }


  init();

  function init(){

    //are we loading an old inspection?
    if( $location.search().hasOwnProperty('pi') && $location.search().pi != null){
       $scope.getAll = true;
       getPi( $location.search().pi )
        .then(getHazards);
    }else{
      $scope.noPiSet = true;
    }

    //always get a list of all PIs so that a user can change the PI in scope, separate from the promise chain that gets our individual PI, Inspeciton and list of Hazards
    getAllPis().then(
        function(pis){
          $scope.PIs = pis;
        }
    );

  }

  //callback function called when a PI is selected in the typeahead
  $scope.onSelectPi = function($item, $model, $label){
    $scope.inspection = {};
    $scope.buildings = [];
    $location.search("pi",'');
    getPi($item.Key_id)
      .then(getHazards)
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
       $scope.roomsToRequest.push(room.Key_id);
       saveInspectionRoomRelation($roomId = NULL,$inspectionId = NULL,$add= NULL)

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
  $scope.getShowRooms = function( hazard ){

    if(hazard.IsPresent){
      return hazardInventoryFactory.getShowRooms( hazard );
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
   // $scope.getShowRooms(hazard);
}



  $scope.handleRoom = function(room, hazard, parent){
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
          removeSubHazardsFromRoom(room, hazard);
        },
        function(){
          room.ContainsHazard = !room.ContainsHazard;
          $scope.error = "Room "+room.Name+" could not be removed from "+hazard.Name+".  Please check your internet conneciton and try again.";
        }
      );
    }

  }

  function removeSubHazardsFromRoom(room, hazard){
      //set the room's containsHazard property to false for each of the subhazards
      //get the index of the room in the parent hazard's rooms collection
      var idx = convenienceMethods.arrayContainsObject(hazard.InspectionRooms, room, null, true);
      if(!room.ContainsHazard && hazard.ActiveSubHazards){
        var subLen = hazard.ActiveSubHazards.length;
        for(var i = 0; i < subLen; i++ ){
            hazard.ActiveSubHazards[i].InspectionRooms[idx].ContainsHazard = false;
            if(hazard.ActiveSubHazards[i].ActiveSubHazards)removeSubHazardsFromRoom(room, hazard.ActiveSubHazards[i]);
        }
      }
  }

  function roomDoesNotContainsHazard(room){
    if(!room.ContainsHazard)return true;
    return false;
  }

  $scope.handleHazardChecked = function(hazard, parent){
    hazard.IsDirty = true;
    hazardInventoryFactory.setHazarRoomRelations(hazard).then(
      function(promise){
        hazard.IsDirty = false;
        hazard.ActiveSubHazards = angular.copy(promise.ActiveSubHazards);
        hazard.InspectionRooms = angular.copy(promise.InspectionRooms);
      },
      function(promise){
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

  $scope.openMultiplePIsModal = function(instanceWithPIs) {
      // Figure out how to pass room to the modal instance.
    var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/multiple-PIs-modal.html',
        controller: controllers.modalCtrl,
        resolve: {instanceWithPIs:function() {return instanceWithPIs;} }
    });
}

};


controllers.footerController = function($scope, $location, $filter, convenienceMethods,hazardInventoryFactory, $rootScope, $modal){

  init();

  function init(){
    $scope.location = $location.search();
    $scope.selectedFooter = '';

  }

  $scope.close = function(){
    $scope.selectedFooter = '';
  }

  $scope.getArchivedReports = function(){
      var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/archived-reports.html',
        controller: controllers.modalCtrl
      });


        modalInstance.result.then(function () {
         locationHubFactory.getRooms()
        .then(
          function(rooms){
            $scope.rooms = rooms;
            $scope.loading = false;
          }
        )
      });

  }

  $scope.getContacts = function(){
      var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/lab-personnel.html',
        controller: contactsController
      });


      modalInstance.result.then(function () {});

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

  $scope.startInspection = function()
  {
    var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/open-inspections.html',
        controller: controllers.findInspectionCtrl
      });


        modalInstance.result.then(function () {
         locationHubFactory.getRooms()
        .then(
          function(rooms){
            $scope.rooms = rooms;
            $scope.loading = false;
          }
        )
      });
  }

  onGetLabContacts = function(data){
    $scope.contacts = data.LabPersonnel;
    $scope.doneLoading = data.doneLoading;
  }

  onFailGetLabContacts = function(){
    alert('Something went wrong when retrieving lab contacts.');
  }

  $scope.editNote = function(){
    $scope.noteEdited = true;
  }

  $scope.openNotes = function(){
     var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/inspection-notes-modal.html',
        controller: controllers.commentsController
      });

      modalInstance.result.then(function () {

      });
  }

  $scope.saveNoteForInspection = function(note){
    $scope.newNoteIsDirty = true;
    var inspectionDTO = {
      Class: "EntityText",
      Entity_id:  $scope.inspection.Key_id,
      Text:  note
    }
    var url = "../../ajaxaction.php?action=saveNoteForInspection";
    convenienceMethods.updateObject(inspectionDTO, $scope.inspection, onSaveNote, onFailSaveNote, url, null, note);
  }

  function onSaveNote(returned, note, test){
    $scope.noteEdited = false;
    $scope.newNoteIsDirty = false;
    $scope.inspection.Note = test;
    $scope.newNote = angular.copy($scope.inspection.Note);
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

}

controllers.modalCtrl = function($scope, hazardInventoryFactory, $modalInstance, convenienceMethods, instanceWithPIs){

    if (instanceWithPIs && instanceWithPIs.HasMultiplePIs) {
        // We have room with multiple PIs, so get PIs for room
        $scope.instanceWithPIs = instanceWithPIs;
        var url = '../../ajaxaction.php?action=getPIsByClassInstance';
        var instanceCopy = jQuery.extend({}, instanceWithPIs);
        convenienceMethods.saveDataAndDefer(url, instanceCopy).then(
            function(pis) {
                $scope.error = null;
                instanceWithPIs.PrincipalInvestigators = pis;
            }, function() {
                $scope.error = "PIs failed to load";
            }
        );
    }
    $scope.gettingInspections = true;
    var pi = hazardInventoryFactory.PI;
    $scope.pi = pi;
    hazardInventoryFactory.getPreviousInspections(pi)
    .then(
        function(inspections){
            $scope.previousInspections = inspections;
            $scope.gettingInspections = false;
        },
        function(){
            $scope.gettingInspections = false;
            $scope.error = 'The system could not retrieve the list of inspections.  Please check your internet connection and try again.  '
        }
    )

  $scope.$watch('previousInspections', function(previousInspections, oldValue) {
    angular.forEach($scope.previousInspections, function(inspection, key){
      if(inspection.Date_created){
        var date =  convenienceMethods.getDate(inspection.Date_created);
        inspection.startDate = date.formattedString;
        inspection.year = date.year;
      }

      if(inspection.Date_closed){
        inspection.endDate = convenienceMethods.getDate(inspection.Date_closed).formattedString;
      }
    });
  });

  $scope.close = function () {
    $modalInstance.dismiss();
  };

}

controllers.findInspectionCtrl = function($scope, hazardInventoryFactory, $modalInstance, convenienceMethods, $q){
  $scope.hif=hazardInventoryFactory;
  var pi = hazardInventoryFactory.PI;
  $scope.pi = pi;
  $scope.buildings = hazardInventoryFactory.buildings;
  $scope.gettingInspections = true;

    hazardInventoryFactory.getOpenInspections(pi)
    .then(
      function(inspections){
        $scope.openInspections = inspections;
        $scope.gettingInspections = false;
      },
      function(){
        $scope.gettingInspections = false;
        $scope.error = 'The system could not retrieve the list of inspections.  Please check your internet connection and try again.  '
      }
    )

  $scope.$watch('openInspections', function(previousInspections, oldValue) {
    angular.forEach($scope.previousInspections, function(inspection, key){
      if(inspection.Date_created){
        var date =  convenienceMethods.getDate(inspection.Date_created);
        inspection.startDate = date.formattedString;
        inspection.year = date.year;
      }
      if(inspection.Date_started){
        inspection.startDate = convenienceMethods.getDate(inspection.Date_closed).formattedString;
      }
    });
  });

  $scope.setInspection = function(rad)
  {
      if(!rad)rad = false;
      $scope.creatingInspection = true;
      //now that we have a PI, we can initialize the inspection
      var PIKeyID = hazardInventoryFactory.PI.Key_id;
      //todo:  when we do user siloing, give the user a way to add another inspection
      //dummy value for inspector ids
      inspectorIds = [10];

      //set up our $q object so that we can either return a promise on success or break the promise chain on error
      var inspectionDefer = $q.defer();

      hazardInventoryFactory
            .initialiseInspection( PIKeyID, inspectorIds, null,rad )
              .then(function(inspection)
              {
                  if(!rad){
                    $scope.creatingInspection = false;
                    if(!$scope.openInspections)$scope.openInspections=[];
                    $scope.openInspections.push(inspection);
                  }else{
                    //navigate to checklist for rad inspection.
                    window.location = "InspectionChecklist.php#?inspection="+inspection.Key_id;
                  }
              },
              function(noRooms)
              {

                  $scope.creatingInspection = false;
                  //$scope.creatingInspection = false;
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
  }

  $scope.close = function () {
    $modalInstance.dismiss();
  };

}

controllers.commentsController = function($scope, hazardInventoryFactory, $modalInstance, convenienceMethods, $q){
  $scope.hif=hazardInventoryFactory;
  var pi = hazardInventoryFactory.PI;
  $scope.pi = pi;
  $scope.piCopy = {
    Key_id: $scope.pi.Key_id,
    Is_active: $scope.pi.Is_active,
    User_id: $scope.pi.User_id,
    Inspection_notes: $scope.pi.Inspection_notes,
    Class:"PrincipalInvestigator"
  };

  $scope.close = function () {
    $modalInstance.dismiss();
  };

  $scope.edit = function(state){
    $scope.pi.editNote = state;
  }

  $scope.saveNote = function(){
    $scope.savingNote = true;
    $scope.error = null;

    hazardInventoryFactory.savePi($scope.piCopy)
      .then(
        function(returnedPi){
          angular.extend(hazardInventoryFactory.PI, returnedPi);
          $scope.savingNote = false;
          $scope.close();
          $scope.pi.editNote = false;
          $scope.pi.Inspection_notes = returnedPi.Inspection_notes;
        },
        function(){
          $scope.savingNote = false;
          $scope.error = "The Inspection Comments could not be saved.  Please check your internet connection and try again."
        }
      )
  }

}

contactsController = function($scope, hazardInventoryFactory, $modalInstance){
  $scope.hif=hazardInventoryFactory;
  var pi = hazardInventoryFactory.PI;
  $scope.pi = pi;

  $scope.close = function () {
    $modalInstance.dismiss();
  };

}

//set controller
hazardInventory.controller( controllers );
