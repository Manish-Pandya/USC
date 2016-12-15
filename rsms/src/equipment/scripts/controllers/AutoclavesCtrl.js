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
    
      function getAllInspections() {
          $scope.inspections = [];
          $q.all([DataStoreManager.getAll("EquipmentInspection", $scope.inspections, false)])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.inspections);
                    console.log(DataStoreManager._actualModel);
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
        }

        function getAllAutoclaves() {
            $scope.autoclaves = [];
            $q.all([DataStoreManager.getAll("Autoclave", $scope.autoclaves, false)])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.autoclaves);
                    console.log(DataStoreManager._actualModel);
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
        }
        
        $rootScope.getCurrentRoles().then(getAllInspections()).then(getAllAutoclaves());

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
                controller: 'AutoclaveModalCtrl'
            });
        }

  })
  .controller('AutoclaveModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
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
