var piHub = angular.module('locationHub', ['ui.bootstrap','convenienceMethodModule'])

.config(function($routeProvider){
  $routeProvider
    .when('/rooms', 
      {
        templateUrl: 'locationHubPartial/rooms.html', 
        controller: roomsCtrl
      }
    )
    .when('/personnel', 
      {
        templateUrl: 'locationHubPartial/buildings.html', 
        controller: buildingsCtrl
      }
    )   
    .when('/departments', 
      {
        templateUrl: 'locationHubPartial/campuses.html', 
        controller: campusesCtrl
      }
    )
    .otherwise(
      {
        redirectTo: '/rooms'
      }
    );
})

.factory('piHubFactory', function(convenienceMethods,$q){
  var factory = {};
  
  factory.createRoom = function(roomDto){
    var url = "../../ajaxaction.php?action=saveRoom";
    var deferred = $q.defer();
    convenienceMethods.saveDataAndDefer(url, roomDto).then(
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

routeController = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory){

  $scope.setRoute = function(route)
  {
    $scope.route = route;
    $location.path(route);
  }

}

roomsCtrl = function($scope, convenienceMethods, $modal, piHubFactory){


}

buildingsCtrl = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory){

}

campusesCtrl = function($scope, $rootScope, $location, convenienceMethods, $modal, piHubFactory){


}