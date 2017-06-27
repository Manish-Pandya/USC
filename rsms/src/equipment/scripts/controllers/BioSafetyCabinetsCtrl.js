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
    $rootScope.modalClosed = true;
    $scope.convenienceMethods = convenienceMethods;
    $rootScope.filterStatuses = [
        { Data: "NEW", Label: "No Certification Record", uncertified: true, currentYear: false, previousYear: false },
        { Data: "PENDING", Label: "Due for Certification", uncertified: true, currentYear: false, previousYear: false },
        { Data: "OVERDUE", Label: "Overdue for Certification", uncertified: true, currentYear: false, previousYear: true },
        { Data: "FAIL", Label: "Failed Certification", uncertified: true, currentYear: true, previousYear: true },
        { Data: "PASS", Label: "Passed Certification", uncertified: false, currentYear: true, previousYear: true }
    ];
    var getAll = function () {
        $rootScope.cabinets = new ViewModelHolder();
        $rootScope.Rooms = new ViewModelHolder();
        $scope.campuses = new ViewModelHolder();
        return $q.all([DataStoreManager.getAll("BioSafetyCabinet", $rootScope.cabinets, true), DataStoreManager.getAll("Campus", $scope.campuses, false), DataStoreManager.getAll("Room", $rootScope.Rooms, true)])
            .then(function (whateverGotReturned) {
            getYears($rootScope.cabinets);
            console.log(DataStoreManager._actualModel);
            return true;
        })
            .catch(function (reason) {
            console.log("bad Promise.all:", reason);
        });
    }, getYears = function (cabs) {
        console.log(cabs);
        var currentYearString = $rootScope.currentYearString = new Date().getFullYear().toString();
        var inspections = [];
        $scope.certYears = [];
        $rootScope.selectedCertificationDate = "";
        $rootScope.selectedDueDate = "";
        var inspections = [];
        cabs.data.forEach(function (c) {
            if (c.EquipmentInspections)
                inspections = inspections.concat(c.EquipmentInspections);
        });
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
            $scope.currentYear = currentYearString;
            $rootScope.selectedDueDate = currentYearString;
        }
    };
    //init load
    $scope.loading = $rootScope.getCurrentRoles().then(getAll);
    $scope.deactivate = function (cabinet) {
        cabinet.Retirement_date = convenienceMethods.getUnixDate(new Date());
        cabinet.Is_active = !cabinet.Is_active;
        $scope.saving = af.save(cabinet);
    };
    $rootScope.getMostRecentComment = function (cabinet) {
        var previousInspection = cabinet.EquipmentInspections.sort(function (a, b) {
            return a["Date_created"] > b["Date_created"] ? 1 : 0;
        })[0];
        if (previousInspection && previousInspection["Comment"]) {
            cabinet["previousComment"] = true;
            var failed = previousInspection.Status == Constants.EQUIPMENT.STATUS.FAIL ? "<span class='red'> Failed</span> " : "";
            var date = previousInspection.Certification_date || previousInspection.Fail_date;
            var dateStr = moment(date).format("YYYY");
            return "<span class='modal-bold'>" + dateStr + failed + ' Comments:<br></span>' + previousInspection["Comment"];
        }
        ;
        cabinet["previousComment"] = false;
        return "";
    };
    $rootScope.failedMostRecentInspection = function (cabinet) {
        var previousInspection = cabinet.EquipmentInspections.filter(function (i) {
            return parseInt(moment(i.Certification_date).format("YYYY")) + 1 == parseInt($rootScope.selectedCertificationDate);
        })[0];
        if (previousInspection) {
            return previousInspection.Status == Constants.EQUIPMENT.STATUS.FAIL;
        }
        return false;
    };
    $scope.openModal = function (object, insp, isCabinet) {
        var modalData = { inspection: null };
        if (!object) {
            object = new equipment.BioSafetyCabinet();
            object.Is_active = true;
            object.Class = "BioSafetyCabinet";
            console.log(object);
        }
        //build new inspection object every time so we can assure we have a good one of proper type
        var inspection;
        if (!insp) {
            inspection = new equipment.EquipmentInspection();
            inspection['Is_active'] = true;
            inspection['Class'] = "EquipmentInspection";
            inspection.Equipment_class = "BioSafetyCabinet";
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
        }
        else {
            inspection = insp;
        }
        modalData[object.Class] = object;
        object.SelectedInspection = inspection;
        DataStoreManager.ModalData = modalData;
        modalData["isCabinet"] = isCabinet;
        var modalInstance = $modal.open({
            templateUrl: isCabinet ? 'views/modals/bio-safety-cabinet-modal.html' : 'views/modals/bio-safety-cabinet-inspection-modal.html',
            controller: 'BioSafetyCabinetsModalCtrl'
        });
        modalInstance.result.then(function (r) {
            if (!object.Key_id) {
                if (!Array.isArray(r)) {
                    console.log(r);
                    //$rootScope.cabinets.push(r);
                    var needsPush = true;
                    $rootScope.cabinets.data.forEach(function (c) {
                        if (c.UID == r.UID)
                            needsPush = false;
                    });
                    if (needsPush)
                        $rootScope.cabinets.data.push(r);
                }
            }
        });
    };
    $scope.openPiInfoModal = function (pi) {
        var modalData = {};
        modalData[pi.Class] = pi;
        DataStoreManager.ModalData = modalData;
        var modalInstance = $modal.open({
            templateUrl: 'views/modals/pi-info-modal.html',
            controller: 'BioSafetyCabinetsModalCtrl'
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
    $scope.testSave = function (i) {
        DataStoreManager._actualModel["EquipmentInspection"].Data.forEach(function (i) {
            i.viewModelWatcher["Certification_date"] = "2017-03-01 15:32:56";
        });
        i["Certification_date"] = "2017-01-01 15:32:56";
        $scope.saving = $q.all([DataStoreManager.save(i)]).then(function (c) {
            console.log("server return: ", c);
            console.log("inspection from view as passed to save call: ", i);
            console.log("DataStoreManger._actualModel", DataStoreManager._actualModel);
        });
    };
    $scope.updateCertDate = function (date) {
        $rootScope.selectedCertificationDate = date;
    };
    var d = new Date().getFullYear();
    $scope.getIsPreviousYear = function (uncert) {
        if (uncert === void 0) { uncert = null; }
        if (uncert)
            return false;
        return parseInt($rootScope.selectedCertificationDate) < d;
    };
    $scope.getIsCurrentYear = function () {
        return parseInt($rootScope.selectedCertificationDate) == d;
    };
    $scope.getIsNextYear = function () {
        return parseInt($rootScope.selectedCertificationDate) == d + 1;
    };
    $scope.openAttachtmentModal = function (object, insp) {
        var modalData = {};
        modalData[object.Class] = object;
        object.SelectedInspection = insp;
        DataStoreManager.ModalData = modalData;
        var modalInstance = $modal.open({
            templateUrl: 'views/modals/attachment-modal.html',
            controller: 'UploadModalCtrl'
        });
        modalInstance.result.then(function (r) {
            if (!object.Key_id) {
                if (!Array.isArray(r)) {
                    console.log(r);
                    //$rootScope.cabinets.push(r);
                    var needsPush = true;
                    $rootScope.cabinets.data.forEach(function (c) {
                        if (c.UID == r.UID)
                            needsPush = false;
                    });
                    if (needsPush)
                        $rootScope.cabinets.data.push(r);
                }
            }
        });
    };
})
    .controller('BioSafetyCabinetsModalCtrl', function ($scope, $q, $modal, applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
    var af = $scope.af = applicationControllerFactory;
    $scope.constants = Constants;
    $scope.convenienceMethods = convenienceMethods;
    $scope.modalData = DataStoreManager.ModalData;
    $rootScope.modalClosed = false;
    $scope.getBuilding = function (id) {
        $rootScope.Buildings.data.forEach(function (b) {
            if (b.UID == id)
                $scope.modalData.selectedBuilding = b;
        });
    };
    $scope.getRoom = function (id) {
        $rootScope.Rooms.data.forEach(function (r) {
            if (r.UID == id) {
                $scope.modalData.selectedRoom = r;
                console.log(r);
                $scope.getBuilding(r.Building_id);
            }
        });
    };
    if (!$rootScope.Buildings) {
        $rootScope.Buildings = new ViewModelHolder();
        $rootScope.loading = $q.all([DataStoreManager.getAll("Building", $rootScope.Buildings, true)]).then(function (b) {
            if ($scope.modalData.BioSafetyCabinet && $scope.modalData.BioSafetyCabinet.SelectedInspection && $scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id) {
                $scope.getRoom($scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id);
            }
        });
    }
    else {
        if ($scope.modalData.BioSafetyCabinet && $scope.modalData.BioSafetyCabinet.SelectedInspection && $scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id) {
            $scope.getRoom($scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id);
        }
    }
    $scope.save = function (cabinet) {
        console.log(cabinet);
        if (!cabinet)
            return;
        $scope.error = false;
        cabinet.Certification_date = convenienceMethods.setMysqlTime(cabinet.Certification_date);
        var l = $rootScope.cabinets.length;
        for (var i = 0; i < l; i++) {
            var cab = $rootScope.cabinets[i];
            if (cab.Serial_number == cabinet.Serial_number && (!cabinet.UID || cabinet.UID != cab.UID)) {
                var modalInstance = $modal.open({
                    templateUrl: 'views/modals/bsc-warning-modal.html',
                    controller: 'warningModalCtrl',
                    resolve: {
                        cabinet: function () {
                            return cab;
                        }
                    }
                });
                return;
            }
        }
        //clear the relationships between pis and inspections so the view reloads it
        //TODO:actually solve this, you, know?
        delete DataStoreManager._actualModel["PrincipalInvestigatorEquipmentInspection"];
        af.save(cabinet).then(function (r) {
            console.log(r[0]);
            $scope.close(r[0]);
        });
    };
    $scope.certify = function (inspection) {
        console.log(inspection);
        $scope.message = null;
        inspection.Certification_date = convenienceMethods.setMysqlTime(inspection.viewDate);
        inspection.Fail_date = convenienceMethods.setMysqlTime(inspection.viewFailDate);
        af.save(inspection).then(function (r) {
            // we added an equipmentInspection, so recompose the cabinet.
            DataStoreManager.getById("BioSafetyCabinet", inspection.Equipment_id, new ViewModelHolder(), true);
            console.log(r);
            delete DataStoreManager._actualModel["PrincipalInvestigatorEquipmentInspection"];
            console.log(DataStoreManager._actualModel);
            $scope.close(r);
        });
    };
    $scope.close = function (r) {
        if (!r) {
            $modalInstance.dismiss();
            return;
        }
        $rootScope.modalClosed = true;
        $modalInstance.close(r);
        DataStoreManager.ModalData = null;
    };
    $scope.getRoomOptions = function (array) {
        array.push({ Name: "Unassigned", Key_id: null });
    };
    console.log($scope.modalData);
})
    .controller('warningModalCtrl', function ($scope, $rootScope, cabinet, $modalInstance) {
    $scope.cabinet = cabinet;
    $scope.close = function () {
        $rootScope.modalClosed = true;
        $modalInstance.dismiss();
    };
})
    .controller('UploadModalCtrl', function ($scope, $rootScope, $modalInstance, $q) {
    $scope.modalData = DataStoreManager.ModalData;
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
    $scope.remove = function (inspection, reportType) {
        inspection[reportType] = null;
        return $rootScope.saving = $q.all([DataStoreManager.save(inspection)]).then(function (i) { console.log(i); return inspection; });
    };
    $scope.close = function () {
        $rootScope.modalClosed = true;
        $modalInstance.dismiss();
    };
});
