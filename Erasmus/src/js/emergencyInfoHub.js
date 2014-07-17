var emergencyInfo = angular.module('emergencyInfo', ['ui.bootstrap','convenienceMethodModule']);


//called on page load, gets initial user data to list users
function emergencyInfoController($scope, $routeParams,$browser,$sniffer,$rootElement,$location, convenienceMethods, $location) {
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
    $scope.error = '';
    console.log($location.search());

    if($location.search().building){
      angular.forEach($scope.Buildings, function(building, key){
        if(building.Key_id == $location.search().building){
          $scope.building = building;
          $scope.selectedBuilding = building;
        }
      });
      
    }

  }
  function onFailGet(){
   $scope.error = 'Something went wrong when we tried to build the list of buildings.';
  }

  $scope.onSelectBuilding = function(building, $model, $label){
    $scope.building = building;
  }

  $scope.onSelectRoom = function(room, $model, $label){
    $scope.gettingHazards = true;
    $scope.room = room;
    var rooms = []
    rooms.push(room.Key_id);

    var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';

    convenienceMethods.getData( url, onGetHazards, onFailGetHazards );

  }

  function onGetHazards(data){
    console.log(data);
    $scope.gettingHazards = false;

    var numberOfHazardsPresent = 0;
    angular.forEach(data.SubHazards, function(hazard, key){
      console.log(hazard);
      hazard.cssId = camelCase(hazard.Name);
      if(hazard.IsPresent)numberOfHazardsPresent++;
    });

    $scope.numberOfHazardsPresent = numberOfHazardsPresent;
    $scope.hazards = data.SubHazards;

   
/*
    angular.forEach(data, function(hazard, key){
      console.log(hazard.ParentIds)
      if(hazard.ParentIds.indexOf("1") > -1){
        console.log(hazard)
        $scope.bioHazards.push(hazard);
      }
    });
*/
  }

  function onFailGetHazards(){
    $scope.error = 'There was a problem getting the hazards.  Please check your internet connection and try again.'
  }

  function getBuilding(id){
    convenienceMethods.getData('../../ajaxaction.php?action=getBuildingById&id='+id+'&callback=JSON_CALLBACK',onGetBuilding,onFailGet);
  }

  function onGetBuilding(data){
    console.log(data);
  }

  function camelCase(input) {
    return input.toLowerCase().replace(/ (.)/g, function(match, group1) {
      return group1.toUpperCase();
    });
  }

};