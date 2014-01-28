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
    console.log(data);
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

};

//set controller
buildingHub.controller( 'buildingHubController', buildingHubController);