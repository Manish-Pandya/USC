'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.piPromise = af.getRadPIById($stateParams.pi)
  			.then(
  				function(pi){  					
  					//pi.loadRooms();
  					if(pi.Pickups){
	  					var i = pi.Pickups.length;
	  					$scope.scheduledPickups = [];
	  					while(i--){
							if(!pi.Pickups[i].Pickup_date){
								$scope.scheduledPickups.unshift(pi.Pickups[i]);
							};
	  					}
  					}
					$scope.pi = pi;
  				},
  				function(){}
  			)


	   


	    $scope.createPickup = function(pi){
	    	//collection of things to be picked up
	    	
	    	if(pi.Pickups.length){
			    var i = pi.Pickups.length;
			    while(i--){
				    if(pi.Pickups[i].Status == "REQUESTED")var pickup = pi.Pickups[i];
			    }
			}

			if(!pickup){
				var pickup = new window.Pickup();
			    pickup.Class="Pickup";
			    pickup.Carboy_use_cycles = [];
			    pickup.Scint_vial_collections = [];
			    pickup.Waste_bags = [];
			    pickup.Principal_investigator_id = null;
			    pickup.Requested_date = convenienceMethods.setMysqlTime(Date());
			    pickup.Status = "REQUESTED";
		    	pickup.Principal_investigator_id = pi.Key_id;
			}

	    	//include proper objects in pickup
	    	var i = pi.SolidsContainers.length;
	    	while(i--){
	    		var container = pi.SolidsContainers[i];
	    		var j =  container.WasteBagsForPickup.length;
	    		while(j--){
	    			if( container.WasteBagsForPickup[j].include && !convenienceMethods.arrayContainsObject(pickup.Waste_bags, container.WasteBagsForPickup[j]))pickup.Waste_bags.push( container.WasteBagsForPickup[j] );
	    		}
	    	}

	    	var i = pi.CurrentScintVialCollection.length;
	    	while(i--){
	    		if( pi.CurrentScintVialCollection[i].include && !convenienceMethods.arrayContainsObject(pickup.Scint_vial_collections, pi.CurrentScintVialCollection[i]) ) pickup.Scint_vial_collections.push( pi.CurrentScintVialCollection[i] );
	    	}

	    	var i = pi.CarboyUseCycles.length;
	    	while(i--){
	    		if( pi.CarboyUseCycles[i].include && !convenienceMethods.arrayContainsObject(pickup.Carboy_use_cycles, pi.CarboyUseCycles[i])  )pickup.Carboy_use_cycles.push( pi.CarboyUseCycles[i] );
	    	}
	    	var modalData = {};
	        modalData.pi = pi;
	        modalData.pickup = pickup;
	        af.setModalData(modalData);
	        var modalInstance = $modal.open({
	          templateUrl: 'views/pi/pi-modals/pickup-modal.html',
	          controller: 'PickupModalCtrl'
	        });

	    }

	    $scope.solidsContainersHavePickups = function(containers){
	    	var i = containers.length;
	    	while(i--){
	    		//if(!containers[i].WasteBagsForPickup.length)return false;
	    		if($scope.hasPickupItems(containers[i].WasteBagsForPickup))return true;
	    	}
	    	return false;
	    }


	    $scope.hasPickupItems = function(collection){
	    	//if(!collection.length)return false;
	    	var hasPickupItems = false;
	    	var i = collection.length;
	    	while(i--){
	    		if(!collection[i].Pickup_id && collection[i].Contents.length){
	    			hasPickupItems = true;
	    		}

	    	}
	    	return hasPickupItems;
	    }

  })
  .controller('PickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;

		$scope.modalData = af.getModalData();

		if(!$scope.modalData.SolidsContainerCopy){
		    $scope.modalData.SolidsContainerCopy = {
		        Class: 'SolidsContainer',
		        Room_id:null,
		        Is_active: true
		    }
		}

		$scope.requestPickup = function(pickup){

			var pickupCopy = dataStoreManager.createCopy(pickup);
			af.savePickup(pickup,pickupCopy)
				.then(
					function(){

					},
					function(){

					}
				)
		}


		$scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
		}

	});
