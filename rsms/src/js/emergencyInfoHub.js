var emergencyInfo = angular.module('emergencyInfo', ['ui.bootstrap','convenienceMethodWithRoleBasedModule'])

.factory('emergencyInfoFactory', function( convenienceMethods, $q, $rootScope ){

    var factory = {};

    factory.getAllPIs = function()
    {
        //if we don't have a the list of pis, get it from the server
        //var deferred = $q.defer();
        var url = '../../ajaxaction.php?action=getAllPIs&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.getAllBuildings = function()
    {

        var url = '../../ajaxaction.php?action=getAllBuildings&skipRooms=true&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.getHazards = function(room)
    {

        //the server expects an array of roomIds, but we are only going to send one, so wrap it in an array;
        var rooms = [room.Key_id];
        var url = '../../ajaxaction.php?action=getHazardRoomMappingsAsTree&'+$.param({roomIds:rooms})+'&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );

    }

    factory.onSelectPIOrBuilding = function( object )
    {
        console.log(object);
        var len = object.Rooms.length;
        var displayRooms = [];

        while( len-- ){
            var room = object.Rooms[len];
            room.roomText = 'Room: '+room.Name;

            if(room.Building){
              room.roomText = room.roomText + ' | ' + room.Building.Name;
              if(room.Building.Physical_address) room.roomText = room.roomText + ' | ' + room.Building.Physical_address;
            }

        }

        if(!object.Rooms)$rootScope.error = "The selected location or PI has no rooms in the system."
        $rootScope.rooms = object.Rooms;

    }

    factory.onSelectPI = function( pi )
    {
        $rootScope.gettingRoomsForPI = true;
        this.getRoomsByPI( pi )
          .then(
            function( rooms ){
                console.log(rooms);
                pi.Rooms = rooms;
                var displayRooms = [];
                var len = pi.Rooms.length;
                while( len-- ){
                    var room = pi.Rooms[len];
                    room.roomText = 'Room: '+room.Name;
                    if(room.Building){
                      room.roomText = room.roomText + ' | ' + room.Building.Name;
                      if(room.Building.Physical_address) room.roomText = room.roomText + ' | ' + room.Building.Physical_address;
                    }
                }
              $rootScope.gettingRoomsForPI = false;
              if(!pi.Rooms)$rootScope.error = "The selected location or PI has no rooms in the system."
              $rootScope.rooms = pi.Rooms;
            }
          )
    }

    factory.getRoomsByPI = function(pi)
    {
        var url = '../../ajaxaction.php?action=getRoomsByPIId&piId='+pi.Key_id+'&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.onSelectBuilding = function( building )
    {
        $rootScope.rooms = null;
        $rootScope.gettingRooms = true;
        console.log(building);
        if(building.Rooms){
          factory.onSelectPIOrBuilding(building);
        }else{
          var url = '../../ajaxaction.php?action=getRoomsByBuildingId&id='+building.Key_id+'&callback=JSON_CALLBACK';
          convenienceMethods.getDataAsDeferredPromise(url).then(
              function(promise){
                building.Rooms = promise;
                $rootScope.gettingRooms = false;
                factory.onSelectPIOrBuilding(building );
              },
              function(promise){
              }
          );
        }
    }

    factory.getPIsByRoom = function( room )
    {

        var url = '../../ajaxaction.php?action=getPIsByRoomId&id='+room.Key_id+'&callback=JSON_CALLBACK';
        return convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              return promise;
            },
            function(promise){
            }
        );
    }

    factory.noSubHazardsPresent = function( hazard )
    {
        if(hazard.ActiveSubHazards.every(this.hazardIsNotPresent))return true;
        return false;
    }

    factory.hazardIsNotPresent = function( hazard )
    {
        console.log(hazard);
        if(!hazard.IsPresent)return true;
        return false;
    }

    return factory;
});

//called on page load, gets initial user data to list users
function emergencyInfoController(  $scope, $rootScope, convenienceMethods, emergencyInfoFactory ) {
  $scope.users = [];
  var eif = emergencyInfoFactory;
  $scope.eif = eif;

  init();

  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
      //get a building list
      eif.getAllBuildings()
        .then(
            function(buildings){
                $scope.buildings = buildings;
                return buildings;
            },
            function(e){
                $scope.error = 'The system couldn\'t load the list of Buildings.  Please check your internet connection and try again.'
            }
        );

      //get a PI list
      eif.getAllPIs()
        .then(
            function(pis){
                $scope.pis = pis;
                return pis;
            },
            function(e){
                $scope.error = 'The system couldn\'t load the list of Principal Investigators.  Please check your internet connection and try again.'
            }
        );
  };

  //grab set user list data into the $scrope object
  function onGetBuildings(data) {
    $scope.Buildings = data;
    $scope.error = '';

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

  $scope.resetSearch = function(){
    $scope.showingHazards = !$scope.showingHazards;
    $scope.selectedRoom = null;
    $scope.searchType = null;
    $scope.room = null;
    $scope.building = null;
    $scope.rooms = null;
  };

  $scope.onSelectRoom = function( room )
  {
        $scope.loading = true;
        $scope.selectedRoom = room;
        eif.getHazards( room ).
          then(
            function(rootHazard){
              console.log(room);
              $scope.hazards = rootHazard.ActiveSubHazards;

              eif.getPIsByRoom(room)
                .then(
                    function(pis){
                      $scope.pisByRoom = pis;
                      $scope.personnel = [];
                      var len = pis.length;
                      while(len--){
                          $scope.personnel = $scope.personnel.concat(pis[len].LabPersonnel);
                      }
                      $scope.loading = false;
                      $scope.showingHazards = true;
                      var i = $scope.buildings.length;
                      while(i--){
                        if(room.Building_id == $scope.buildings[i].Key_id)$scope.building = $scope.buildings[i];
                      }

                      $scope.room = room;
                    }
                )


            },
            function(){

            }

          )

  }


};
