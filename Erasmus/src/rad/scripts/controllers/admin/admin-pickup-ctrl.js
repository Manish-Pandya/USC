'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('AdminPickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.pickupsPromise = af.getAllPickups()
  			.then(
  				function(pickups){  					
  					$scope.pickups = pickups;
  				},
  				function(){}
  			)

  })
  .controller('AdminPickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
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
