angular
    .module('VerificationApp')
    .controller('LocationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory, $stateParams, $modal) {
        var ac = applicationControllerFactory;
        $rootScope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;
        var id = $stateParams.id;

        $scope.rooms = [];
        $scope.room;

        $rootScope.loading = getVerification(id)
            .then(getPI)
            .then(getAllBuildings);

        function getVerification(id) {
            return ac.getVerification(id)
                .then(
                function () {
                    $rootScope.verification = dataStoreManager.getById("Verification", id);
                    return $rootScope.verification.Principal_investigator_id;
                }
                )
        }

        function getPI(id) {
            return ac.getPI(id)
                .then(
                function () {
                    $scope.PI = dataStoreManager.getById("PrincipalInvestigator", id);
                    console.log(dataStore);
                }
                )
        }

        function getAllBuildings() {
            return ac.getAllBuildings()
                .then(
                function (buildings) {
                    $scope.allBuildings = dataStore.Building;
                },
                function () {
                    $scope.error = "Couldn't get the users";
                    return false;
                }
                );
        }

        $scope.onBuildingSelect = function (item) {
            if (item) $scope.rooms = item.Rooms;
            $scope.selectedBuilding = item;
        }

        $scope.onRoomSelect = function (item) {
            if (item) {
                if (item.Animal_facility) {
                    item.PendingRoomChangeCopy.New_status = Constants.PENDING_CHANGE.ROOM_STATUS.ADDED;
                    item.PendingRoomChangeCopy.Is_active = true;
                    item.PendingRoomChangeCopy.Name = item.Name
                    var building = dataStoreManager.getById("Building", item.Building_id)
                    if (!item.Building_name) item.Building_name = building.Name;
                    $scope.room = item;                    
                    openModal(item, building);
                } else {
                    if (!item.PendingRoomChangeCopy) item.PendingRoomChangeCopy = modelInflatorFactory.instantiateObjectFromJson(new window.PendingRoomChange);

                    item.PendingRoomChangeCopy.New_status = Constants.PENDING_CHANGE.ROOM_STATUS.ADDED;
                    item.PendingRoomChangeCopy.Is_active = true;
                    item.PendingRoomChangeCopy.Name = item.Name
                    item.Building_name = dataStoreManager.getById("Building", item.Building_id).Name;
                    $scope.room = item;
                }
            }
        }

        function openModal(room, building) {
            var modalInstance = $modal.open({
                templateUrl: 'views/animal-room-modal.html',
                controller: 'LocationModalCtrl',
                resolve: {
                    room: function () { return room; },
                    building: function () { return building; }
                }
            });
        }

    }).controller('LocationModalCtrl', function ($scope, room, building, $rootScope, applicationControllerFactory, modelInflatorFactory, $stateParams, $modalInstance) {
        console.log(room);
        $scope.room = room;        
        
        $scope.save = function(room){
            return applicationControllerFactory.savePendingRoomChange(room, $rootScope.verification.Key_id, building).then(function(){$modalInstance.close()})
        }

        $scope.getNeedsSpecification = function (room) {
            $scope.message = "";
            $scope.rooms = null;
            if (room.PendingRoomChangeCopy.NeedsSpecific && room.Name.toUpperCase() == "DLAR") {
                $scope.rooms = building.Rooms.filter(function (r) {
                    return r.Name.toUpperCase() != "DLAR" && r.Animal_facility;
                })
            } else {
                $scope.message = "Since you don't do experiments involving hazardous biological materials in animals in this room, we'll record your room as DLAR";
                if (room.Name.toUpperCase() != "DLAR") {
                    //set room_id to the id of the room named DLAR in the relevant building;
                    var room = building.Rooms.filter(function (r) {
                        return r.Name.toUpperCase() == "DLAR";
                    })[0];

                    if(room){
                        $scope.room.PendingRoomChangeCopy.Parent_id = room.Key_id;
                        $scope.room.PendingRoomChangeCopy.Name = room.Name;
                    }

                }
            }


        }

        $scope.selectRoom = function(change, roomId, rooms){
            rooms.forEach(function(r){if(r.Key_id == roomId) change.Name = r.Name})
        }

    });
