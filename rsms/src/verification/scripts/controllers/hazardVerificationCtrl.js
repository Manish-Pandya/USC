angular
    .module('VerificationApp')
    .controller('HazardVerificationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory, $modal, $stateParams, convenienceMethods) {
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

        $scope.openModal = function (parentHazard) {
            var modalData = {};
            modalData.HazardDto = hazardDto;
            modalData.PI = $scope.PI;
            $scope.pisPromise = ac.getPiHazards(hazardDto, room)
                .then(function (pHRS) {
                    modalData.pHRS = pHRS;
                    ac.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: 'modals/hazard-modal.html',
                        controller: 'HazardVerificationAddModalCtrl'
                    });
                })

        }

        $scope.onSelectHazard = function (hazard) {
            hazard.Room_id = $scope.PI.Buildings[$scope.buildingIdx].Rooms[$scope.roomIdx].Key_id;
            hazard.RoomIds = [hazard.Room_id];
            $scope.selectedHazard = hazard;
            setHazardChangeDTO($scope.selectedHazard, true);

        }

        $scope.addNewHazard = function (name) {

            console.log("SELECTED IS", name, event)

            var newChange = new PendingHazardDtoChange();

            newChange.Class = "PendingHazardDtoChange";
            newChange.Hazard_name = name;
            newChange.Room_id = $scope.PI.Buildings[$scope.buildingIdx].Rooms[$scope.roomIdx].Key_id;
            newChange.Principal_investigator_id = $scope.PI.Key_id;
            newChange.Parent_class = "PrincipalInvestigatorHazardRoomRelation";
            newChange.Verification_id = $scope.verification.Key_id;
            newChange.New_status = null;
            

            var newHazard = new HazardDto();
            newHazard.Class = "HazardDto";
            newHazard.Principal_investigator_id = $scope.PI.Key_id;
            newHazard.Hazard_name = name;
            newHazard.IsPresent = true;
            newHazard.Name = name;

            //make a copy of some inspection rooms
            newHazard.InspectionRooms = dataStore.HazardDto[0].InspectionRooms.map(function (r) {
                return Object.assign({}, r, {ContainsHazard:false, MultiplePis:false})
            })

            setHazardChangeDTO(newHazard, true);
            return newHazard;
        }

        var id = $stateParams.id;

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllHazards);

        $scope.dsm = dataStoreManager;

       

        var setHazardChangeDTO = $scope.setHazardChangeDTO = function (hazard, log) {
            var roomLen = hazard.InspectionRooms.length;  
            if (log) console.log(hazard);
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
                    room.PendingHazardDtoChange.Parent_id = $scope.hazardCategories[$scope.categoryIdx];
                    room.PendingHazardDtoChange.Hazard_name = hazard.Hazard_name;
                    //get the proper status
                    room.PendingHazardDtoChange.New_status = getStatus(room);
                }
                                        
                room.PendingHazardDtoChangeCopy = new window.PendingHazardDtoChange();
                room.PendingHazardDtoChangeCopy = Object.assign(room.PendingHazardDtoChangeCopy, room.PendingHazardDtoChange);
            }
        }

        function getStatus(room) {
            //console.log("KEYS",Object.keys(Constants.HAZARD_PI_ROOM.STATUS))
            if(room.ContainsHazard === true){
                if (room.Status == Constants.ROOM_HAZARD_STATUS.IN_USE.KEY) {
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
                        console.log(change);
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
                             console.log($scope.PI);
                             return $scope.verification.Principal_investigator_id;
                         }
                     )
        }
        

        function recurseToLeaves(hazard, leaves) {
            var leaves = leaves ? leaves : [];
            hazard.ActiveSubHazards.forEach(function (h) {
                if (!h.ActiveSubHazards) {
                    leaves.push(h);
                } else {
                    h.ActiveSubHazards.forEach(function (sh) {
                        recurseToLeaves(sh, leaves);
                    });
                }
            })
            return leaves;
        }
        

        function getAllHazards(id) {
            return ac.getAllHazards(id)
                     .then(
                        function () {
                            var hazards = $scope.allHazards = dataStore.HazardDto;
                             // get leaf parents
                             var hazard, leafParentHazards = [];
                             var len = hazards.length;
                             for (var n = 0; n < len; n++) {
                                 hazard = hazards[n];
                                 hazard.loadSubHazards();
                             }

                             var categorizedHazards = {};
                             var categorizedLeafHazards = {};
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.BIOLOGICAL] = [];
                             categorizedLeafHazards[Constants.MASTER_HAZARD_IDS.BIOLOGICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.CHEMICAL] = [];
                             categorizedLeafHazards[Constants.MASTER_HAZARD_IDS.CHEMICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.RADIATION] = [];
                             categorizedLeafHazards[Constants.MASTER_HAZARD_IDS.RADIATION] = [];

                             //get all hazards that are present, but don't have children that are parent
                             $scope.presentHazards = hazards.filter((h) => {
                                 setHazardChangeDTO(h);
                                 if ((h.IsPresent || h.InspectionRooms.some( (r) => { return r.PendingHazardDtoChangeCopy && r.PendingHazardDtoChangeCopy.Key_id && r.PendingHazardDtoChangeCopy.New_status != Constants.ROOM_HAZARD_STATUS.NOT_USED }))
                                     && ((!h.ActiveSubHazards || h.ActiveSubHazards.every((sh) => { return !sh.IsPresent })))
                                 ) {
                                     return true;
                                 }
                                 return false;
                             })
                             var branches = [];
                             $scope.presentHazards.forEach((h) => {
                                 var idMap = [];

                                 if ( categorizedHazards.hasOwnProperty(h.Parent_hazard_id) ) {
                                     branches[h.Parent_hazard_id] = dataStoreManager.getById("HazardDto", h.Parent_hazard_id);
                                 } else {
                                     getBranches(h)
                                 }

                                 function getBranches(parent) {
                                     if ( categorizedHazards.hasOwnProperty(parent.Parent_hazard_id) ) {
                                         branches[parent.Hazard_id] = parent;
                                     } else {
                                        return (getBranches(dataStoreManager.getById("HazardDto", parent.Parent_hazard_id) ) )
                                     }
                                     return false;
                                 }
                             })  

                             $scope.branches = branches;
                             
                             $scope.categorizedHazards = categorizedHazards;
                             $scope.currentStep = $scope.maxStep = $scope.categoryIdx = parseInt($scope.verification.Substep || 0);
                             $scope.stepMap = setStepMap($scope.currentStep);
                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }


        $scope.getPresentLeavesByParent = (parent) => {
            var children = [];
            recurseBranch(parent);
            function recurseBranch(parent) {
                parent.ActiveSubHazards.forEach((h) => {                    
                    if (convenienceMethods.arrayContainsObject($scope.presentHazards, h)) {
                        children.push(h);
                    } else if ( !$scope.categorizedHazards.hasOwnProperty(parent.Hazard_id) ){
                        return recurseBranch(h)
                    }
                    return false;
                })
            }
            return children;
        }

        $scope.getLeaves = (parent) => {
            var children = [];
            recurseBranch(parent);
            function recurseBranch(parent) {
                parent.ActiveSubHazards.forEach((h) => {
                    if (h.ActiveSubHazards && h.ActiveSubHazards.length) {
                        return recurseBranch(h)
                    }
                    children.push(h);
                    return false;
                })
            }
            return children;
        }


        $scope.getSubHazards = function (id, all) {
            var branches = all ? $scope.allHazards : $scope.branches;
            return branches.filter((h) => {
                return all && h.Parent_hazard_id == id || ( !all && (h.Parent_hazard_id == id || h.Hazard_id == id) );
            })
        }

        function setStepMap(currentStep) {
            return Constants.CHECKLIST_CATEGORIES_BY_MASTER_ID.map(function (c, i) { 
                console.log(i, currentStep)
                return Object.assign({}, c, {
                    Step: i, isComplete:  i < currentStep
                });
            });
        }

        $scope.setSubStep = function (v, substep, checked) {
            substep = checked ? substep : substep-1;
            console.log(substep, checked);
            $scope.saving = ac.saveVerification(v, v.Step, substep).then(function () {
                $scope.verification.Substep = substep;
                $scope.currentStep = substep;
                $scope.categoryIdx = substep;
                $scope.maxStep = substep;
            });
        }
        
        $scope.navigate = function (hazard) {
            console.log("NAV RECEIVED",hazard);
            $scope.categoryIdx = $scope.currentStep = hazard.Step;
        }

        $scope.getMatchingInspectionRoom = function (room, rooms) {
            if (!room || !rooms) return;
            for (var i = 0; i < rooms.length; i++) {
                if (room.Key_id == rooms[i].Room_id) return i;
            }
            return -1;
        }

        $scope.getCorrespondingNewHazard = function (change) {
            var hazard = change.Hazard_id ? change : change;
            console.log(dataStore.HazardDto[change.Hazard_id]);
        }

        $scope.getUsed = function (hazard) {
            setHazardChangeDTO(hazard);
            var notAnswered = hazard.InspectionRooms.every((r) => {
                return !r.PendingHazardDtoChangeCopy.Key_id;               
            })
            var used = hazard.InspectionRooms.some((r) => {
                //console.log(hazard.Hazard_name, r.PendingHazardDtoChangeCopy.New_status, Constants.ROOM_HAZARD_STATUS.IN_USE.KEY, [Constants.ROOM_HAZARD_STATUS.IN_USE.KEY, Constants.ROOM_HAZARD_STATUS.STORED_ONLY.KEY].indexOf(r.PendingHazardDtoChangeCopy.New_status) != -1)
                return [Constants.ROOM_HAZARD_STATUS.IN_USE.KEY, Constants.ROOM_HAZARD_STATUS.STORED_ONLY.KEY].indexOf(r.PendingHazardDtoChangeCopy.New_status) != -1;
            })
            console.log(hazard.Hazard_name, notAnswered, used )
            if (notAnswered) return null;
            if (used) return true;
            return false;
        }

        $scope.conditionallyGetSelectedHazard = function (hazard) {
            $scope.selectedHazard = false;
            if (!$scope.getLeaves(hazard).length) $scope.selectedHazard = hazard;
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

    })
    .controller('HazardVerificationAddModalCtrl', function ($scope, $q, $http, applicationControllerFactory, $modalInstance, convenienceMethods) {
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
