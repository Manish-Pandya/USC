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
	return function (items,search) {
		if(search){
			var i = 0;
			if(items)i = items.length;
			var filtered = [];

			var isMatched = function(input, item){
				if(item.Name == input)return true;
				return false;
			}

			while(i--){

				//we filter for every set search filter, looping through the collection only once

				var item=items[i];
				item.matched = true;

				if(search.building){
					if( item.Building.Name && item.Building.Name.toLowerCase().indexOf(search.building.toLowerCase() ) < 0 ){
						item.matched = false;
					}
				}

				if(search.room){
					if( item.Name && item.Name.toLowerCase().indexOf(search.room) < 0 )  item.matched = false;
				}


				if(search.pi || search.department){
					if(!item.PrincipalInvestigators.length){
						item.PrincipalInvestigators = [{User:{Name: 'Unassigned'}}]; 
					}

					var j = item.PrincipalInvestigators.length

					while(j--){
						var pi = item.PrincipalInvestigators[j];
						if( search.pi && pi.User.Name && pi.User.Name.toLowerCase().indexOf(search.pi) < 0 ) item.matched = false;

						if(search.department){
							if(!pi.Departments){
								item.matched = false;
							}else{
								var k = pi.Departments.length;
								while(k--){
									if( pi.Departments && pi.Departments[k].Name && pi.Departments[k].Name.toLowerCase().indexOf(search.department) < 0 ) item.matched = false;
								}
							}

						} 
					}

				}


				if(item.matched == true)filtered.push(item);

			}
			filtered.reverse();
			return filtered;
		}else{
			return items;
		}
	};
})
.factory('locationHubFactory', function(convenienceMethods,$q){
	var factory = {};
	factory.rooms = [];
	factory.buildings = [];
	factory.campuses = [];
	factory.modalData;

	factory.getRooms = function(){
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

	factory.handleObjectActive = function(object)
	{
		var copy = convenienceMethods.copyObject( object );
		console.log(copy);
		copy.Is_active = !copy.Is_active;

		this['save'+object.Class](copy)
			.then(
				function(returned){
					object = returned;
					//TODO:  change factory's properties to uppercase, remove stupid toLowercase() calls
					var i = factory[object.Class.toLowerCase()+'s'].length

					while(i--){
						if( factory[object.Class.toLowerCase()+'s'][i].Key_id ==  copy.Key_id) factory[object.Class.toLowerCase()+'s'][i] = copy;
					}

				},
				function(){
    				$scope.error = 'The' + object.Class + ' could not be saved.  Please check your internet connection and try again.'
				}
			)

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
				locationHubFactory.getRooms()
				.then(
					function(rooms){
						$scope.rooms = rooms;
						$scope.loading = false;
					}
				)
			}
		)

	$scope.openRoomModal = function(room){

		if(!room)room = {Is_active: true, Class:'Room', Name:'', Building:{}};

		locationHubFactory.setModalData(room);

	    var modalInstance = $modal.open({
	      templateUrl: 'rooms-modal.html',
	      controller: modalCtrl
	    });


        modalInstance.result.then(function () {
	       locationHubFactory.getRooms()
				.then(
					function(rooms){
						console.log('got rooms');
						$scope.rooms = rooms;
						$scope.loading = false;
					}
				)
	    });

	}


}

var buildingsCtrl = function ($scope, $rootScope, $modalInstance, PI, adding, locationHubFactory, $q) {

}


campusesCtrl = function($scope, $location, convenienceMethods, $modal, locationHubFactory){


}

modalCtrl = function($scope, locationHubFactory, $modalInstance, convenienceMethods){

	//make a copy without reference to the modalData so we can manipulate our object without applying changes until we save
	$scope.modalData = convenienceMethods.copyObject( locationHubFactory.getModalData() );
	$scope.buildings = locationHubFactory.getBuildings();
	$scope.campuses = locationHubFactory.getCampuses();

	$scope.cancel = function () {
      $modalInstance.dismiss();
    };


    $scope.onSelectBuilding = function(building){
    	$scope.modalData.Building_id = building.Key_id;
    }

    $scope.save = function(obj){
    	//unset global error, if it exists.
    	$scope.error = null;

    	locationHubFactory['get'+obj.Class+'s']().then(
    		function(stuff){
    			var collection = stuff;
    			locationHubFactory['save'+obj.Class]( obj ).then(
	    			function(returned){
	    				if( obj.Key_id ){
	    					//we are editing an old object
	    					var i = collection.length;
	    					while(i--){
	    						//var objectInCollection = collection[i];
	    						if(collection[i].Key_id == returned.Key_id){
	    							collection[i] = returned;
	    							break;
	    						}
	    					}
	    				}else{
	    					//we are creating an new object
	    					collection.push(returned);
	    				}
				        $modalInstance.close();
	    			},
	    			function(){
	    				$scope.error = 'The' + obj.Class + ' could not be saved.  Please check your internet connection and try again.'
	    				$modalInstance.dismiss();
	    			}
    			)
    		}
    		
    	);

    }

}