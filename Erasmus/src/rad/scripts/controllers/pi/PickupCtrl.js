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
							if(pi.Pickups[i].Requested_date){
								$scope.scheduledPickups.unshift(pi.Pickups[i]);
							};
	  					}
	  				}
					$scope.pi = pi;
  				},
  				function(){}
  			)


	    //collection of things to be picked up
	    var pickup = new window.Pickup();
	    pickup.Class="Pickup";
	    pickup.Carboy_use_cycles = [];
	    pickup.Scint_vial_collections = [];
	    pickup.Waste_bags = [];
	    pickup.Principal_investigator_id = null;
	    pickup.Requested_date = convenienceMethods.setMysqlTime(Date());
	    pickup.Status = "Requested";


	    $scope.createPickup = function(pi){

	    	pickup.Principal_investigator_id = pi.Key_id;

	    	//include proper objects in pickup
	    	var i = pi.SolidsContainers.length;
	    	while(i--){
	    		var container = pi.SolidsContainers[i];
	    		var j =  container.WasteBagsForPickup.length;
	    		while(j--){
	    			if( container.WasteBagsForPickup[j].include )pickup.Waste_bags.push( container.WasteBagsForPickup[j] );
	    		}
	    	}

	    	var i = pi.CurrentScintVialCollection.length;
	    	while(i--){
	    		if( pi.CurrentScintVialCollection[i].include ) pickup.Scint_vial_collections.push( pi.CurrentScintVialCollection[i] );
	    	}

	    	var i = pi.CarboyUseCycles.length;
	    	while(i--){
	    		if( pi.CarboyUseCycles[i].include )pickup.Carboy_use_cycles.push( pi.CarboyUseCycles[i] );
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
	    	var i = collection.length;
	    	while(i--){
	    		if(!collection[i].Pickup_id){
	    			return true;
	    		}
	    	}
	    	return false;
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
