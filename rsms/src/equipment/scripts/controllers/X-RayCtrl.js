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

        $rootScope.getCurrentRoles().then(getAll);
    
        $scope.deactivate = function(xray) {
            var copy = _.cloneDeep(xray); // TODO: Do we really need a clone? This should just be the viewModel, right?
            copy.Retirement_date = new Date();
            af.saveXRay(copy);
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
