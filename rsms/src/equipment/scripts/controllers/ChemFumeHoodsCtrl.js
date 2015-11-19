'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:ChemFumeHoodsCtrl
 * @description
 * # ChemFumeHoodsCtrl
 * Controller of the EquipmentModule Chemical Fume Hoods view
 */
angular.module('EquipmentModule')
  .controller('ChemFumeHoodsCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = $scope.af = actionFunctionsFactory;

        $scope.hoods = [];
    
        $scope.deactivate = function(hood) {
            var copy = dataStoreManager.createCopy(hood);
            copy.Retirement_date = new Date();
            af.saveChemFumeHood(hood.pi, copy, hood);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.ChemFumeHood();
                object.Class = "ChemFumeHood";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/chem-fume-hood-modal.html',
                controller: 'ChemFumeHoodModalCtrl'
            });
        }

  })
  .controller('ChemFumeHoodModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(copy, chemFumeHood) {
            af.saveAutoclave(copy, chemFumeHood)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            dataStore.ChemFumeHood.push($scope.modalData.ChemFumeHoodCopy);
            af.deleteModalData();
		}

	});
