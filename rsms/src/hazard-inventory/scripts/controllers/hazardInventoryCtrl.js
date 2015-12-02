'use strict';

/**
 * @ngdoc function
 * @name HazardInventory.controller:HazardInventoryCtrl
 * @description
 * # HazardInventoryCtrl
 * Controller of the HazardInventory Hazard Hub
 */
angular.module('HazardInventory')
    .controller('HazardInventoryCtrl', function ($scope, $q, $http, applicationControllerFactory, $modal, $location) {

        //do we have access to action functions?
        $scope.af = applicationControllerFactory;
        var af = applicationControllerFactory;
        var getAllPIs = function () {
                return af
                    .getAllPIs()
                    .then(function (pis) {
                            //we have to set this equal to the promise rather than the getter, because the getter will return a promise, and that breaks the typeahead because of a ui-bootstrap bug
                            $scope.PIs = dataStoreManager.get("PrincipalInvestigator");
                            return pis;
                        },
                        function () {
                            $scope.error = 'There was a problem getting the list of Principal Investigators.  Please check your internet connection.'
                        });
            },
            getHazards = function (id, roomId) {
                if(!roomId)roomId = false;
                return af
                    .getAllHazardDtos(id, roomId)
                    .then(
                        function (hazards) {
                            return hazards
                        },
                        function () {
                            $scope.error = 'Couldn\'t get all hazards.'
                        }
                    ).then(
                        function (hazards) {
                            var hazard = dataStoreManager.getById('HazardDto', 10000);
                            hazard.loadSubHazards();
                            $scope.hazard = hazard;
                            var hazards = dataStoreManager.get("HazardDto");
                            var i = hazards.length;
                            while(i--){
                                if(hazards[i].HasChildren)hazards[i].loadSubHazards();
                            }
                        },
                        function () {
                            $scope.error = 'Couldn\'t find the right hazards.'
                        }

                    ).then(
                        af.getBuildings(id,roomId).then(function(rooms){;$scope.PI.Rooms=rooms;})
                    );

            }

        $scope.piPromise =
            getAllPIs()
            .then(
                function (pis) {
                    $scope.pis = pis;
                    console.log($location);
                    if($location.search()){
                        if ($location.search().pi){
                            console.log(dataStore);
                            var pi = dataStoreManager.getById("PrincipalInvestigator",$location.search().pi);
                            if($location.search().room){
                                $scope.onSelectPi(pi, $location.search().room);
                            }else{
                                $scope.onSelectPi(pi);
                            }
                        }
                    }
                }
            );


        $scope.onSelectPi = function (pi, roomId) {
            pi.loadInspections();
            $scope.PI = pi;
            if(!roomId)roomId = false;
            $scope.hazardPromise = getHazards(pi.Key_id, roomId);
            $scope.selectPI = false;
            $location.search("pi",pi.Key_id);
        }

        $scope.getShowRooms = function(hazard){
            var atLeastOne = false;
            var notAll = false;
            var i = hazard.InspectionRooms.length;
            while(i--){
                var room = hazard.InspectionRooms[i];
                if(room.ContainsHazard){
                    atLeastOne = true;
                }else{
                    notAll = true;
                }
            }
            if(atLeastOne && notAll)return true;
            return false;

        }

        $scope.openSubsModal = function (hazard) {
            hazard.loadSubHazards();
            var modalData = {};
            modalData.Hazard = hazard;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/sub-hazards-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }

        $scope.openRoomsModal = function (hazard) {
            hazard.loadSubHazards();
            var modalData = {};
            modalData.Hazard = hazard;
            modalData.Parent = dataStoreManager.getById("HazardDto",hazard.Parent_hazard_id);
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/rooms-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }

        $scope.openMultiplePIsModal = function (hazardDto, room) {
            var modalData = {};
            if(!room){
                var room = false;
            }else{
                modalData[room.Class] = room;
            }
            if(!hazardDto){
                var hazardDto = false;
            }else{
                modalData.HazardDto = hazardDto;
            }
            $scope.pisPromise = af.getPIs(hazardDto, room)
                .then(function(pis){
                    modalData.PIs = pis;
                    af.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: 'views/modals/multiple-PIs-modal.html',
                        controller: 'HazardInventoryModalCtrl'
                    });
                })

        }

        $scope.openNotes = function(){
             var modalData = {};
             modalData.PI = $scope.PI;
             af.setModalData( modalData );
             var modalInstance = $modal.open({
                templateUrl: 'views/modals/inspection-notes-modal.html',
                controller: 'HazardInventoryModalCtrl'
              });

              modalInstance.result.then(function () {

              });
        }

        $scope.openPreviousInspections = function(){
             var modalData = {};
             modalData.PI = $scope.PI;
             af.setModalData( modalData );
             var modalInstance = $modal.open({
                templateUrl: 'views/modals/archived-reports.html',
                controller: 'HazardInventoryModalCtrl'
              });

              modalInstance.result.then(function () {

              });
        }

        $scope.startInspection = function(){
             var modalData = {};
             modalData.PI = $scope.PI;
             modalData.openInspections = true;
             af.setModalData( modalData );
             var modalInstance = $modal.open({
                templateUrl: 'views/modals/open-inspections.html',
                controller: 'HazardInventoryModalCtrl'
              });

              modalInstance.result.then(function () {

              });
        }

    })
    .controller('HazardInventoryModalCtrl', function ($scope, $q, $http, applicationControllerFactory, $modalInstance, convenienceMethods) {
        var af = applicationControllerFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        $scope.processRooms = function(inspection, rooms){
            for(var j = 0; j<inspection.Rooms.length; j++){
                inspection.Rooms[j].checked = true;
            }
            for(var k = 0; k<rooms.length; k++){
                if(!convenienceMethods.arrayContainsObject(inspection.Rooms, rooms[k])){
                    inspection.Rooms.push(rooms[k]);
                }
            }
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }


    });
