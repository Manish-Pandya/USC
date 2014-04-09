var buildingHub = angular.module('buildingHub', ['ui.bootstrap','convenienceMethodModule']);


//called on page load, gets initial user data to list users
function buildingHubController($scope, $routeParams,$browser,$sniffer,$rootElement,$location, convenienceMethods) {
  $scope.users = [];
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    //get a building list
	  convenienceMethods.getData('../../ajaxaction.php?action=getAllBuildings&callback=JSON_CALLBACK',onGetBuildings,onFailGet);
  };

  //grab set user list data into the $scrope object
  function onGetBuildings(data) {
    console.log(data);
	  $scope.Buildings = data;
  }
  function onFailGet(){
    alert('Something went wrong when we tried to build the list of buildings.');
  }

  $scope.onSelectBuilding = function(building, $model, $label){
    $scope.building = building;
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

    buildingDto = {
      Class: "Building",
      Name: $scope.newBuilding.Name,
      Is_active: 1
    }

    if($scope.building){
      building = $scope.building;
    }

    if(update)buildingDto.Key_id = $scope.building.Key_id;

    var url = '../../ajaxaction.php?action=saveBuilding';
    convenienceMethods.updateObject(  buildingDto, building, onSaveBuilding, onFailSaveBuilding, url  );
  }

  function onSaveBuilding(data){
    $scope.building = {};
    $scope.building = angular.copy(data);
  }

  function onFailSaveBuilding(){
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

  $scope.createRoom = function(building){


    roomDTO = {
      Name: $scope.newRoom,
      KeyId: null,
      PIs:[],
      isNew: true,
      Class: "Room",
      Building_id: $scope.building.Key_id,
      Safety_contact_information : $scope.safety_contact_information
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

  $scope.saveRoom = function( building ){
    var url = '../../ajaxaction.php?action=getAllHazards&callback=JSON_CALLBACK';
    convenienceMethods.updateObject(  $scope.roomDTO, building, onAddRoom, onFailAddRoom, url  );




  }

  onAddRoom = function( objDTO, building ){
    //console.log(building);
    //console.log(objDTO);
    room = angular.copy($scope.roomDTO);
    room.isNew = false;
    building.rooms.shift();
    building.rooms.unshift(room);
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



};

//set controller
buildingHub.controller( 'buildingHubController', buildingHubController);