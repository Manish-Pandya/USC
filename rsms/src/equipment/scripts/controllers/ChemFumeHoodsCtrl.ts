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
                  if (inspections[i].Equipment_class == Constants.CHEM_FUME_HOOD.EQUIPMENT_CLASS) {
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
    
      $scope.openModal = function (object, insp, isHood) {
          var modalData = { inspection: null };
          if (!object) {
              object = new equipment.ChemFumeHood();
              object.Is_active = true;
              object.Class = "ChemFumeHood";
              console.log(object);
          }

          //build new inspection object every time so we can assure we have a good one of proper type
          var inspection: equipment.EquipmentInspection;
          if (!insp) {
              inspection = new equipment.EquipmentInspection();
              inspection['Is_active'] = true;
              inspection['Class'] = "EquipmentInspection";
              inspection.Equipment_class = "ChemFumeHood";
              inspection.Equipment_id = object.Key_id || null;
              inspection['Key_id'] = insp ? insp.Key_id : null;
              inspection.Comments = insp ? insp.Comments : null;
              inspection.Frequency = insp ? insp.Frequency : null;
              inspection.Room_id = insp ? insp.Room_id : null;
              inspection.Certification_date = insp ? insp.Certification_date : null;
              inspection.Due_date = insp ? insp.Due_date : null;
              inspection.Status = insp ? insp.Status : null;
              inspection.UID = insp ? insp.Key_id : null;
              inspection.PrincipalInvestigators = insp ? insp.PrincipalInvestigators : [];
          } else {
              inspection = insp;
          }

          modalData[object.Class] = object;
          object.SelectedInspection = inspection;
          DataStoreManager.ModalData = modalData;

          modalData["isHood"] = isHood;
          var modalInstance = $modal.open({
              templateUrl: isHood ? 'views/modals/chem-fume-hood-modal.html' : 'views/modals/chem-fume-hood-inspection-modal.html',
              controller: 'ChemFumeHoodModalCtrl'
          });

          modalInstance.result.then(function (r) {
              if (!object.Key_id) {
                  if (!Array.isArray(r)) {
                      console.log(r);
                      var needsPush = true;
                      $scope.hoods.data.forEach((c) => {
                          if (c.UID == r.UID) needsPush = false;
                      });
                      if (needsPush) $scope.hoods.data.push(r);
                  }
              }
          });
      }

      $scope.updateCertDate = function (date) {
          $rootScope.selectedCertificationDate = date;
      }

  })
    .controller('ChemFumeHoodModalCtrl', function ($scope, $q, $modal, applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
        var af = $scope.af = applicationControllerFactory;

        $scope.modalData = DataStoreManager.ModalData;
        $rootScope.modalClosed = false;
        
        $scope.save = function (chemFumeHood) {
            af.save(chemFumeHood)
                .then($scope.close);
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
		}

	});
