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
        var nullReturn = function(){}
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
                            if (inspections[i].Due_date) {
                                console.log()
                                var dueYear = inspections[i].Due_date.split('-')[0];
                                if ($scope.dueYears.indexOf(dueYear) == -1) {
                                    $scope.dueYears.push(dueYear);
                                }
                            }
                        }
                    }
                    console.log("cert", $scope.certYears);
                    console.log("due", $scope.dueYears);
                    return inspections;
                }
            );
        },
        getAllBioSafetyCabinets = function(){
           // console.log('second one called')
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
        
        $scope.getUniqueCertYears = function() {
            if ($scope.certYears) {
                return $scope.certYears;
            } else {
                $scope.certYears = [];
                var i = $scope.cabinets.length;
                while(i--){
                    var j = $scope.cabinets[i].EquipmentInspections ? $scope.cabinets[i].EquipmentInspections.length : 0;
                    console.log($scope.cabinets[i].EquipmentInspections);
                    while(j--){
                        var certYear = $scope.cabinets[i].EquipmentInspections.Certification_date.split('-')[0];
                        var dueYear = $scope.cabinets[i].EquipmentInspections.Due_date.split('-')[0];
                        if ($scope.certYears.indexOf(certYear) == -1) {
                            $scope.certYears.push(certYear);
                        }
                        if ($scope.dueYears.indexOf(dueYear) == -1) {
                            $scope.dueYears.push(dueYear);
                        }
                    }
                }
                console.log($scope.certYears);
                console.log($scope.dueYears);
                return $scope.certYears;
            }
        }

        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.BioSafetyCabinet();
                object.Is_active = true;
                object.Class = "BioSafetyCabinet";
            }
            if(object.PrincipalInvestigator)object.PrincipalInvestigator.loadRooms();
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/bio-safety-cabinet-modal.html',
                controller: 'BioSafetyCabinetsModalCtrl'
            });
        }

  })
  .controller('BioSafetyCabinetsModalCtrl', function ($scope, applicationControllerFactory, $stateParams, $rootScope, $modalInstance) {
        var af = $scope.af = applicationControllerFactory;
        
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.PIs = dataStoreManager.get("PrincipalInvestigator");
        if($scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator){
            $scope.pi = $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator;
            $scope.pi.selected = $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator;
        }
    
        $scope.onSelectPi = function(pi){
            pi.loadRooms();
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator = pi;
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigatorId = pi.Key_id;
            console.log($scope.modalData.BioSafetyCabinetCopy);
        }
    
        $scope.getBuilding = function(){
            $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinetCopy.Room.Building.Name;
        }
    
        $scope.onSelectBuilding = function(){
            $scope.roomFilter = $scope.modalData.SelectedBuilding;            
        }
    
        $scope.onSelectRoom = function(){
            $scope.modalData.BioSafetyCabinetCopy.RoomId = $scope.modalData.BioSafetyCabinetCopy.Room.Key_id;
        }
        
        $scope.$watch('modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms', function() {
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.loadBuildings();
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

    });
