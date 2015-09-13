'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('CarboysCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		var getAllCarboys = function(){
  			af.getAllCarboys()
  			.then(
  				function(carboys){  	
  					$scope.carboys = dataStore.Carboy;
  				},
  				function(){}
  			)
  		}

  		$scope.af = af;
  		$rootScope.carboysPromise = af.getAllPIs()
  										.then(getAllCarboys);
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Carboy();
                object.Class = "Carboy";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/carboy-modal.html',
                controller: 'CarboysModalCtrl'
            });
        }

  })
  .controller('CarboysModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(carboy) {
            af.saveCarboy(carboy.PrincipalInvestigator, carboy, $scope.modalData.Carboy)
                .then($scope.close);
        }

		$scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
		}

	});
