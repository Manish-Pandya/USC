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
            .then(function (whateverGotReturned) {
            getYears();
            return true;
        })
            .catch(function (reason) {
            console.log("bad Promise.all:", reason);
        });
    }, getYears = function () {
        var currentYearString = $rootScope.currentYearString = new Date().getFullYear().toString();
        var inspections = [];
        $scope.certYears = [];
        $rootScope.selectedCertificationDate = "";
        $rootScope.selectedDueDate = "";
        DataStoreManager.getAll("EquipmentInspection", [], false).then(function (inspections) {
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
        });
        console.log($scope.cabinets);
    };
    //init load
    $scope.loading = $rootScope.getCurrentRoles().then(getAll);
    $scope.deactivate = function (cabinet) {
        cabinet.Retirement_date = convenienceMethods.getUnixDate(new Date());
        cabinet.Is_active = !cabinet.Is_active;
        $scope.saving = af.save(cabinet);
    };
    $scope.openModal = function (object, inspection, isCabinet) {
        var modalData = {};
        if (!object) {
            object = new equipment.BioSafetyCabinet();
            object.Is_active = true;
            object.Class = "BioSafetyCabinet";
        }
        if (isCabinet && !inspection) {
            var inspection = new equipment.EquipmentInspection();
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
        var modalInstance = $modal.open({
            templateUrl: isCabinet ? 'views/modals/bio-safety-cabinet-modal.html' : 'views/modals/bio-safety-cabinet-inspection-modal.html',
            controller: 'BioSafetyCabinetsModalCtrl'
        });
        modalInstance.result.then(function () {
            getAll();
        });
    };
    $scope.isOverdue = function (cab) {
        var d = new Date();
        d.setHours(0, 0, 0, 0);
        var todayInSeconds = d.getTime();
        for (var i = 0; i < cab.EquipmentInspections.length; i++) {
            var insp = cab.EquipmentInspections[i];
            var dueSeconds = convenienceMethods.getDate(insp.Due_date).getTime();
            if (dueSeconds < todayInSeconds)
                return true;
        }
        return false;
    };
    $scope.save = function (copy) {
        copy.Certification_date = convenienceMethods.setMysqlTime($scope.certDate);
        af.save(copy).then(function () { $scope.close(); });
    };
    $scope.certify = function (original) {
        original.Certification_date = convenienceMethods.setMysqlTime(original.viewDate);
        $scope.saving = af.save(original);
    };
    $scope.$on('fileUpload', function (event, data) {
        var formData = data.formData;
        data.clickTarget.Is_active = false;
        var insp = data.clickTarget;
        insp.reportUploaded = false;
        insp.reportUploading = true;
        $scope.$apply();
        var xhr = new XMLHttpRequest;
        var url = '../ajaxaction.php?action=' + data.path;
        if (insp.Key_id)
            url = url + "&id=" + insp.Key_id;
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
                }
                else {
                    insp.Quote_path = xhr.responseText.replace(/['"]+/g, '');
                }
                $scope.$apply();
            }
        };
    });
})
    .controller('BioSafetyCabinetsModalCtrl', function ($scope, $q, applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
    var af = $scope.af = applicationControllerFactory;
    $scope.constants = Constants;
    $scope.modalData = DataStoreManager.ModalData;
    $scope.PIs = [];
    $scope.loading = $q.all([DataStoreManager.getAll("PrincipalInvestigator", $scope.PIs, true)]);
    if ($scope.modalData.BioSafetyCabinet.EquipmentInspections) {
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
        }
        else {
            $scope.modalData.BioSafetyCabinet.viewDate = new Date();
        }
        if ($rootScope.selectedCertificationDate) {
            if (!$scope.modalData.inspection) {
                $scope.modalData.BioSafetyCabinet.SelectedInspection = $scope.modalData.BioSafetyCabinet.EquipmentInspections.filter(function (i) {
                    return moment(i.Certification_date).format("YYYY") == $rootScope.selectedCertificationDate;
                })[0];
            }
            else {
                $scope.modalData.BioSafetyCabinet.SelectedInspection = $scope.modalData.inspection;
            }
        }
    }
    $scope.getBuilding = function () {
        if ($scope.modalData.BioSafetyCabinet.EquipmentInspections) {
            $scope.modalData.selectedBuilding = $scope.modalData.inspection.Room.Building.Name;
        }
        else {
            $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.Buildings[0];
        }
    };
    $scope.getRoom = function () {
        if ($scope.modalData.BioSafetyCabinet.RoomId) {
        }
        else {
            for (var i = 0; i < $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.Rooms.length; i++) {
                var room = $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.Rooms[i];
                if (room.Building.Name == $scope.modalData.selectedBuilding) {
                    $scope.modalData.BioSafetyCabinet.Room = room;
                    $scope.modalData.BioSafetyCabinet.RoomId = room.Key_id;
                }
            }
        }
    };
    $scope.onSelectBuilding = function () {
        $scope.roomFilter = $scope.modalData.SelectedBuilding;
        $scope.getRoom();
    };
    $scope.onSelectRoom = function () {
        $scope.modalData.BioSafetyCabinet.RoomId = $scope.modalData.BioSafetyCabinet.Room.Key_id;
    };
    $scope.$watch('modalData.BioSafetyCabinet.PrincipalInvestigator.Rooms', function () {
        if ($scope.modalData.BioSafetyCabinet.PrincipalInvestigator) {
            $scope.modalData.BioSafetyCabinet.PrincipalInvestigator.loadBuildings();
        }
    });
    $scope.save = function (copy) {
        console.log(copy);
        copy.Certification_date = convenienceMethods.setMysqlTime(copy.Certification_date);
        af.save(copy).then(function () { $scope.close(); });
    };
    $scope.certify = function (copy) {
        $scope.message = null;
        copy.Certification_date = convenienceMethods.setMysqlTime(copy.viewDate);
        copy.Fail_date = convenienceMethods.setMysqlTime(copy.viewFailDate);
        af.save(copy).then(function (r) { console.log(r, DataStoreManager._actualModel.EquipmentInspection); $scope.close(); });
    };
    $scope.close = function () {
        $modalInstance.close();
        DataStoreManager.ModalData = null;
    };
    $scope.getMostRecentComment = function () {
        if ($scope.modalData.inspection && $scope.modalData.inspection.Comment)
            return $scope.modalData.inspection.Comment;
        var thing = $scope.modalData.BioSafetyCabinet.EquipmentInspections.filter(function (i) {
            return parseInt(moment(i.Certification_date).format("YYYY")) + 1 == parseInt($rootScope.selectedCertificationDate);
        })[0];
        console.log(thing);
        if (thing)
            return thing.Comment || $scope.modalData.BioSafetyCabinet.Comment || "";
    };
    console.log($scope.modalData);
});
