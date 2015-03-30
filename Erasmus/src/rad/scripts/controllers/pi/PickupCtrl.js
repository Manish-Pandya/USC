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

	    $scope.openModal = function(templateName, object){
	        var modalData = {};
	        modalData.pi = $scope.pi;
	        if(object)modalData[object.Class] = object;
	        af.setModalData(modalData);
	        var modalInstance = $modal.open({
	          templateUrl: templateName+'.html',
	          controller: 'PickupModalCtrl'
	        });
	    }


	    //collection of things to be picked up
	    var pickup = new window.Pickup();

	    $scope.handleItemInPickup = function( item ){

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

		$scope.selectRoom = function(){
			$scope.modalData.SolidsContainerCopy.Room_id = $scope.modalData.SolidsContainerCopy.Room.Key_id;
		}

		$scope.saveSolidsContainer = function(pi, copy, container){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.saveSolidsContainer( pi, copy, container )
		}

		$scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
		}

	});
