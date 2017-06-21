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
      $scope.constants = Constants;
      $rootScope.modalClosed = true;
      $scope.convenienceMethods = convenienceMethods;
      
      function getAll() {
          $scope.hoods = new ViewModelHolder();
          $scope.rooms = new ViewModelHolder();
          $scope.campuses = new ViewModelHolder();
          $q.all([DataStoreManager.getAll("ChemFumeHood", $scope.hoods, true), DataStoreManager.getAll("Campus", $scope.campuses, false), DataStoreManager.getAll("Room", $scope.rooms, true)])
          .then(
              function (whateverGotReturned) {
                  getYears($scope.hoods);
                  console.log($scope.hoods);
                  console.log(DataStoreManager._actualModel);
              }
          )
          .catch(
              function (reason) {
                  console.log("bad Promise.all:", reason);
              }
          )
      }

      function getYears(hoods) {
          console.log(hoods);
          var currentYearString = $rootScope.currentYearString = new Date().getFullYear().toString();
          var inspections = [];
          $scope.certYears = [];
          $rootScope.selectedCertificationDate = "";
          $rootScope.selectedDueDate = "";
          var inspections = [];
          hoods.data.forEach(function (c) {
              if (c.EquipmentInspections) inspections = inspections.concat(c.EquipmentInspections);
          })
          if (inspections) {
              var i = inspections.length;
              while (i--) {
                  if (inspections[i].Equipment_class == Constants.BIOSAFETY_CABINET.EQUIPMENT_CLASS) {
                      if (inspections[i].Certification_date) {
                          var certYear = inspections[i].Certification_date.split('-')[0];
                          if ($scope.certYears.indexOf(certYear) == -1) {
                              $scope.certYears.push(certYear);
                          }
                      }
                      if (inspections[i].Due_date) {
                          var dueYear = inspections[i].Due_date.split('-')[0];
                          if ($scope.certYears.indexOf(dueYear) == -1) {
                              $scope.certYears.push(dueYear);
                          }
                      }

                  }
              }


              if ($scope.certYears.indexOf(currentYearString) < 0) {
                  $scope.certYears.push(currentYearString);
              }
              $rootScope.selectedCertificationDate = currentYearString;
              $rootScope.selectedDueDate = currentYearString;
          }

      }

      $scope.loading = $rootScope.getCurrentRoles().then(getAll);
    
      $scope.deactivate = function (chemFumeHood) {
            chemFumeHood.Retirement_date = new Date();
            chemFumeHood.Is_active = !chemFumeHood.Is_active;
            af.save(chemFumeHood);
      }
    
      $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new equipment.ChemFumeHood();
                object.Is_active = true;
                object.Class = "ChemFumeHood";
            }
            modalData[object.Class] = object;
            DataStoreManager.ModalData = modalData;
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/chem-fume-hood-modal.html',
                controller: 'ChemFumeHoodModalCtrl'
            });
        }

  })
  .controller('ChemFumeHoodModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = $scope.af = actionFunctionsFactory;

		$scope.modalData = DataStoreManager.ModalData;
        console.log($scope.modalData);
        $scope.save = function (chemFumeHood) {
            af.save(chemFumeHood)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
		}

	});
