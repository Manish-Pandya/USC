'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:AutoclavesCtrl
 * @description
 * # AutoclavesCtrl
 * Controller of the EquipmentModule Autoclaves view
 */
angular.module('EquipmentModule')
  .controller('AutoclavesCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = $scope.af = actionFunctionsFactory;
    
        var getAllAutoclaves = function(){
  			af.getAllAutoclaves()
  			.then(
  				function(autoclaves){  	
  					$scope.autoclaves = dataStore.Autoclave;
  				},
  				function(){}
  			)
  		}

  		getAllAutoclaves();

        $scope.deactivate = function(autoclave) {
            var copy = dataStoreManager.createCopy(autoclave);
            copy.Retirement_date = new Date();
            af.saveAutoclave(autoclave.pi, copy, autoclave);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Autoclave();
                object.Class = "Autoclave";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/autoclave-modal.html',
                controller: 'AutoclavesModalCtrl'
            });
        }

  })
  .controller('AutoclavesModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(copy, autoclave) {
            af.saveAutoclave(copy, autoclave)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            dataStore.Autoclave.push($scope.modalData.AutoclaveCopy);
            af.deleteModalData();
		}

	});
