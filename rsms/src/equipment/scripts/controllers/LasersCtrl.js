'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:LaserCtrl
 * @description
 * # LaserCtrl
 * Controller of the EquipmentModule Lasers view
 */
angular.module('EquipmentModule')
  .controller('LasersCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods, $q) {
      var af = $scope.af = applicationControllerFactory;

      function getAll() {
          $scope.inspections = [];
          $scope.lasers = [];
          $q.all([DataStoreManager.getAll("EquipmentInspection", $scope.inspections, false), DataStoreManager.getAll("Laser", $scope.lasers, false)])
          .then(
              function (whateverGotReturned) {
                  console.log($scope.inspections);
                  console.log($scope.lasers);
              }
          )
          .catch(
              function (reason) {
                  console.log("bad Promise.all:", reason);
              }
          )
      }

      $rootScope.getCurrentRoles().then(getAll);
    
        $scope.deactivate = function(laser) {
            var copy = _.cloneDeep(laser); // TODO: Do we really need a clone? This should just be the viewModel, right?
            copy.Retirement_date = new Date();
            af.saveLaser(copy);
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
