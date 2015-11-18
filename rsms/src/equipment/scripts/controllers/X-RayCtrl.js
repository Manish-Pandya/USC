'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:AutoclavesCtrl
 * @description
 * # AutoclavesCtrl
 * Controller of the EquipmentModule X-Ray Machines view
 */
angular.module('EquipmentModule')
  .controller('X-RayCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		$scope.af = af;
    
        $scope.xrays = [];
    
        $scope.deactivate = function(cabinet) {
            
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
  .controller('AutoclavesModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;

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
