'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:AutoclavesCtrl
 * @description
 * # AutoclavesCtrl
 * Controller of the EquipmentModule PI waste Pickups view
 */
angular.module('EquipmentModule')
  .controller('LasersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		$scope.af = af;
    
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
		var af = actionFunctionsFactory;
		$scope.af = af;

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
