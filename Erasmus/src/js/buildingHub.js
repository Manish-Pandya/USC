var buildingHub = angular.module('buildingHub', ['ui.bootstrap','convenienceMethodModule']);


//called on page load, gets initial user data to list users
function buildingHubController($scope, $routeParams,$browser,$sniffer,$rootElement,$location, convenienceMethods) {
  $scope.users = [];
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  
  function init(){
    $scope.newRoom = false;
    //get a building list
	  convenienceMethods.getData('../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK',onGetBuildings,onFailGet);
  };

  //grab set user list data into the $scope object
  function onGetBuildings(data) {
    $scope.error = '';
	  $scope.Buildings = data;
    $scope.building = false;
    if($location.search().building){
      angular.forEach($scope.Buildings, function(building, key){
        if(building.Key_id === $location.search().building){
          $scope.building = building;
          $scope.selectedBuilding = building;
        }
      });
    }
  }

  function onFailGet(){
    $scope.error="There was a problem retrieving the list of all the buildings in the system.  Please check your internet connection and try again."
  }

  $scope.onSelectBuilding = function(buildingDTO, $model, $label){
    $scope.building = buildingDTO;
    $location.search({building: buildingDTO.Key_id});
  }

  $scope.onSelectRoom = function(room, $model, $label){
    $scope.room = room;
    var url = '../../ajaxaction.php?action=getHazardsInRoom&roomId='+room.Key_id+'&subHazards=false&callback=JSON_CALLBACK';
    convenienceMethods.getData( url, onGetHazards, onFailGetHazards );

  }

  function onGetHazards(data){

    $scope.bioHazards = [];
    $scope.chemicalHazards = [];
    $scope.radHazards = []

    angular.forEach(data, function(hazard, key){
      console.log(hazard.ParentIds)
      if(hazard.ParentIds.indexOf("1") > -1){
        console.log(hazard)
        $scope.bioHazards.push(hazard);
      }
    });
  }

  function onFailGetHazards(){

  }

  function getBuilding(id){
    convenienceMethods.getData('../../ajaxaction.php?action=getBuildingById&id='+id+'&callback=JSON_CALLBACK',onGetBuilding,onFailGet);
  }

  function onGetBuilding(data){
    console.log(data);
  }

  $scope.showCreateBuilding = function(){

    $scope.showAdmin = !$scope.showAdmin;
    if($scope.building)$scope.newBuilding = $scope.building;

  }

  $scope.createBuilding = function(update){

    if($scope.newBuilding){
      var name = $scope.newBuilding.Name;
      $scope.newBuilding.IsDirty = true;
    }
    if($scope.buildingCopy){
      var name = $scope.buildingCopy.Name;
       $scope.buildingCopy.IsDirty = true;
    }

    buildingDto = {
      Class: "Building",
      Name: name,
      Is_active: 1
    }

    if($scope.building){
      building = $scope.building;
    }else{
      building = buildingDto
    }

    if(update)buildingDto.Key_id = $scope.building.Key_id;
    var url = '../../ajaxaction.php?action=saveBuilding';
    convenienceMethods.updateObject(  buildingDto, building, onSaveBuilding, onFailSaveBuilding, url  );
  }

  function onSaveBuilding(data){
    if($scope.newBuilding)$scope.newBuilding.IsDirty = false;
    if($scope.buildingCopy)$scope.buildingCopy.IsDirty = false;

    $scope.building = {};
    $scope.building = angular.copy(data);
  }

  function onFailSaveBuilding(){
       if($scope.newBuilding)$scope.newBuilding.IsDirty = false;
    if($scope.buildingCopy)$scope.buildingCopy.IsDirty = false;
    alert("There was an error when the system tried to save the building.");
  }

  $scope.reveal = function(building){
    angular.forEach($scope.buildings, function(thisBuilding, key){
        thisBuilding.showChildren = false;
    });
    building.showChildren = true;
  }

  $scope.deactivateBuilding = function(building){

  }

  $scope.editBuilding = function(building){
    building.edit = true;
    $scope.buildingCopy = angular.copy(building);
  }

  $scope.cancelEditBuilding = function(building){
    building.edit = false;
    $scope.buildingCopy = {};
  }

  $scope.editRoom = function(room){
    room.edit = true;
    $scope.roomCopy = angular.copy(room);
  }

  $scope.cancelEditRoom = function(room){
    room.edit = false;
    $scope.roomCopy = {};
  }

  $scope.saveEditedRoom = function(room){
    console.log($scope.roomCopy);
    $scope.roomCopy.IsDirty = true;
    var url = '../../ajaxaction.php?action=saveRoom';
    convenienceMethods.updateObject(  $scope.roomCopy, $scope.building, onAddRoom, onFailSaveRoom, url, room );
  }

  $scope.createRoom = function(building){

    roomDTO = {
      Name: $scope.roomCopy.Name,
      PIs:[],
      isNew: true,
      Class: "Room",
      Building_id: $scope.building.Key_id
    };

    var url = '../../ajaxaction.php?action=saveRoom';
    convenienceMethods.updateObject(  roomDTO, $scope.building, onSaveRoom, onFailSaveRoom, url  );
  }

  function onSaveRoom(data){
    $scope.building.Rooms.push(data);
  }

  function onFailSaveRoom(){
    alert('Something went wrong when the system tried to save the room.')
  }

  var onAddRoom = function( returnedFromServer, building ){

    if($scope.roomDTO)room = angular.copy($scope.roomDTO);
    if($scope.roomCopy)room = $scope.roomCopy;
    room.isNew = false;
    room.edit = false;
    room.IsDirty = false;
    $scope.building.Rooms.shift();
    if(!convenienceMethods.arrayContainsObject($scope.building.Rooms,room)){
      $scope.building.Rooms.unshift(room);
      $scope.newRoom = false;
    }else{
      var idx = convenienceMethods.arrayContainsObject($scope.building.Rooms,room, null, true);
      room = angular.copy(returnedFromServer);
      $scope.roomCopy = {};
    }
  }

  onFailAddRoom = function(obj){
    alert('There was a problem when saving '+obj);
  }

  $scope.removeRoomFromBuilding = function(room, building){
    //remove a room from a building?
  }

  $scope.addPItoRoom = function(room){

    $scope.piDTO = {
      KeyId: null,
      Hazards: [],
      isNew: true,
      Class: "PI"
    };

    room.PIs.unshift($scope.piDTO);
  }

  $scope.saveNewPI = function( room, customSelected ){
    console.log(customSelected);
   $scope.piDTO.Name = customSelected.Name;
    var url = '../../ajaxaction.php?action=getAllHazards&callback=JSON_CALLBACK';
    convenienceMethods.updateObject(  $scope.piDTO, room, onAddPI, onFailAddPI, url  );
  }

  onAddPI = function( objDTO, room ){
    console.log($scope.piDTO);
    PI = angular.copy($scope.piDTO);
    PI.isNew = false;
    room.PIs.shift();
    room.PIs.unshift(PI);
  }

  onFailAddPI = function(obj){
    alert('There was a problem when saving '+obj);
  }

  $scope.removePIfromRoom = function(pi, room){

  }
  $scope.setAddNewRoom = function(){
    console.log('new room')
    $scope.newRoom = true;
    $scope.roomCopy = {
      Class: 'Room',
      Building_id: $scope.building.Key_id,
      Name: ''
    }
    if(!$scope.building.Rooms){
      $scope.building.Rooms = [];
    }
  }
  $scope.cancelNewRoom = function(){
    $scope.newRoom = false;
  }
};