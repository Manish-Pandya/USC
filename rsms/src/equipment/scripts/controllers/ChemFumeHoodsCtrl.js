'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:ChemFumeHoodsCtrl
 * @description
 * # ChemFumeHoodsCtrl
 * Controller of the EquipmentModule Chemical Fume Hoods view
 */
angular.module('EquipmentModule')
  .controller('ChemFumeHoodsCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods, $q) {
      var af = $scope.af = applicationControllerFactory;

      $scope.hoods = [];
      function getAll() {
          $scope.inspections = [];
          $scope.hoods = [];
          $q.all([DataStoreManager.getAll("EquipmentInspection", $scope.inspections, false), DataStoreManager.getAll("ChemFumeHood", $scope.hoods, false)])
          .then(
              function (whateverGotReturned) {
                  console.log($scope.inspections);
                  console.log($scope.hoods);
              }
          )
          .catch(
              function (reason) {
                  console.log("bad Promise.all:", reason);
              }
          )
      }

      $scope.loading = $rootScope.getCurrentRoles().then(getAll);
    
        $scope.deactivate = function(hood) {
            hood.Retirement_date = new Date();
            af.saveChemFumeHood(hood);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.ChemFumeHood();
                object.Class = "ChemFumeHood";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/chem-fume-hood-modal.html',
                controller: 'ChemFumeHoodModalCtrl'
            });
        }

  })
  .controller('ChemFumeHoodModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(copy, chemFumeHood) {
            af.saveAutoclave(copy, chemFumeHood)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            dataStore.ChemFumeHood.push($scope.modalData.ChemFumeHoodCopy);
            af.deleteModalData();
		}

	});
