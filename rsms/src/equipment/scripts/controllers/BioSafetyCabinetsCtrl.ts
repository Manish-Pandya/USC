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
            return $q.all([DataStoreManager.getAll("BioSafetyCabinet", $scope.cabinets, true), DataStoreManager.getAll("Campus", $scope.campuses, false)])
                .then(
                function (whateverGotReturned) {
                    getYears($scope.cabinets);
                    return true;
                }
                )
                .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
                )
        },
            getYears = function (cabs) {
                console.log(cabs);

                var currentYearString = $rootScope.currentYearString = new Date().getFullYear().toString();
                var inspections = [];
                $scope.certYears = [];
                $rootScope.selectedCertificationDate = "";
                $rootScope.selectedDueDate = "";
                var inspections = [];
                cabs.forEach(function (c) {
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

//init load
$scope.loading = $rootScope.getCurrentRoles().then(getAll);

$scope.deactivate = function (cabinet) {
    cabinet.Retirement_date = convenienceMethods.getUnixDate(new Date());
    cabinet.Is_active = !cabinet.Is_active;
    $scope.saving = af.save(cabinet);
}

$scope.openModal = function (object, inspection, isCabinet) {
    var modalData = { inspection: null };
    if (!object) {
        object = new equipment.BioSafetyCabinet();
        object.Is_active = true;
        object.Class = "BioSafetyCabinet";
    }
    if (isCabinet && !inspection) {
        inspection = new equipment.EquipmentInspection();
        inspection.Is_active = true;
        inspection.Class = "EquipmentInspection";
        inspection.Equipment_class = "BioSafetyCabinet";
        inspection.Equipment_id = object.Key_id || null;
        inspection.PrincipalInvestigators = inspection.PrincipalInvestigators || [];
        object.SelectedInspection = inspection;
    }

    modalData[object.Class] = object;
    modalData.inspection = inspection;
    DataStoreManager.ModalData = modalData;

    modalData["isCabinet"] = isCabinet;
    var modalInstance = $modal.open({
        templateUrl: isCabinet ? 'views/modals/bio-safety-cabinet-modal.html' : 'views/modals/bio-safety-cabinet-inspection-modal.html',
        controller: 'BioSafetyCabinetsModalCtrl'
    });

    modalInstance.result.then(function (r) {
        //bandaids for data binding after save
        if (!object.Key_id) {
            if (!Array.isArray(r)) {
                console.log(r);
                $scope.cabinets.push(r);
            }
        }
        console.log(DataStoreManager._actualModel);

        var test = DataStoreManager.getActualModelEquivalent(object.EquipmentInspections[0]);
        console.log(object,test)
        test.viewModelWatcher["test"] = "i SHOULD show up";

        test["test"] = "i shouldn't show up";

        object.doCompose([equipment.BioSafetyCabinet.EquipmentInspectionMap]);
    });
}

$scope.openPiInfoModal = function (pi) {
    var modalData = {};
    modalData[pi.Class] = pi;
    DataStoreManager.ModalData = modalData;
    var modalInstance = $modal.open({
        templateUrl: 'views/modals/pi-info-modal.html',
        controller: 'BioSafetyCabinetsModalCtrl'
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

$scope.$on('fileUpload', function (event, data) {
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
        if (xhr.readyState !== XMLHttpRequest.DONE || xhr.status !== 200) {
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
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, $q, applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
    var af = $scope.af = applicationControllerFactory;
    $scope.constants = Constants;

    $scope.modalData = DataStoreManager.ModalData;
    if ($scope.modalData.isCabinet) {
        $scope.PIs = [];
        $scope.loading = $q.all([DataStoreManager.getAll("PrincipalInvestigator", $scope.PIs, true)])
    }

    if ($scope.modalData.BioSafetyCabinet && $scope.modalData.BioSafetyCabinet.EquipmentInspections) {
        if ($scope.modalData.inspection.Room) {
            $scope.modalData.BioSafetyCabinet.Room = $scope.modalData.inspection.Room;
        }
        if ($scope.modalData.inspection.Frequency) {
            $scope.modalData.BioSafetyCabinet.Frequency = $scope.modalData.inspection.Frequency;
        }
        if ($scope.modalData.inspection.Report_path) {
            $scope.modalData.BioSafetyCabinet.Report_path = $scope.modalData.inspection.Report_path;
        }
        if ($scope.modalData.inspection.Equipment_id) {
            $scope.modalData.BioSafetyCabinet.Equipment_id = $scope.modalData.inspection.Equipment_id;
        }
        if ($scope.modalData.inspection.Certification_date) {
            $scope.modalData.BioSafetyCabinet.Certification_date = $scope.modalData.inspection.Certification_date;
        }
        //set date for calendar widget
        if ($scope.modalData.inspection.Certification_date) {
            $scope.modalData.BioSafetyCabinet.viewDate = new Date(convenienceMethods.getDate($scope.modalData.inspection.Certification_date));
        } else {
            $scope.modalData.BioSafetyCabinet.viewDate = new Date();
        }

        if ($rootScope.selectedCertificationDate) {
            if (!$scope.modalData.inspection) {
                $scope.modalData.BioSafetyCabinet.SelectedInspection = $scope.modalData.BioSafetyCabinet.EquipmentInspections.filter(function (i) {
                    return moment(i.Certification_date).format("YYYY") == $rootScope.selectedCertificationDate;
                })[0];
            } else {
                $scope.modalData.BioSafetyCabinet.SelectedInspection = $scope.modalData.inspection;
            }
        }
    }


    $scope.getBuilding = function () {
        if ($scope.modalData.BioSafetyCabinet.EquipmentInspections) {
            $scope.modalData.selectedBuilding = $scope.modalData.inspection.Room.Building.Name;
        } else {
            $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.Buildings[0];
        }

    }

    $scope.getRoom = function () {
        if ($scope.modalData.BioSafetyCabinet.RoomId) {

        } else {
            for (var i = 0; i < $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.Rooms.length; i++) {
                var room = $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.Rooms[i];
                if (room.Building.Name == $scope.modalData.selectedBuilding) {
                    $scope.modalData.BioSafetyCabinet.Room = room;
                    $scope.modalData.BioSafetyCabinet.RoomId = room.Key_id;
                }
            }
        }
    }

    $scope.onSelectBuilding = function () {
        $scope.roomFilter = $scope.modalData.SelectedBuilding;
        $scope.getRoom();
    }

    $scope.onSelectRoom = function () {
        $scope.modalData.BioSafetyCabinet.RoomId = $scope.modalData.BioSafetyCabinet.Room.Key_id;
    }

    $scope.$watch('modalData.BioSafetyCabinet.PrincipalInvestigator.Rooms', function () {
        if ($scope.modalData.BioSafetyCabinet.PrincipalInvestigator) {
            $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.loadBuildings();
        }
    });

    $scope.save = function (cabinet) {
        cabinet.Certification_date = convenienceMethods.setMysqlTime(cabinet.Certification_date);
        af.save(cabinet).then(function (r) { console.log(r);$scope.close(r) })
    }

    $scope.certify = function (inspection) {
        $scope.message = null;
        inspection.Certification_date = convenienceMethods.setMysqlTime(inspection.viewDate);
        inspection.Fail_date = convenienceMethods.setMysqlTime(inspection.viewFailDate);
        af.save(inspection).then(function (r) {
            // we added an equipmentInspection, so recompose the cabinet.
            //DataStoreManager.getById("BioSafetyCabinet", inspection.Equipment_id, {}, true);
            $scope.close(r);
        })
    }

    $scope.close = function (r) {
        $modalInstance.close(r || null);
        DataStoreManager.ModalData = null;
    }

    $scope.getMostRecentComment = function () {
        if ($scope.modalData.inspection && $scope.modalData.inspection.Comment) return $scope.modalData.inspection.Comment;
        var thing = $scope.modalData.BioSafetyCabinet.EquipmentInspections.filter(function (i) {
            return parseInt(moment(i.Certification_date).format("YYYY")) + 1 == parseInt($rootScope.selectedCertificationDate);
        })[0];
        if (thing) return thing.Comment || $scope.modalData.BioSafetyCabinet.Comment || "";
    }

    console.log($scope.modalData);
});
