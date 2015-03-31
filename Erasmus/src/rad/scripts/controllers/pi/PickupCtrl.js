'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
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
								scheduledPickups.unshift(pi.Pickups[i]);
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
	    pickup.Carboys = [];
	    pickup.ScintVialCollections = [];
	    pickup.WasteBags = [];
	    pickup.Principal_investigator_id = null;


	    $scope.createPickup = function(pi){

	    	pickup.Principal_investigator_id = pi.Key_id;

	    	//include proper objects in pickup
	    	var i = pi.SolidsContainers.length;
	    	while(i--){
	    		var container = pi.SolidsContainers[i];
	    		var j =  container.WasteBagsForPickup.length;
	    		while(j--){
	    			if( container.WasteBagsForPickup[j].include )pickup.WasteBags.push( container.WasteBagsForPickup[j] );
	    		}
	    	}

	    	var i = pi.CurrentScintVialCollection.length;
	    	while(i--){
	    		if( pi.CurrentScintVialCollection[i].include ) pickup.ScintVialCollections.push( pi.CurrentScintVialCollection[i] );
	    	}

	    	var i = pi.CarboyUseCycles.length;
	    	while(i--){
	    		if( pi.CarboyUseCycles[i].include )pickup.Carboys.push( pi.CarboyUseCycles[i].Carboy );
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
			console.log(pickup)
			af.savePickup(pickup)
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
