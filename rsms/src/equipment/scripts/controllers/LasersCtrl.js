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

      $scope.loading = $rootScope.getCurrentRoles().then(getAll);
    
        $scope.deactivate = function(laser) {
            laser.Retirement_date = new Date();
            af.save(laser);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Laser();
                object.Class = "Laser";
            }
            modalData[object.Class] = object;
            DataStoreManager.ModalData = modalData;
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/laser-modal.html',
                controller: 'LaserModalCtrl'
            });
        }

  })
  .controller('LaserModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = DataStoreManager.ModalData;
        console.log($scope.modalData);
        $scope.save = function(laser) {
            af.save(laser)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
		}

	});
