'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:X-RayCtrl
 * @description
 * # X-RayCtrl
 * Controller of the EquipmentModule X-Ray Machines view
 */
angular.module('EquipmentModule')
  .controller('X-RayCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods, $q) {
      var af = $scope.af = applicationControllerFactory;

      function getAll() {
            $scope.inspections = [];
            $scope.xrays = [];
            $q.all([DataStoreManager.getAll("EquipmentInspection", $scope.inspections, false), DataStoreManager.getAll("XRay", $scope.xrays, false)])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.inspections);
                    console.log($scope.xrays);
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
        }

      $scope.loading = $rootScope.getCurrentRoles().then(getAll);
    
        $scope.deactivate = function(xray) {
            xray.Retirement_date = new Date();
            af.save(xray);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.XRay();
                object.Class = "XRay";
            }
            modalData[object.Class] = object;
            DataStoreManager.ModalData = modalData;
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/xray-modal.html',
                controller: 'XRayModalCtrl'
            });
        }

  })
  .controller('XRayModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = DataStoreManager.ModalData;
        console.log($scope.modalData);
        $scope.save = function(xray) {
            af.save(xray)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
		}

	});
