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

      $scope.updateCertDate = function (date) {
          $rootScope.selectedCertificationDate = date;
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

  })
    .controller('ChemFumeHoodModalCtrl', function ($scope, $q, $modal, applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
        var af = $scope.af = applicationControllerFactory;
        $scope.constants = Constants;

        $scope.modalData = DataStoreManager.ModalData;

        $scope.getBuilding = function (id: string | number): void {
            $rootScope.Buildings.data.forEach((b) => {
                if (b.UID == id) $scope.modalData.selectedBuilding = b;
            });
        }

        $scope.getRoom = function (id: string | number): void {
            $rootScope.Rooms.data.forEach((r) => {
                if (r.UID == id) {
                    $scope.modalData.selectedRoom = r;
                    console.log(r);
                    $scope.getBuilding(r.Building_id);
                }
            });
        }

        if (!$rootScope.Buildings) {
            $rootScope.Buildings = new ViewModelHolder();
            $rootScope.loading = $q.all([DataStoreManager.getAll("Building", $rootScope.Buildings, true)]).then((b) => {
                if ($scope.modalData.ChemFumeHood && $scope.modalData.ChemFumeHood.SelectedInspection && $scope.modalData.ChemFumeHood.SelectedInspection.Room_id) {
                    $scope.getRoom($scope.modalData.ChemFumeHood.SelectedInspection.Room_id);
                }
            });
        } else {
            if ($scope.modalData.ChemFumeHood && $scope.modalData.ChemFumeHood.SelectedInspection && $scope.modalData.ChemFumeHood.SelectedInspection.Room_id) {
                $scope.getRoom($scope.modalData.ChemFumeHood.SelectedInspection.Room_id);
            }
        }
        
        $scope.save = function (hood) {
            if (!hood) return;
            hood.Certification_date = convenienceMethods.setMysqlTime(hood.Certification_date);
            //clear the relationships between pis and inspections so the view reloads it
            //TODO:actually solve this, you, know?
            delete DataStoreManager._actualModel["PrincipalInvestigatorEquipmentInspection"];
            af.save(hood).then(function (r) {
                console.log(r[0]);
                $scope.close(r[0]);
            })
        }

        $scope.certify = function (inspection) {
            console.log(inspection);
            $scope.message = null;
            inspection.Certification_date = convenienceMethods.setMysqlTime(inspection.viewDate);
            inspection.Fail_date = convenienceMethods.setMysqlTime(inspection.viewFailDate);
            af.save(inspection).then(function (r) {
                // we added an equipmentInspection, so recompose the cabinet.
                DataStoreManager.getById("ChemFumeHood", inspection.Equipment_id, new ViewModelHolder(), true);
                console.log(r);
                delete DataStoreManager._actualModel["PrincipalInvestigatorEquipmentInspection"];
                console.log(DataStoreManager._actualModel);
                $scope.close(r);
            })
        }

		$scope.close = function(){
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
		}

	});
