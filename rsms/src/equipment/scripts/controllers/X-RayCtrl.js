'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:X-RayCtrl
 * @description
 * # X-RayCtrl
 * Controller of the EquipmentModule X-Ray Machines view
 */
angular.module('EquipmentModule')
  .controller('X-RayCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
      var af = $scope.af = applicationControllerFactory;

        $scope.xrays = [];
    
        $scope.deactivate = function(xray) {
            var copy = dataStoreManager.createCopy(xray);
            copy.Retirement_date = new Date();
            af.saveXRay(xray.pi, copy, xray);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.XRay();
                object.Class = "XRay";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/xray-modal.html',
                controller: 'XRayModalCtrl'
            });
        }

  })
  .controller('XRayModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(copy, xray) {
            af.saveAutoclave(copy, xray)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            dataStore.XRay.push($scope.modalData.XRayCopy);
            af.deleteModalData();
		}

	});
