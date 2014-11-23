var locationHub = angular.module('locationHub', ['ui.bootstrap','convenienceMethodModule','once'])

.config(function($routeProvider){
	$routeProvider
		.when('/rooms', 
			{
				templateUrl: 'locationHubPartials/rooms.html', 
				controller: roomsCtrl
			}
		)
		.when('/buildings', 
			{
				templateUrl: 'locationHubPartials/buildings.html', 
				controller: buildingsCtrl
			}
		)		
		.when('/campuses', 
			{
				templateUrl: 'locationHubPartials/campuses.html', 
				controller: campusesCtrl
			}
		)
		.otherwise(
			{
				redirectTo: '/rooms'
			}
		);
})
.filter('genericFilter', function () {
	return function (input) {
		console.log(input);
		return input;
	};
})
.factory('locationHubFactory', function(convenienceMethods,$q){
	var factory = {};
	factory.rooms = [];
	factory.buildings = [];
	factory.campuses = [];
	factory.modalData;

	factory.getRooms = function(){
		console.log('getting rooms');
		//if we don't have a the list of pis, get it from the server
		var deferred = $q.defer();
		//lazy load
		if(this.rooms.length){
			deferred.resolve(this.rooms);
		}else{
			var url = '../../ajaxaction.php?action=getAllRooms&callback=JSON_CALLBACK';
	    	convenienceMethods.getDataAsDeferredPromise(url).then(
				function(promise){
					deferred.resolve(promise);
				},
				function(promise){
					deferred.reject();
				}
			);
		}

		deferred.promise.then(
			function(rooms){
				factory.rooms = rooms;
			}
		)

		return deferred.promise;
		 
    }


	factory.getBuildings = function()
	{
		console.log('getting buildings');
		//if we don't have a the list of pis, get it from the server
		var deferred = $q.defer();
				//lazy load
		if(this.buildings.length){
			deferred.resolve(this.buildings);
		}else{
			var url = '../../ajaxaction.php?action=getAllBuildings&skipRooms=true&callback=JSON_CALLBACK';
	    	convenienceMethods.getDataAsDeferredPromise(url).then(
				function(promise){
					deferred.resolve(promise);
				},
				function(promise){
					deferred.reject();
				}
			);
		}
		deferred.promise.then(
			function(buildings){
				factory.buildings = buildings;
			}
		)
		return deferred.promise;
	}

	factory.setBuildings = function(buildings)
	{
		this.buildings = buildings;
	}
	factory.getCampuses = function()
	{
		//get campuses from this.buildings so that we can keep views in syn
	}
	factory.setCampuses = function( campuses )
	{
		this.campuses = campuses
	}

	factory.getBuildingByRoom = function( room )
	{	
		if(!room.trusted){
			var i = this.buildings.length
			while(i--){
				room.trusted = true;
				if(this.buildings[i].Key_id == room.Building_id){
					room.Building = this.buildings[i];
				}
			}
		}

		return room.Building;
	}

	factory.saveRoom = function(roomDto){
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

	factory.saveBuilding = function(buildingDto){
		var url = "../../ajaxaction.php?action=saveBuilding";
		var deferred = $q.defer();
		convenienceMethods.saveDataAndDefer(url, buildingDto).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise
	}


	factory.saveCampus = function(campusDto){
		var url = "../../ajaxaction.php?action=saveBuilding";
		var deferred = $q.defer();
		convenienceMethods.saveDataAndDefer(url, campusDto).then(
			function(promise){
				deferred.resolve(promise);
			},
			function(promise){
				deferred.reject();
			}
		);	
		return deferred.promise
	}

	factory.setModalData = function( data )
	{
		this.modalData = data;
	}

	factory.getModalData = function()
	{
		return this.modalData;
	}


	return factory;
});


routeCtrl = function($scope, $location){
	$scope.location = $location.path();
	$scope.setRoute = function(route){
		$location.path(route);
		$scope.location = route;
	}
}

roomsCtrl = function($scope, $rootScope, $location, convenienceMethods, $modal, locationHubFactory){
	$scope.loading = true;
	$scope.lhf = locationHubFactory;

	locationHubFactory.getBuildings()
		.then(
			function(buildings){
				console.log(buildings);
				locationHubFactory.getRooms()
				.then(
					function(rooms){
						console.log('got rooms');
						$scope.rooms = rooms;
						$scope.loading = false;
					}
				)
			}
		)

	

}

var buildingsCtrl = function ($scope, $rootScope, $modalInstance, PI, adding, locationHubFactory, $q) {

}


campusesCtrl = function($scope, $location, convenienceMethods, $modal, locationHubFactory){


}