angular
    .module('VerificationApp')
    .controller('HazardVerificationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory, $modal, $stateParams) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;
        $scope.Constants = Constants;

        $scope.categoryIdx = $scope.buildingIdx = $scope.roomIdx = 0;
        $scope.hazardCategories = [Constants.MASTER_HAZARD_IDS.BIOLOGICAL, Constants.MASTER_HAZARD_IDS.CHEMICAL, Constants.MASTER_HAZARD_IDS.RADIATION];

        $scope.HazCat = {};
        $scope.dataHolder = { hasNewHazards: false };

     
        $scope.setCategoryIdx = function (idx) {
            $scope.categoryIdx = idx;
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

        var id = $stateParams.id;

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
                             setStepMap($scope.PI);
                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }

        $scope.stepMap = [];
        function setStepMap(pi) {
            var buildings = pi.Buildings;
            for (var i = 0;i < buildings.length; i++) {
                var bldg = buildings[i];
                var rooms = bldg.Rooms;
                for (var n = 0; n < rooms.length; n++) {
                    var room = rooms[n];                   
                    for (var x = 0; x < $scope.hazardCategories.length; x++) {
                        var mapping = { BuildingIdx: i, RoomIdx: n, HazardIdx: x, isComplete:false }
                        if (ac.getCachedVerification().Substep > $scope.stepMap.length) {
                            mapping.isComplete = true;
                        }
                        $scope.stepMap.push(mapping);
                    }
                }

            }
            if (ac.getCachedVerification().Substep > 0) {
                var idx = ac.getCachedVerification().Substep;
                $scope.navigate($scope.stepMap[idx]);
            }
        }

        $scope.stepIsAllowed = function (step, verification) {
            if (!verification || !step) return false;
            var maxStep = parseInt(verification.Substep);
            for (var i = 0; i < $scope.stepMap.length; i++) {
                //if (!mapping) return false;

                var mapping = $scope.stepMap[i];
                

                if (mapping.HazardIdx == step.HazardIdx
                    && mapping.BuildingIdx == step.BuildingIdx
                    && mapping.RoomIdx == step.RoomIdx) {

                    if (i != 0) var previousMapping = $scope.stepMap[i - 1];
                    if (previousMapping && previousMapping.isComplete) {
                        return i <= maxStep + 1
                    } else {
                        return i <= maxStep;
                    }
                }
            }
            return false;
        }

        $scope.navigate = function (mapObject) {
            if (!$scope.currentStep) $scope.currentStep = 0;
            if ($scope.stepIsAllowed(mapObject, ac.getCachedVerification())) {
                $scope.categoryIdx = mapObject.HazardIdx;
                $scope.buildingIdx = mapObject.BuildingIdx;
                $scope.roomIdx = mapObject.RoomIdx;

                //get the index of the current step
                for (var i = 0; i < $scope.stepMap.length; i++) {
                    var mapping = $scope.stepMap[i];
                    if (mapping.HazardIdx == mapObject.HazardIdx
                        && mapping.BuildingIdx == mapObject.BuildingIdx
                        && mapping.RoomIdx == mapObject.RoomIdx) {
                        $scope.currentStep = i;
                        break;
                    }
                }
            } else {
                alert('you gotta finish first')
            }
        }

        $scope.setSubStep = function (verification, step) {
            for (var i = 0; i < $scope.stepMap.length; i++) {
                var mapping = $scope.stepMap[i];
                if (mapping.HazardIdx == step.HazardIdx
                    && mapping.BuildingIdx == step.BuildingIdx
                    && mapping.RoomIdx == step.RoomIdx) {
                    var verDto = {};
                    angular.extend(verDto, verification);
                    verDto.Substep = i;
                    ac.saveVerification(verDto, verDto.Step).then(function () {
                        verification.Substep = verDto.Substep;
                    })

                }
            }
        }

        $scope.getNextRoomMapping = function (building) {
            if (!$scope.currentStep) return false;
            var step = $scope.stepMap[$scope.currentStep];
            var roomIdx = step.RoomIdx + 1;
            //does another room exist in the building?
            if (building.Rooms.length > roomIdx) {
                return $scope.nextRoomStep = $scope.stepMap[$scope.currentStep+1] || false;
            } else {
                var buildIdx = $scope.stepMap[$scope.currentStep].BuildingIdx;
                for (var i = $scope.currentStep; i < $scope.stepMap.length; i++) {                    
                    if (step.BuildingIdx != $scope.stepMap[i].BuildingIdx) {
                        return $scope.nextRoomStep = $scope.stepMap[i];
                    }
                }
            }

            return false;
        }


        $scope.getPreviousRoomMapping = function (building) {
            if (!$scope.currentStep) return false;
            var step = $scope.stepMap[$scope.currentStep];
            var roomIdx = step.RoomIdx + 1;
            //does another room exist in the building?
            console.log(roomIdx, building.Rooms.length)
            if (building.Rooms.length > roomIdx) {
                console.log($scope.stepIsAllowed($scope.stepMap[$scope.currentStep + 1]), $scope.stepMap[$scope.currentStep + 1])
                return $scope.stepMap[$scope.currentStep + 1] || false;
            } else {
                var buildIdx = $scope.stepMap[$scope.currentStep].BuildingIdx;
                var i = $scope.currentStep;
                while ( i--) {
                    if (step.BuildingIdx != $scope.stepMap[i].BuildingIdx) {
                        console.log($scope.stepIsAllowed($scope.stepMap[i]), $scope.stepMap[i])
                        return $scope.stepMap[i];
                    }
                }
            }

            return false;
        }


        $scope.isCurrentStep = function (step, current) {
            console.log('hey')
            return current && current.BuildingIdx == step.BuildingIdx && current.RoomIdx == step.RoomIdx && current.HazardIdx == step.HazardIdx;
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
