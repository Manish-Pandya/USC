angular
    .module('VerificationApp')
    .controller('HazardVerificationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory, $modal) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;
        $scope.Constants = Constants;

        $scope.categoryIdx = $scope.buildingIdx = $scope.roomIdx = 0;
        $scope.hazardCategories = [Constants.MASTER_HAZARD_IDS.BIOLOGICAL, Constants.MASTER_HAZARD_IDS.CHEMICAL, Constants.MASTER_HAZARD_IDS.RADIATION];

        $scope.HazCat = {};
        $scope.dataHolder = { hasNewHazards: false };


        $scope.incrementRoom = function (int) {
            $scope.dataHolder.hasNewHazards = false;
            var turn = false;
            $scope.categoryIdx += int;
            if ($scope.categoryIdx > $scope.hazardCategories.length - 1) {
                $scope.categoryIdx = 0;
                turn = true;
            } else if ($scope.categoryIdx < 0) {
                $scope.categoryIdx = $scope.hazardCategories.length - 1;
                turn = true;
            }
            if (turn) {
                var bldg = $scope.PI.Buildings[$scope.buildingIdx];
                if ($scope.roomIdx + int > -1) {
                    if ($scope.roomIdx + int < bldg.Rooms.length) {
                        $scope.roomIdx += int;
                    } else {
                        $scope.roomIdx = 0;
                        $scope.buildingIdx++;
                    }
                } else if ($scope.buildingIdx > 0) {
                    $scope.buildingIdx--;
                    bldg = $scope.PI.Buildings[$scope.buildingIdx];
                    $scope.roomIdx = bldg.Rooms.length - 1;
                }
            }
        }

        $scope.getThatThereRoom = function (hazard, id) {
            var len = hazard.InspectionRooms.length; 
            for (var x = 0; x < len; x++) {
                var room = hazard.InspectionRooms[x];
                if (room.Room_id == id) {
                    $scope.childRoomIdx = x;
                    return;
                }
            }
        }

        $scope.openMultiplePIHazardsModal = function (hazardDto, room) {
            var modalData = {};
            modalData.HazardDto = hazardDto;
            $scope.PI.Rooms = [room];
            modalData.PI = $scope.PI;
            $scope.pisPromise = ac.getPiHazards(hazardDto, room)
                .then(function (pHRS) {
                    modalData.pHRS = pHRS;
                    ac.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: '../hazard-inventory/views/modals/multiple-PI-hazards-modal.html',
                        controller: 'HazardVerificationModalCtrl'
                    });
                })

        }

        $scope.onSelectHazard = function (hazard) {
            $scope.selectedHazard = hazard;
            console.log(hazard);
            var roomId = $scope.PI.Buildings[$scope.buildingIdx].Rooms[$scope.roomIdx].Key_id;

            for (var x = 0; x < hazard.InspectionRooms.length; x++) {
                if (roomId === hazard.InspectionRooms[x].Room_id) {
                    $scope.roomDto = hazard.InspectionRooms[x];
                    continue;
                }
            }
        }

        $scope.addNewHazard = function (name) {
            var newChange = new PendingHazardDtoChange();

            newChange.Class = "PendingHazardDtoChange";
            newChange.Hazard_name = name;
            newChange.Room_id = $scope.PI.Buildings[$scope.buildingIdx].Rooms[$scope.roomIdx].Key_id;
            newChange.Principal_investigator_id = $scope.PI.Key_id;
            newChange.Parent_class = "PrincipalInvestigatorHazardRoomRelation";
            newChange.Verification_id = $scope.verification.Key_id;
            newChange.New_status = null;

            var roomDto = {
                    Class: "PIHazardRoomDto",
                    Principal_investigator_id: $scope.PI.Key_id,
                    Room_id: $scope.PI.Buildings[$scope.buildingIdx].Rooms[$scope.roomIdx].Key_id,
                    ContainsHazard: true,
                    PendingHazardDtoChangeCopy: newChange
            }


            var newHazard = new HazardDto();
            newHazard.Class = "HazardDto";
            newHazard.Principal_investigator_id = $scope.PI.Key_id;
            newHazard.Hazard_name = name;
            newHazard.InspectionRooms = [roomDto];
            newHazard.IsPresent = true;
            newHazard.Name = name;
            newHazard.RoomIds = [roomDto.Room_id];
            
            return newHazard;
        }

        var id = 1; // TODO: This shouldn't be set here. Just for testing.

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllHazards);
        $scope.dsm = dataStoreManager;

        var setHazardChangeDTO = function (hazard) {
            var roomLen = hazard.InspectionRooms.length;           
            for (var x = 0; x < roomLen; x++) {
                var room = hazard.InspectionRooms[x];
                //WE DON'T NEED TO CREATE A PendingHazardDtoChange IF THERE IS ALREADY ONE IN THE DATASTORE
                room.PendingHazardDtoChange = findRelevantPendingChange(room.Room_id, hazard.Hazard_id);
                if (!room.PendingHazardDtoChange) {
                    //create PendingHazardDtoChange
                    room.PendingHazardDtoChange = new window.PendingHazardDtoChange();
                    room.PendingHazardDtoChange.Hazard_id = hazard.Key_id;
                    room.PendingHazardDtoChange.Room_id = room.Room_id;
                    room.PendingHazardDtoChange.Principal_investigator_id = $scope.PI.Key_id;
                    room.PendingHazardDtoChange.Parent_class = "PrincipalInvestigatorHazardRoomRelation";
                    room.PendingHazardDtoChange.Class = "PendingHazardDtoChange";

                    //get the proper status
                    room.PendingHazardDtoChange.New_status = getStatus(room);
                }
                                        
                room.PendingHazardDtoChangeCopy = new window.PendingHazardDtoChange();
                room.PendingHazardDtoChangeCopy = Object.assign(room.PendingHazardDtoChangeCopy, room.PendingHazardDtoChange);
            }
        }

        function getStatus(room) {
            if(room.ContainsHazard){
                if (room.Status == Constants.HAZARD_PI_ROOM.STATUS.IN_USE.KEY) {
                    return Constants.ROOM_HAZARD_STATUS.IN_USE.KEY;
                } else {
                    return Constants.ROOM_HAZARD_STATUS.STORED_ONLY.KEY;
                } 
            }else{
                return Constants.ROOM_HAZARD_STATUS.NOT_USED.KEY;
            }
        }

        function findRelevantPendingChange(roomId, hazardId) {
            var changes = dataStoreManager.get("PendingHazardDtoChange");
            if (changes) {
                for (var x = 0; x < changes.length; x++) {
                    var change = changes[x];
                    if (change.Room_id == roomId && change.Hazard_id == hazardId) {
                        return change;
                    }
                }
            }
            return null;
        }

        function getVerification(id) {
            return ac.getVerification(id)
                    .then(
                        function () {
                            $scope.verification = dataStoreManager.getById("Verification", id);
                            return $scope.verification.Principal_investigator_id;
                        }
                    )
        }

        function getPI(id) {
            return ac.getPI(id)
                     .then(
                         function () {
                             $scope.PI = dataStoreManager.getById("PrincipalInvestigator", id);
                             return $scope.verification.Principal_investigator_id;
                         }
                     )
        }

        function getAllHazards() {
            return ac.getAllHazards(id)
                     .then(
                         function (hazards) {
                             // get leaf parents
                             var hazard, leafParentHazards = [];
                             var len = hazards.length;
                             for (var n = 0; n < len; n++) {
                                 hazard = hazards[n];
                                 hazard.loadSubHazards();                                 
                                
                                 if (hazard.ActiveSubHazards && !hazard.ActiveSubHazards.length) {
                                    setHazardChangeDTO(hazard);
                                    if (leafParentHazards.indexOf(hazard.Parent_hazard_id) == -1) {
                                        leafParentHazards.push(hazard.Parent_hazard_id);
                                    }
                                 }
                             }
                             //http://erasmus.graysail.com/rsms/src/verification/#/inventory
                             var categorizedHazards = {};
                             var categorizedLeafHazards = {};
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.BIOLOGICAL] = [];
                             categorizedLeafHazards[Constants.MASTER_HAZARD_IDS.BIOLOGICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.CHEMICAL] = [];
                             categorizedLeafHazards[Constants.MASTER_HAZARD_IDS.CHEMICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.RADIATION] = [];
                             categorizedLeafHazards[Constants.MASTER_HAZARD_IDS.RADIATION] = [];

                             var idsMap = [];

                             for (var x = 0; x < leafParentHazards.length; x++) {
                                 recurseUpTree(leafParentHazards[x]);
                             }
                             
                             function recurseUpTree(originalHazardId, hazardId) {
                                 var hazard;
                                 if (hazardId) {
                                     hazard = dataStoreManager.getById("HazardDto", hazardId);
                                 } else {
                                     hazard = dataStoreManager.getById("HazardDto", originalHazardId);
                                 }
                                 
                                 if (categorizedHazards.hasOwnProperty(hazard.Key_id) || categorizedHazards.hasOwnProperty(hazard.Parent_hazard_id)) {
                                     if (idsMap.indexOf(hazard.Key_id) == -1 && categorizedHazards.hasOwnProperty(hazard.Parent_hazard_id)) {
                                         var leafParent = dataStoreManager.getById("HazardDto", originalHazardId);
                                         categorizedHazards[hazard.Parent_hazard_id].push(leafParent);
                                         categorizedLeafHazards[hazard.Parent_hazard_id] = categorizedLeafHazards[hazard.Parent_hazard_id].concat(leafParent.ActiveSubHazards);
                                         idsMap.push(hazard.Key_id);
                                         return false;
                                     }
                                 } else {
                                     var p = dataStoreManager.getById("HazardDto", hazard.Parent_hazard_id);
                                     //why is there a hazard with the same key_id as its parent_id???
                                     if (!p || hazard.Key_id == hazard.Parent_hazard_id) return false;                                 
                                     return recurseUpTree(originalHazardId, p.Key_id);
                                 }
                                 return false;
                             }
                             
                             $scope.allHazards = categorizedHazards;
                             $scope.allLeafHazards = categorizedLeafHazards;

                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }

    })

    .controller('HazardVerificationModalCtrl', function ($scope, $q, $http, applicationControllerFactory, $modalInstance, convenienceMethods) {
        $scope.constants = Constants;
        var af = applicationControllerFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.dataStoreManager = dataStoreManager;

        $scope.processRooms = function (inspection, rooms) {
            for (var j = 0; j < inspection.Rooms.length; j++) {
                inspection.Rooms[j].checked = true;
            }
            for (var k = 0; k < rooms.length; k++) {
                if (!convenienceMethods.arrayContainsObject(inspection.Rooms, rooms[k])) {
                    inspection.Rooms.push(rooms[k]);
                }
            }
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

    });
