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

        var getAllBioSafetyCabinets = function(){
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
                                console.log($scope.rooms);
                                return $scope.rooms
                            }
                        );
        },
        getAllBuildings = function(){
            return af.getAllBuildings()
                        .then(
                            function(){
                                console.log( dataStoreManager.get("Building"));
                                $scope.buildings = dataStoreManager.get("Building");
                                return $scope.buildings
                            }
                        );
        }

        //init load
        $scope.loading = getAllRooms()
                            .then(getAllPis())
                            .then(getAllBioSafetyCabinets());

        $scope.deactivate = function(cabinet) {
            var copy = dataStoreManager.createCopy(cabinet);
            copy.Retirement_date = convenienceMethods.getUnixDate(new Date());
            console.log(copy);
            af.saveBioSafetyCabinet(cabinet.pi, copy, cabinet);
        }

        $scope.report = function(cabinet) {

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
            $scope.modalData.BioSafetyCabinetCopy.Principal_investigator_id = pi.Key_id;
        }

        $scope.getBuilding = function(){
            $scope.modalData.selectedBuilding = $scope.modalData.BioSafetyCabinetCopy.Room.Building.Name;
        }

        $scope.onSelectBuilding = function(){
            $scope.roomFilter = $scope.modalData.SelectedBuilding;
        }

        $scope.onSelectRoom = function(){
            $scope.modalData.BioSafetyCabinetCopy.Room_id = $scope.modalData.BioSafetyCabinetCopy.Room.Key_id;
        }

        $scope.$watch('modalData.BioSafetyCabinetCopy.PrincipalInvestigator.Rooms', function() {
            $scope.modalData.BioSafetyCabinetCopy.PrincipalInvestigator.loadBuildings();
        });

        $scope.save = function(copy, orginal){
            console.log(copy);
            return;
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
