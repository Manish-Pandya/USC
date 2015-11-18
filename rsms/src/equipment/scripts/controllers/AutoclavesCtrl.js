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
  		var af = actionFunctionsFactory;

  		$scope.af = af;
    
        $scope.autoclaves = [];
    
        $scope.deactivate = function(autoclave) {
            
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
		var af = actionFunctionsFactory;
		$scope.af = af;

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
