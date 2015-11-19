'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:LaserCtrl
 * @description
 * # LaserCtrl
 * Controller of the EquipmentModule Lasers view
 */
angular.module('EquipmentModule')
  .controller('LasersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = $scope.af = actionFunctionsFactory;

        $scope.lasers = [];
    
        $scope.deactivate = function(laser) {
            var copy = dataStoreManager.createCopy(laser);
            copy.Retirement_date = new Date();
            af.saveLaser(laser.pi, copy, laser);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Laser();
                object.Class = "Laser";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/laser-modal.html',
                controller: 'LaserModalCtrl'
            });
        }

  })
  .controller('LaserModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(copy, laser) {
            af.saveAutoclave(copy, laser)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            dataStore.Laser.push($scope.modalData.LaserCopy);
            af.deleteModalData();
		}

	});
