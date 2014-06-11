var hazardAssesment = angular.module('hazardAssesment', ['ui.bootstrap','convenienceMethodModule','once']);

controllers = {};

//called on page load, gets initial user data to list users
controllers.hazardAssessmentController = function ($scope, $timeout, $location, $filter, convenienceMethods,$window,$element) {
 
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
       getPi($location.search().pi);
       //$scope.needNewHazards = true;
    }else{
      $scope.noPiSet = true;
    }

    //always get a list of all PIs so that a user can change the PI in scope
    var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetAllPIs, onFailGetAllPIs );   

    //always get a list of all Buildings so that a user can change the Building in scope
    var url = '../hubs/buildingMock.php?&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetBuildings, onFailGetBuildings );   

  }

  function setInspection(PIKeyID,inspectorIds,inspectionId){
    console.log('setting inpsection' + inspectionId);
    if(!inspectionId) inspectionId = '';
    console.log(inspectorIds);
    $scope.PI = false;
    var url = '../../ajaxaction.php?action=initiateInspection&piId='+PIKeyID+'&'+$.param({inspectorIds:inspectorIds})+'&inspectionId='+inspectionId;
    convenienceMethods.updateObject( PIKeyID, inspectorIds, onSetInspection, onFailSetInspection, url );
    $scope.noPiSet = false;
  }

  function onSetInspection(inspection){
    console.log(inspection);
    $scope.inspection = inspection;

    $scope.PI = inspection.PrincipalInvestigator;
    $scope.selectBuildings();

    $location.search('inspectionId', $scope.inspection.Key_id);
    $location.search("pi", $scope.PI.Key_id);
  }

  function onFailSetInspection(){
  }

  function getPi(PIKeyID){
    console.log(PIKeyID);
    $scope.PI = false;
    var url = '../../ajaxaction.php?action=getPIById&id='+PIKeyID+'&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetPI, onFailGetPI );
  }

  function onGetPI(data){
    $scope.noPiSet = false;
    $scope.PI = data;
    $scope.customSelected = $scope.PI.User.Name;
    $scope.doneLoading = data.doneLoading;

    //if($scope.getAll)$scope.onSelectPi($scope.PI);
    inspectors=[1];
    $scope.needNewHazards = true;

    var inspectionId;
    if($location.search().inspectionId)inspectionId = $location.search().inspectionId;
    setInspection($scope.PI.Key_id,inspectors,inspectionId);
  }

  function onFailGetPI(){
    alert('The system couldn\'t find the Principal Investigator');
  }

  function onGetAllPIs(data){
   // data = 
    $scope.PIs = data;
    $scope.doneLoadingAll = data.doneLoading;
    $scope.doneLoadingAll = true;
  } 

  function onFailGetAllPIs(){
    alert('Something went wrong getting the list of all Principal Investigators');
  }

//building api callback functions
  function onGetBuildings(data){
    $scope.Buildings = data;
  }

  function onFailGetBuildings(){
    alert('There was a problem getting the list of all buildings');
  }

  //callback function called when a PI is selected in the typeahead
  $scope.onSelectPi = function($item, $model, $label){
    getPi($item.Key_id);
  }

  $scope.selectedBuildings = [];
  $scope.selectBuildings = function($item, $model, $label){
   
    //replace with a request to get rooms by building
    $scope.buildings = [];
    var bldgCount = 0;
    angular.forEach($scope.inspection.Rooms, function(room, key){    
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
    if(!rooms){
      rooms = []
      angular.forEach($scope.buildings, function(building, key){
        angular.forEach(building.Rooms, function(room, key){
          rooms.push(room.Key_id);
        });
      });
    }
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

  //grab set user list data into the $scrope object
  function onGetHazards (data) {
    console.log(data);
    if(!data.InspectionRooms)data.InspectionRooms = [];
    $scope.hazards = data.SubHazards;
  //  console.log(data);
    angular.forEach($scope.hazards, function(hazard, key){
     // console.log(hazard);
      hazard.cssId = camelCase(hazard.Name);
    });
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
    hazard.showSubHazardsModal = !hazard.showSubHazardsModal;
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

  function getShowRooms(hazard){
    if(hazard.IsPresent){
     hazard.showRooms = false;
      angular.forEach(hazard.InspectionRooms, function(room, key){
        if(!hazard.InspectionRooms.every(roomDoesNotContainHazard) && !hazard.InspectionRooms.every(roomContainsHazard)){
          console.log(hazard.Name);
          hazard.showRooms = true;
        }else{
         // console.log(hazard.Name);
          hazard.showRooms = false;
        }
      });
      if(hazard.SubHazards.length){
        angular.forEach(hazard.SubHazards, function(child, key){
          getShowRooms(child);
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

        if(hazard.SubHazards.length){
          var releventRooms = hazard.InspectionRooms;
          angular.forEach(hazard.SubHazards, function(child, key){
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

    if(hazard.SubHazards.length  && hazard.IsPresent){

      var children = hazard.SubHazards;

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
        if(child.InspectionRooms.every(roomDoesNotContainHazard) ){
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
    getShowRooms(hazard);
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


  $scope.addHazardtoRoom = function( hazard, room, parent ){
    console.log(hazard);
    console.log(room);
    //console.log(parent);
    hazard.IsDirty = true;
    room.IsDirty = true;    
    parent.IsDirty = true;  

    room.waitingForServer = true;

    roomDto = {
      roomId: room.Key_id,
      hazardId: hazard.Key_id,
      add: true
    }

    var url = "../../ajaxaction.php?action=saveHazardRelation&roomId="+room.Key_id+"&hazardId="+hazard.Key_id+"&add=1hazar&callback=JSON_CALLBACK";
    convenienceMethods.updateObject(hazard.KeyId, room.KeyId, onAddHazardToRoom, onFailAddHazardToRoom, url, 'test', hazard, room, parent);
    if(parent){
      var url = "../../ajaxaction.php?action=saveHazardRelation&roomId="+room.Key_id+"&hazardId="+parent.Key_id+"&add=1hazar&callback=JSON_CALLBACK";
      convenienceMethods.updateObject(parent.KeyId, room.KeyId, onAddHazardToRoom, onFailAddHazardToRoom, url, 'test', hazard, room, parent);
    }
  }


  function onAddHazardToRoom(data, obj, haz, room, parent){
    haz.IsDirty = false;
    room.IsDirty = false;    
    room.ContainsHazard = true;
    /*
    angular.forEach(parent.InspectionRooms, function(parentRoom, key){
      if(parentRoom.Key_id == room.Key_id) parentRoom.ContainsHazard = true;
    });
    */
    room.waitingForServer = false;
    $scope.walkhazard(haz);
  }

  function onFailAddHazardToRoom(){
    alert("Something went wrong when trying to add the hazard to the room");
  }

  $scope.removeHazardFromRoom = function(hazard, room){
    console.log(hazard);
    room.waitingForServer = true;

    roomDto = {
      roomId: room.Key_id,
      hazardId: hazard.Key_id,
      add: false
    }

    var url = "../../ajaxaction.php?action=saveHazardRelation&roomId="+room.Key_id+"&hazardId="+hazard.Key_id+"&add=0&callback=JSON_CALLBACK";
    convenienceMethods.updateObject(hazard.KeyId, room.KeyId, onRemoveHazardFromRoom, onFailAddHazardToRoom, url, 'test', hazard, room);
  }


  function onRemoveHazardFromRoom(data, obj, haz, room){
    console.log(haz);
    console.log(room);
    room.ContainsHazard = false;
    room.waitingForServer = false;
    if(haz.InspectionRooms.every(roomDoesNotContainHazard)){
        haz.IsPresent = false;
        haz.IsDirty = false;
    }

    $scope.walkhazard(haz);
  }

  function onFailRemoveHazardFromRoom(){
    alert("Something went wrong when trying to remove the hazard from the room");
  }

  $scope.handleRoom = function(room, hazard, parent){
    console.log(room);
    room.ContainsHazard = !room.ContainsHazard;
    if(!room.ContainsHazard){
      $scope.addHazardtoRoom(hazard, room, parent);
     }else{
      $scope.removeHazardFromRoom(hazard, room, parent);      
    }
  }

  $scope.handleHazardChecked = function(hazard, parent){
    console.log(hazard);
    console.log(parent);
    if(parent.Key_id != 10000)parent.IsPresent = hazard.IsPresent
    $scope.selectedHazard = angular.copy(hazard);
    hazard.IsDirty = true;
    angular.forEach(hazard.InspectionRooms, function(room, key){
      if(!parent ||parent.Key_id != 10000)room.IsAllowed = true;
      console.log(room);
      convenienceMethods.setIsDirty(room);
      if(hazard.IsPresent && room.IsAllowed){
        console.log('here');
        $scope.addHazardtoRoom(hazard, room, parent);
      }else{
        if(room.ContainsHazard){
          $scope.removeHazardFromRoom(hazard, room, parent);
        }
      }
    });
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
hazardAssesment.controller( controllers );