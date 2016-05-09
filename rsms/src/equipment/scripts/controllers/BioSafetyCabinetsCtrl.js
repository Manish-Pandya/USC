'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:BioSafetyCabinetsCtrl
 * @description
 * # BioSafetyCabinetsCtrl
 * Controller of the EquipmentModule Biological Safety Cabinets view
 */
angular.module('EquipmentModule')
    .directive('fileUpload', function (applicationControllerFactory) {
        return {
            restrict: 'A',
            scope: true,
            link: function (scope, element, attr) {
                element.bind('change', function () {
                    var formData = new FormData();
                    formData.append('file', element[0].files[0]);
                    element.blur();
                    $("label[for='"+element.attr('id')+"']").blur();
                    scope.$emit("fileUpload",formData);
                    return;
                });

            }
        };
    })
  .controller('BioSafetyCabinetsCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
        var af = $scope.af = applicationControllerFactory;    
        var getAllInspections = function(){
            return af.getAllEquipmentInspections().then(
                function(){
                    var inspections = dataStoreManager.get("EquipmentInspection");
                    getYears();

                    return inspections;
                }
            );
        },
        getAllBioSafetyCabinets = function(){
            return af.getAllBioSafetyCabinets()
                .then(
                    function () {
                        $scope.cabinets = dataStoreManager.get("BioSafetyCabinet");
                        if (!$scope.cabinets) $scope.cabinets = [];
                        return $scope.cabinets;
                    }
                )
              
        },
        getAllPis = function(){
            return af.getAllPrincipalInvestigators()
                        .then(function(){$scope.pis = dataStoreManager.get("PrincipalInvestigator");return $scope.pis;})
        },
        getAllRooms = function(){
            return af.getAllRooms().then(
                        function(){
                                $scope.rooms = dataStoreManager.get("Room");
                                return $scope.rooms
                            }
                        );
        },
        getAllBuildings = function(){
            return af.getAllBuildings()
                        .then(
                            function(){
                                $scope.buildings = dataStoreManager.get("Building");
                                return $scope.buildings
                            }
                        );
        },
        getYears = function () {
            var inspections = dataStoreManager.get("EquipmentInspection");
            $scope.certYears = [];
            $scope.dueYears = [];
            if (!inspections) return;
            var i = inspections.length;

            while (i--) {
                if (inspections[i].Equipment_class == Constants.BIOSAFETY_CABINET.EQUIPMENT_CLASS) {
                    if (inspections[i].Certification_date) {
                        var certYear = inspections[i].Certification_date.split('-')[0];
                        if ($scope.certYears.indexOf(certYear) == -1) {
                            $scope.certYears.push(certYear);
                        }
                    }
                    if (inspections[i].Due_date && !inspections[i].Certification_date) {
                        var dueYear = inspections[i].Due_date.split('-')[0];
                        if ($scope.dueYears.indexOf(dueYear) == -1) {
                            $scope.dueYears.push(dueYear);
                        }
                    }

                    var currentYearString = new Date().getFullYear().toString();
                    if ($scope.dueYears.indexOf(currentYearString) < 0) {
                        $scope.dueYears.push(currentYearString);
                    }
                    if ($scope.certYears.indexOf(currentYearString) < 0) {
                        $scope.certYears.push(currentYearString);
                    }
                    $scope.selectedCertificationDate = currentYearString;
                    $scope.selectedDueDate = currentYearString;
                    console.log(currentYearString);
                }
            }
        }
        
        //init load
        $scope.loading = getAllRooms()
                            .then(getAllPis())
                            .then(getAllInspections())
                            .then(getAllBioSafetyCabinets());

        $scope.deactivate = function(cabinet) {
            var copy = dataStoreManager.createCopy(cabinet);
            copy.Retirement_date = convenienceMethods.getUnixDate(new Date());
            af.saveBioSafetyCabinet(cabinet.pi, copy, cabinet);
        }
        
        $scope.openModal = function(object, inspectionIndex) {
            var modalData = {};
            if (!object) {
                object = new window.BioSafetyCabinet();
                object.Is_active = true;
                object.Class = "BioSafetyCabinet";
            }
            if(object.PrincipalInvestigator)object.PrincipalInvestigator.loadRooms();
            modalData[object.Class] = object;
            modalData.inspectionIndex = inspectionIndex ? inspectionIndex : 0;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: isNaN(inspectionIndex) ? 'views/modals/bio-safety-cabinet-modal.html' : 'views/modals/bio-safety-cabinet-inspection-modal.html',
                controller: 'BioSafetyCabinetsModalCtrl'
            });

            modalInstance.result.then(function () {
                getAllBioSafetyCabinets();
            });
        }

        $scope.isOverdue = function (cab) {
            var d = new Date();
            d.setHours(0,0,0,0);
            var todayInSeconds = d.getTime();
            for (var i = 0; i < cab.EquipmentInspections.length; i++) {
                var insp = cab.EquipmentInspections[i];
                var dueSeconds = convenienceMethods.getDate(insp.Due_date).getTime();
                if (dueSeconds < todayInSeconds) return true;
            }
            return false;
        }

  })
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
        var af = $scope.af = applicationControllerFactory;
        
        $scope.modalData = af.getModalData();
        $scope.PIs = dataStoreManager.get("PrincipalInvestigator");
    
        $scope.onSelectPi = function(pi){
            pi.loadRooms();
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator = pi;
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigatorId = pi.Key_id;
        }
        
        if ($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections) {
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].PrincipalInvestigator){
                $scope.pi = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].PrincipalInvestigator;
                $scope.pi.selected = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].PrincipalInvestigator;
                $scope.onSelectPi($scope.pi);
            }
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Room){
                $scope.modalData.BioSafetyCabinetCopy.Room = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Room;
            }
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Frequency){
                $scope.modalData.BioSafetyCabinetCopy.Frequency = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Frequency;
            }
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Report_path){
                $scope.modalData.BioSafetyCabinetCopy.Report_path = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Report_path;
            }
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Equipment_id){
                $scope.modalData.BioSafetyCabinetCopy.Equipment_id = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Equipment_id;
            }
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Certification_date){
                $scope.modalData.BioSafetyCabinetCopy.Certification_date = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Certification_date;
            }

            $scope.modalData.BioSafetyCabinetCopy.viewDate = new Date($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Certification_date || null);
            console.log($scope.modalData.BioSafetyCabinetCopy.viewDate);
        }
        
    
        $scope.getBuilding = function(){
            if ($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections) {
                $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Room.Building.Name;
            } else {
                $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Buildings[0];
            }
            
        }

        $scope.getRoom = function () {
            if ($scope.modalData.BioSafetyCabinetCopy.RoomId) {

            } else {
                for (var i = 0; i < $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms.length; i++) {
                    var room = $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms[i];
                    if(room.Building.Name == $scope.modalData.selectedBuilding){
                        $scope.modalData.BioSafetyCabinetCopy.Room = room;
                        $scope.modalData.BioSafetyCabinetCopy.RoomId = room.Key_id;
                    }
                }
            }
        }
    
        $scope.onSelectBuilding = function(){
            $scope.roomFilter = $scope.modalData.SelectedBuilding;
            $scope.getRoom();
        }
    
        $scope.onSelectRoom = function(){
            $scope.modalData.BioSafetyCabinetCopy.RoomId = $scope.modalData.BioSafetyCabinetCopy.Room.Key_id;
        }
        
        $scope.$watch('modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms', function() {
            if($scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator){
                $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.loadBuildings();
            }
        });
    
        $scope.save = function(copy, original){
            if (!original) original = null;
            copy.Certification_date = convenienceMethods.setMysqlTime(copy.Certification_date);
            af.saveBioSafetyCabinet(copy, original)
                    .then(function(){$scope.close()})
        }
        
        $scope.certify = function (copy, original) {
            $scope.message = null;
            if (!copy.Report_path) {
                $scope.message = "Please upload a report.";
                return;
            }

            if(!original)original = null;
            copy.Certification_date = convenienceMethods.setMysqlTime(copy.Certification_date);
            af.saveEquipmentInspection(copy, original)
                    .then(function(){$scope.close()})
        }
        
        $scope.close = function(){
            $modalInstance.close();
            af.deleteModalData();
        }
        
        $scope.$on('fileUpload', function(event, formData) {
            $scope.modalData.BioSafetyCabinetCopy.reportUploaded = false;
            $scope.modalData.BioSafetyCabinetCopy.reportUploading = true;
            $scope.$apply();

            var xhr = new XMLHttpRequest;
            var url = '../ajaxaction.php?action=uploadReportCertDocument';
            if($scope.modalData.BioSafetyCabinetCopy.Key_id)url = url + "&id="+$scope.modalData.BioSafetyCabinetCopy.Key_id;
            xhr.open('POST', url, true);
            xhr.send(formData);
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== XMLHttpRequest.DONE) {
                    return;
                }
                if (xhr.status !== 200) {
                    return;
                }
                if (xhr.status == 200){
                    $scope.modalData.BioSafetyCabinetCopy.reportUploaded = true;
                    $scope.modalData.BioSafetyCabinetCopy.reportUploading = false;
                    $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Report_path = xhr.responseText.replace(/['"]+/g, '');
                    $scope.$apply();
                }
            }
        });

    });
