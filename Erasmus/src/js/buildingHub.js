var buildingHub = angular.module('buildingHub', ['ui.bootstrap','convenienceMethodModule']);


//called on page load, gets initial user data to list users
function buildingHubController($scope, $routeParams,$browser,$sniffer,$rootElement,$location, convenienceMethods) {
  $scope.users = [];
  
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
	  convenienceMethods.getData('buildingMock.php?&callback=JSON_CALLBACK',onGetBuildings,onFailGet);
	  console.log('init called');
  };
  //grab set user list data into the $scrope object
  function onGetBuildings(data) {
	  $scope.buildings = data;
  }
  function onFailGet(){
    alert('Something went wrong when we tried to build the list of buildings.');
  }

  $scope.reveal = function(building){
    angular.forEach($scope.buildings, function(thisBuilding, key){
        thisBuilding.showChildren = false;
    });
    building.showChildren = true;
  }

  $scope.createBuilding = function(){
    //add a new building to the system
  }

  $scope.editBuilding = function(building){

  }

  $scope.deactivateBuilding = function(building){

  }

  $scope.addRoomToBuilding = function(building){

    $scope.roomDTO = {
      name: '',
      KeyId: null,
      PIs:[],
      isNew: true,
      Class: "Room"
    };

    building.rooms.unshift($scope.roomDTO);
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

  $scope.PIs = [{"Name":"John Investigator 1","flag":"5/5c/Flag_of_Alabama.svg/45px-Flag_of_Alabama.svg.png"},{"Name":"John Investigator2","flag":"e/e6/Flag_of_Alaska.svg/43px-Flag_of_Alaska.svg.png"},{"Name":"John Investigator3","flag":"9/9d/Flag_of_Arizona.svg/45px-Flag_of_Arizona.svg.png"}];



};

//set controller
buildingHub.controller( 'buildingHubController', buildingHubController);