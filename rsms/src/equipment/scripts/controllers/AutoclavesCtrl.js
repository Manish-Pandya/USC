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

        $rootScope.getCurrentRoles().then(getAll);

        $scope.deactivate = function(autoclave) {
            var copy = _.cloneDeep(autoclave); // TODO: Do we really need a clone? This should just be the viewModel, right?
            copy.Retirement_date = new Date();
            af.saveAutoclave(copy);
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
        $scope.save = function(copy) {
            af.saveAutoclave(copy)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            dataStore.Autoclave.push($scope.modalData.AutoclaveCopy);
            af.deleteModalData();
		}

	});
