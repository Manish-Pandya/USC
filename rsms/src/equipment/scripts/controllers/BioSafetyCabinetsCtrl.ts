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
        var getAll = function () {
            $rootScope.cabinets = new ViewModelHolder();
            $rootScope.Rooms = new ViewModelHolder();
            $scope.campuses = new ViewModelHolder();
            return $q.all([DataStoreManager.getAll("BioSafetyCabinet", $rootScope.cabinets, true), DataStoreManager.getAll("Campus", $scope.campuses, false), DataStoreManager.getAll("Room", $rootScope.Rooms, true)])
                .then(
                function (whateverGotReturned) {
                    getYears($rootScope.cabinets);
                    var actModCab = DataStoreManager.getActualModelEquivalent($rootScope.cabinets.data[1].EquipmentInspections[0]);
                    $rootScope.cabinets.data.sort((a, b) => { return a.EquipmentInspections[0].Room.Building.Name != b.EquipmentInspections[0].Room.Building.Name ? a.EquipmentInspections[0].Room.Building.Name > b.EquipmentInspections[0].Room.Building.Name : a.EquipmentInspections[0].Room.Name > b.EquipmentInspections[0].Room.Name; });

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
                var currentYearString = $rootScope.currentYearString = new Date().getFullYear().toString();
                var inspections = [];
                $scope.certYears = [];
                $rootScope.selectedCertificationDate = "";
                $rootScope.selectedDueDate = "";
                var inspections = [];
                cabs.data.forEach(function (c) {
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

        $scope.openModal = function (object, insp, isCabinet) {
            var modalData = { inspection: null };
            if (!object) {
                object = new equipment.BioSafetyCabinet();
                object.Is_active = true;
                object.Class = "BioSafetyCabinet";
                console.log(object);
            }
           
           //build new inspection object every time so we can assure we have a good one of proper type
            var inspection: equipment.EquipmentInspection;
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
            } else {
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
                        $rootScope.cabinets.data.forEach((c) => {
                            if (c.UID == r.UID)needsPush = false;
                        });
                        if (needsPush) $rootScope.cabinets.data.push(r);
                    }
                }
            });
        }

        $scope.openPiInfoModal = function (pi) {
            var modalData = {};
            modalData[pi.Class] = pi;
            modalData["piModal"] = true;
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

        $scope.testSave = function (i: equipment.EquipmentInspection) {
            DataStoreManager._actualModel["EquipmentInspection"].Data.forEach((i) => {
                i.viewModelWatcher["Certification_date"] = "2017-03-01 15:32:56";
            })
            i["Certification_date"] = "2017-01-01 15:32:56";
           
            $scope.saving = $q.all([DataStoreManager.save(i)]).then((c) => {
                console.log("server return: ", c);
                console.log("inspection from view as passed to save call: ", i);
                console.log("DataStoreManger._actualModel", DataStoreManager._actualModel);
            })
        }

        $scope.updateCertDate = function (date) {
            $rootScope.selectedCertificationDate = date;
        }

        $scope.getIsPreviousYear = function (uncert: boolean = null):boolean {
            if(uncert)return true
            return parseInt($rootScope.selectedCertificationDate) >= new Date().getFullYear();
        }

        $scope.getIsNextYear = function (): boolean {
            return parseInt($rootScope.selectedCertificationDate) == new Date().getFullYear() + 1;
        }

        $scope.openAttachtmentModal = function (object, insp) {
            let modalData = {};
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


        $scope.modalData = DataStoreManager.ModalData;
        $rootScope.modalClosed = false;

        $scope.getBuilding = function (id: string | number): void {      
            $rootScope.Buildings.data.forEach((b) => {
                if(b.UID == id)$scope.modalData.selectedBuilding = b;
            });
        }

        $scope.getRoom = function (id :string | number):void  {
            $rootScope.Rooms.data.forEach((r) => {
                if(r.UID == id){
                    $scope.modalData.selectedRoom = r;
                    console.log(r);
                    $scope.getBuilding(r.Building_id);
                }
            });
        }
        
        if (!$rootScope.Buildings) {
            $rootScope.Buildings = new ViewModelHolder();
            $rootScope.loading = $q.all([DataStoreManager.getAll("Building", $rootScope.Buildings, true)]).then((b)=>{
                if ($scope.modalData.BioSafetyCabinet.SelectedInspection && $scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id) {
                    $scope.getRoom($scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id);
                }
            });
        }else{
            if ($scope.modalData.BioSafetyCabinet.SelectedInspection && $scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id) {
                $scope.getRoom($scope.modalData.BioSafetyCabinet.SelectedInspection.Room_id);
            }
        }
            
        $scope.save = function (cabinet) {
            console.log(cabinet);
            if (!cabinet) return;
            $scope.error = false;
            cabinet.Certification_date = convenienceMethods.setMysqlTime(cabinet.Certification_date);
            var l = $rootScope.cabinets.data.length;
            for (let i = 0; i < l; i++) {
                var cab = $rootScope.cabinets.data[i];
                console.log(cab.Serial_number, cabinet.Serial_number);
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
            af.save(cabinet).then(function (r) { console.log(r[0]); $scope.close(r[0]) })
        }

        $scope.certify = function (inspection) {
            console.log(inspection);
            $scope.message = null;
            inspection.Certification_date = convenienceMethods.setMysqlTime(inspection.viewDate);
            inspection.Fail_date = convenienceMethods.setMysqlTime(inspection.viewFailDate);
            af.save(inspection).then(function (r) {
                // we added an equipmentInspection, so recompose the cabinet.
                //DataStoreManager.getById("BioSafetyCabinet", inspection.Equipment_id, {}, true);
                console.log(r);
                delete DataStoreManager._actualModel["PrincipalInvestigatorEquipmentInspection"];
                console.log(DataStoreManager._actualModel);
                $scope.close(r);
            })
        }

        $scope.close = function (r) {
            if (!r) {
                $modalInstance.dismiss();
                return;
            }
            $rootScope.modalClosed = true;
            $modalInstance.close(r);
            DataStoreManager.ModalData = null;
        }

        $scope.getMostRecentComment = function () {
            if ($scope.modalData.inspection && $scope.modalData.inspection.Comment) return $scope.modalData.inspection.Comment;
            var thing = $scope.modalData.BioSafetyCabinet.EquipmentInspections.filter(function (i) {
                return parseInt(moment(i.Certification_date).format("YYYY")) + 1 == parseInt($rootScope.selectedCertificationDate);
            })[0];
            if (thing) return thing.Comment || $scope.modalData.BioSafetyCabinet.Comment || "";
        }

        $scope.getRoomOptions = function (array) {
            array.push({ Name: "Unassigned", Key_id: null });
        }

        console.log($scope.modalData);
    })
    .controller('warningModalCtrl', function ($scope, $rootScope, cabinet, $modalInstance) {
        $scope.cabinet = cabinet;
        $scope.close = function () {
            $rootScope.modalClosed = true;
            $modalInstance.dismiss();
        }
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

        $scope.remove = function (inspection: equipment.EquipmentInspection, reportType) {
            inspection[reportType] = null;
            return $rootScope.saving = $q.all([DataStoreManager.save(inspection)]).then((i) => { console.log(i); return inspection; })
        }

        $scope.close = function () {
            $rootScope.modalClosed = true;
            $modalInstance.dismiss();
        };
    });