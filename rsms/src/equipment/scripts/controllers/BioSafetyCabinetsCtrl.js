'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:BioSafetyCabinetsCtrl
 * @description
 * # BioSafetyCabinetsCtrl
 * Controller of the EquipmentModule Biological Safety Cabinets view
 */
angular.module('EquipmentModule')
  .controller('BioSafetyCabinetsCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
        var af = $scope.af = applicationControllerFactory;
    
        var getAllInspections = function(){
            return af.getAllEquipmentInspections().then(
                function(){
                    var inspections = dataStoreManager.get("EquipmentInspection");
                    $scope.certYears = [];
                    $scope.dueYears = [];
                    var i = inspections.length;
                    while(i--){
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
                        }
                    }
                    return inspections;
                }
            );
        },
        getAllBioSafetyCabinets = function(){
            return af.getAllBioSafetyCabinets()
                .then(
                    function(){
                        $scope.cabinets = dataStoreManager.get("BioSafetyCabinet");
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
        }
        
        //init load
        $scope.loading = getAllRooms()
                            .then(getAllPis())
                            .then(getAllInspections())
                            .then(getAllBioSafetyCabinets());

        $scope.deactivate = function(cabinet) {
            var copy = dataStoreManager.createCopy(cabinet);
            copy.Retirement_date = convenienceMethods.getUnixDate(new Date());
            console.log(copy);
            af.saveBioSafetyCabinet(cabinet.pi, copy, cabinet);
        }

        $scope.report = function(cabinet) {

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
        }

  })
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modalInstance) {
        var af = $scope.af = applicationControllerFactory;
        
        $scope.modalData = af.getModalData();
        console.log("modalData:", $scope.modalData);
        $scope.PIs = dataStoreManager.get("PrincipalInvestigator");
    
        $scope.onSelectPi = function(pi){
            pi.loadRooms();
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator = pi;
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigatorId = pi.Key_id;
        }
        
        if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections && $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].PrincipalInvestigator){
            $scope.pi = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].PrincipalInvestigator;
            $scope.pi.selected = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].PrincipalInvestigator;
            $scope.onSelectPi($scope.pi);
        }
    
        if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections && $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Room){
            $scope.modalData.BioSafetyCabinetCopy.Room = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Room;
        }
    
        if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections && $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Frequency){
            $scope.modalData.BioSafetyCabinetCopy.Frequency = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Frequency;
        }
    
        if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections && $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Report_path){
            $scope.modalData.BioSafetyCabinetCopy.Report_path = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Report_path;
        }
    
        if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections && $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Equipment_id){
            $scope.modalData.BioSafetyCabinetCopy.Equipment_id = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Equipment_id;
        }
    
        console.log($scope.modalData.BioSafetyCabinetCopy);
    
        $scope.getBuilding = function(){
            if($scope.modalData.BioSafetyCabinetCopy.EquipmentInspections){
                $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinetCopy.EquipmentInspections[$scope.modalData.inspectionIndex].Room.Building.Name;
            }
        }
    
        $scope.onSelectBuilding = function(){
            $scope.roomFilter = $scope.modalData.SelectedBuilding;    
        }
    
        $scope.onSelectRoom = function(){
            $scope.modalData.BioSafetyCabinetCopy.RoomId = $scope.modalData.BioSafetyCabinetCopy.Room.Key_id;
        }
        
        $scope.$watch('modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms', function() {
            if($scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator){
                $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.loadBuildings();
            }
        });
    
        $scope.save = function(copy, orginal){
            if(!orginal)orginal = null;
            console.log(orginal);
            af.saveBioSafetyCabinet(copy, orginal)
                    .then(function(){$scope.close()})
        }

        $scope.close = function(){
            $modalInstance.dismiss();
            af.deleteModalData();
        }
        
        $scope.$on('fileUpload', function(event, formData) {
            console.log("DIG:", $scope.modalData.BioSafetyCabinetCopy);
            $scope.modalData.BioSafetyCabinetCopy.reportUploaded = false;
            $scope.modalData.BioSafetyCabinetCopy.reportUploading = true;
            $scope.$apply();

            var xhr = new XMLHttpRequest;
            var url = '../ajaxaction.php?action=uploadProtocolDocument';
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
                    $scope.modalData.BioSafetyCabinetCopy.Report_path = xhr.responseText.replace(/['"]+/g, '');
                    $scope.$apply();
                }
            }
        });

    });
