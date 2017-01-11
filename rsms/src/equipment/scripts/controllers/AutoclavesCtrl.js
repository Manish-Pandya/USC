'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:AutoclavesCtrl
 * @description
 * # AutoclavesCtrl
 * Controller of the EquipmentModule Autoclaves view
 */
angular.module('EquipmentModule')
  .controller('AutoclavesCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods, $q) {
      var af = $scope.af = applicationControllerFactory;
    
      function getAll() {
            $scope.inspections = [];
            $scope.autoclaves = [];
            $q.all([DataStoreManager.getAll("EquipmentInspection", $scope.inspections, false), DataStoreManager.getAll("Autoclave", $scope.autoclaves, false)])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.inspections);
                    console.log($scope.autoclaves);
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getAll);

        $scope.deactivate = function(autoclave) {
            autoclave.Retirement_date = new Date();
            af.save(autoclave);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new Autoclave();
                object.Class = "Autoclave";
            }
            modalData[object.Class] = object;
            DataStoreManager.ModalData = modalData;
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/autoclave-modal.html',
                controller: 'AutoclaveModalCtrl'
            });
        }

  })
  .controller('AutoclaveModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = DataStoreManager.ModalData;
        console.log($scope.modalData);
        $scope.save = function(copy) {
            af.save(copy)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
		}

	});
