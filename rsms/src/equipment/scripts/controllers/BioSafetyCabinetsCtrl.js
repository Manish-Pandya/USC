'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:BioSafetyCabinetsCtrl
 * @description
 * # BioSafetyCabinetsCtrl
 * Controller of the EquipmentModule Biological Safety Cabinets view
 */
angular.module('EquipmentModule')
  .controller('BioSafetyCabinetsCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods, $q) {
      var af = $scope.af = applicationControllerFactory;
      $scope.constants = Constants;

      var getAll = function () {
          $scope.cabinets = [];
          $scope.campuses = [];
          var test = [];
          return $q.all([DataStoreManager.getAll("EquipmentInspection", [], true), DataStoreManager.getAll("PrincipalInvestigator", test, true), DataStoreManager.getAll("BioSafetyCabinet", $scope.cabinets, true), DataStoreManager.getAll("Campus", $scope.campuses, false)])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.cabinets);
                    test.forEach(function (p) {
                        console.log(p.Rooms)
                    })
                    getYears();
                    return true;
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
      },
        getYears = function () {
            var currentYearString = $rootScope.currentYearString = new Date().getFullYear().toString();
            var inspections = [];
            $scope.certYears = [];
            $rootScope.selectedCertificationDate = "";
            $rootScope.selectedDueDate = "";

            DataStoreManager.getAll("EquipmentInspection", [], false).then(function (inspections) {
                console.log(inspections);
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
                console.log($rootScope.selectedCertificationDate);
                $scope.$apply();
            })

        }

      //init load
      $scope.loading = $rootScope.getCurrentRoles().then(getAll);
      /*$scope.loading = new Promise((resolve, reject) => {
          setTimeout(() => {
              $scope.$apply();
              resolve(true);
          }, 100);
      });*/

      $scope.deactivate = function (cabinet) {
          var copy = dataStoreManager.createCopy(cabinet);
          copy.Retirement_date = convenienceMethods.getUnixDate(new Date());
          copy.Is_active = !copy.Is_active;
          $scope.Saving = af.saveBioSafetyCabinet(copy, cabinet);
      }

      $scope.openModal = function (object, inspection, isCabinet) {
          var modalData = {};
          if (!object) {
              object = new window.BioSafetyCabinet();
              object.Is_active = true;
              object.Class = "BioSafetyCabinet";
          }
          if (isCabinet && !inspection) {
              var inspection = new EquipmentInspection();
              inspection.Is_active = true;
              inspection.Class = "EquipmentInspection";
              inspection.Equipment_class = "BioSafetyCabinet";
              inspection.Equipment_id = object.Key_id || null;
          }

          if (object.PrincipalInvestigator) object.PrincipalInvestigator.loadRooms();
          modalData[object.Class] = object;
          modalData.inspection = inspection;
          console.log(inspection);
          af.setModalData(modalData);
          var modalInstance = $modal.open({
              templateUrl: isCabinet ? 'views/modals/bio-safety-cabinet-modal.html' : 'views/modals/bio-safety-cabinet-inspection-modal.html',
              controller: 'BioSafetyCabinetsModalCtrl'
          });

          modalInstance.result.then(function () {
              getAllBioSafetyCabinets();
          });
      }

      $scope.isOverdue = function (cab) {
          var d = new Date();
          d.setHours(0, 0, 0, 0);
          var todayInSeconds = d.getTime();
          for (var i = 0; i < cab.EquipmentInspections.length; i++) {
              var insp = cab.EquipmentInspections[i];
              var dueSeconds = convenienceMethods.getDate(insp.Due_date).getTime();
              if (dueSeconds < todayInSeconds) return true;
          }
          return false;
      }

      $scope.save = function (copy, original) {
          if (!original) original = null;
          copy.Certification_date = convenienceMethods.setMysqlTime($scope.certDate);
          af.saveBioSafetyCabinet(copy, original)
                  .then(function () { $scope.close() })
      }

      $scope.certify = function (original) {
          var copy = dataStoreManager.createCopy(original);
          copy.Certification_date = convenienceMethods.setMysqlTime(copy.viewDate);
          $scope.Saving = af.saveEquipmentInspection(copy, original)
      }

      $scope.$on('fileUpload', function (event, data) {
          console.log(data);
          var formData = data.formData;
          data.clickTarget.Is_active = false;
          var insp = data.clickTarget;
          insp.reportUploaded = false;
          insp.reportUploading = true;
          $scope.$apply();

          var xhr = new XMLHttpRequest;
          var url = '../ajaxaction.php?action=' + data.path;
          if (insp.Key_id) url = url + "&id=" + insp.Key_id;
          xhr.open('POST', url, true);
          xhr.send(formData);
          xhr.onreadystatechange = function () {
              if (xhr.readyState !== XMLHttpRequest.DONE) {
                  return;
              }
              if (xhr.status !== 200) {
                  return;
              }
              if (xhr.status == 200) {
                  insp.reportUploaded = true;
                  insp.reportUploading = false;
                  if (data.path.toLowerCase().indexOf("quote") == -1) {
                      insp.Report_path = xhr.responseText.replace(/['"]+/g, '');
                  } else {
                      insp.Quote_path = xhr.responseText.replace(/['"]+/g, '');
                  }

                  $scope.$apply();
              }
          }
      });

  })
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, $q,applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
      var af = $scope.af = applicationControllerFactory;
      $scope.constants = Constants;

      $scope.modalData = af.getModalData();
      $scope.PIs = [];
      $scope.loading = $q.all([DataStoreManager.getAll("PrincipalInvestigator",$scope.PIs,true)]);


      if ($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections) {


          if ($scope.modalData.inspection.Room) {
              $scope.modalData.BioSafetyCabinetCopy.Room = $scope.modalData.inspection.Room;
          }
          if ($scope.modalData.inspection.Frequency) {
              $scope.modalData.BioSafetyCabinetCopy.Frequency = $scope.modalData.inspection.Frequency;
          }
          if ($scope.modalData.inspection.Report_path) {
              $scope.modalData.BioSafetyCabinetCopy.Report_path = $scope.modalData.inspection.Report_path;
          }
          if ($scope.modalData.inspection.Equipment_id) {
              $scope.modalData.BioSafetyCabinetCopy.Equipment_id = $scope.modalData.inspection.Equipment_id;
          }
          if ($scope.modalData.inspection.Certification_date) {
              $scope.modalData.BioSafetyCabinetCopy.Certification_date = $scope.modalData.inspection.Certification_date;
          }
          //set date for calendar widget
          if ($scope.modalData.inspection.Certification_date) {
              $scope.modalData.BioSafetyCabinetCopy.viewDate = new Date(convenienceMethods.getDate($scope.modalData.inspection.Certification_date));
          } else {
              $scope.modalData.BioSafetyCabinetCopy.viewDate = new Date();
          }

          if ($rootScope.selectedCertificationDate) {
              if (!$scope.modalData.inspectionCopy) {
                  $scope.modalData.BioSafetyCabinetCopy.SelectedInspection = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections.filter(function (i) {
                      return moment(i.Certification_date).format("YYYY") == $rootScope.selectedCertificationDate;
                  })[0];
              } else {
                  $scope.modalData.BioSafetyCabinetCopy.SelectedInspection = $scope.modalData.inspectionCopy;
              }
              if ($scope.modalData.BioSafetyCabinetCopy.SelectedInspection.PrincipalInvestigators) {
                  $scope.modalData.BioSafetyCabinetCopy.SelectedInspection.PrincipalInvestigators.forEach(function (pi) {
                      console.log(pi);
                      pi.loadRooms();
                  })
              }
          }
      }


      $scope.getBuilding = function () {
          if ($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections) {
              $scope.modalData.selectedBuilding = $scope.modalData.inspection.Room.Building.Name;
          } else {
              $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Buildings[0];
          }

      }

      $scope.getRoom = function () {
          if ($scope.modalData.BioSafetyCabinetCopy.RoomId) {

          } else {
              for (var i = 0; i < $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms.length; i++) {
                  var room = $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms[i];
                  if (room.Building.Name == $scope.modalData.selectedBuilding) {
                      $scope.modalData.BioSafetyCabinetCopy.Room = room;
                      $scope.modalData.BioSafetyCabinetCopy.RoomId = room.Key_id;
                  }
              }
          }
      }

      $scope.onSelectBuilding = function () {
          $scope.roomFilter = $scope.modalData.SelectedBuilding;
          $scope.getRoom();
      }

      $scope.onSelectRoom = function () {
          $scope.modalData.BioSafetyCabinetCopy.RoomId = $scope.modalData.BioSafetyCabinetCopy.Room.Key_id;
      }

      $scope.$watch('modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms', function () {
          if ($scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator) {
              $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.loadBuildings();
          }
      });

      $scope.save = function (copy, original) {
          console.log(copy);
          if (!original) original = null;
          copy.Certification_date = convenienceMethods.setMysqlTime(copy.Certification_date);
          af.saveBioSafetyCabinet(copy, original)
                  .then(function () { $scope.close() })
      }

      $scope.certify = function (copy, original) {

          $scope.message = null;

          if (!original) original = null;
          copy.Certification_date = convenienceMethods.setMysqlTime(copy.viewDate);
          copy.Fail_date = convenienceMethods.setMysqlTime(copy.viewFailDate);

          af.saveEquipmentInspection(copy, original)
                  .then(function () { $scope.close() })
      }

      $scope.close = function () {
          $modalInstance.close();
          af.deleteModalData();
      }

      $scope.getMostRecentComment = function () {
          if ($scope.modalData.inspectionCopy && $scope.modalData.inspectionCopy.Comment) return $scope.modalData.inspectionCopy.Comment;
          var thing = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections.filter(function (i) {
              return parseInt(moment(i.Certification_date).format("YYYY")) + 1 == parseInt($rootScope.selectedCertificationDate);
          })[0];
          console.log(thing);
          if (thing) return thing.Comment || $scope.modalData.BioSafetyCabinetCopy.Comment || "";

      }

      console.log($scope.modalData);

  });
