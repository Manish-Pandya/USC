'use strict';

/**
 * @ngdoc function
 * @name HazardInventory.controller:HazardInventoryCtrl
 * @description
 * # HazardInventoryCtrl
 * Controller of the HazardInventory Hazard Hub
 */
angular.module('HazardInventory')
    .controller('HazardInventoryCtrl', function ($scope, $q, $http, applicationControllerFactory, $modal, $location, $rootScope, convenienceMethods) {

        //do we have access to action functions?
        $scope.af = applicationControllerFactory;
        $scope.convenienceMethods = convenienceMethods;

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
            getHazards = function (id, roomIds) {
                return af
                    .getAllHazardDtos(id, roomIds)
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
                            hazards.forEach(function (h) {
                                if (h.HasChildren) h.loadSubHazards();
                            })
                        },
                        function () {
                            $scope.error = 'Couldn\'t find the right hazards.'
                        }

                    ).then(
                        af.getBuildings(id).then(function (rooms) {; $scope.PI.Rooms = rooms; })
                    );

            }

        $scope.piPromise =
            getAllPIs()
            .then(
                function (pis) {
                    $scope.pis = pis;
                    console.log(window.location.hash);
                    if ($location.search()) {
                        if ($location.search().pi) {
                            var pi = dataStoreManager.getById("PrincipalInvestigator", $location.search().pi);
                            if (window.location.hash.indexOf("room") != -1) {
                                console.log($location.search());
                                $rootScope.selectedRoomIds = $location.search()["room[]"];
                                var roomIds =[];

                                if(!Array.isArray($location.search()["room[]"])) {
                                    roomIds =[$location.search()["room[]"]]
                                } else {
                                      roomIds = $location.search()["room[]"];
                                }   
                                $scope.onSelectPi(pi, roomIds);
                            } else {
                                $scope.onSelectPi(pi);
                            }
                        }
                    }
                }
            );


        $scope.onSelectPi = function (pi, roomIds) {
            pi.loadInspections();
            $scope.PI = pi;
            console.log("PI Selected: ",pi)
            if (!roomIds) roomIds = false;
            $scope.hazardPromise = getHazards(pi.Key_id, roomIds);
            $scope.selectPI = false;
            $location.search("pi", pi.Key_id);
            if (!roomIds && $location.search()["room[]"]) {
                $location.search("room[]", false);
                $rootScope.selectedRoomIds = null;
            }
        }

        $scope.getShowRooms = function (hazard, room, building) {
            return hazard.InspectionRooms.some(function (r) { return r.ContainsHazard || r.OtherLab }) && hazard.InspectionRooms.some(function (r) { return !r.ContainsHazard && !r.OtherLab });
        }

        $scope.openSubsModal = function (hazard, parent) {
            hazard.loadSubHazards();
            var modalData = {};
            modalData.Hazard = hazard;
            modalData.Parent = parent;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/sub-hazards-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }

        $scope.openRoomsModal = function (hazard, masterHazard) {
            hazard.loadSubHazards();
            var modalData = {};
            modalData.Hazard = hazard;
            if (masterHazard) modalData.GrandParent = masterHazard;

            modalData.Parent = dataStoreManager.getById("HazardDto", hazard.Parent_hazard_id);

            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/rooms-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }

        $scope.openMultiplePIsModal = function (hazardDto, room) {
            var modalData = {};
            if (!room) {
                var room = false;
            } else {
                modalData[room.Class] = room;
            }
            if (!hazardDto) {
                var hazardDto = false;
            } else {
                modalData.HazardDto = hazardDto;
            }
            modalData.PI = $scope.PI;
            $scope.pisPromise = af.getPIs(hazardDto, room)
                .then(function (pis) {
                    modalData.PIs = pis;
                    af.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: 'views/modals/multiple-PIs-modal.html',
                        controller: 'HazardInventoryModalCtrl'
                    });
                })

        }

        $scope.openMultiplePIHazardsModal = function (hazardDto, room) {
            var modalData = {};
            modalData.HazardDto = hazardDto;
            modalData.PI = $scope.PI;
            if (!room) room = null;
            $scope.pisPromise = af.getPiHazards(hazardDto, $scope.PI.Key_id, room)
                .then(function (pHRS) {
                    modalData.pHRS = pHRS;
                    af.setModalData(modalData);
                    var modalInstance = $modal.open({
                        templateUrl: 'views/modals/multiple-PI-hazards-modal.html',
                        controller: 'HazardInventoryModalCtrl'
                    });
                })

        }

        $scope.openNotes = function () {
            var modalData = {};
            modalData.PI = $scope.PI;
            modalData.isComments = true;
            af.setModalData(modalData);            
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/inspection-notes-modal.html',
                controller: 'CommentsCtrl'
            });

            modalInstance.result.then(function () {

            });
        }

        $scope.openPreviousInspections = function () {
            var modalData = {};
            modalData.PI = $scope.PI;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/archived-reports.html',
                controller: 'HazardInventoryModalCtrl'
            });

            modalInstance.result.then(function () {

            });
        }

        $scope.startInspection = function () {
            var modalData = {};
            modalData.PI = $scope.PI;
            modalData.openInspections = true;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/open-inspections.html',
                controller: 'HazardInventoryModalCtrl'
            });

            modalInstance.result.then(function () {

            });
        }

        $scope.isHazardBiosafetyCabinets = function isHazardBiosafetyCabinets (hazard) {
            if(!hazard){
                return false;
            }

            // TODO: Logically determine this based on data other than name
            return hazard.Hazard_name == 'Biosafety Cabinets';
        };

        $scope.getDisabled = function (hazard) {
            if(!dataStore.HazardDto)return false;
            var parent = dataStoreManager.getById("HazardDto", hazard.Parent_hazard_id);
            if (Constants.BRANCH_HAZARD_IDS.indexOf(hazard.Parent_hazard_id) < 0 && (parent.Stored_only || parent.BelongsToOtherPI)) {
                return true;
            }

            hazard.loadSubHazards();
            if (!hazard.ActiveSubHazards) return false;
            var subs = hazard.ActiveSubHazards;
            for (var i = 0; i < subs.length; i++) {
                if (subs[i].IsPresent || subs[i].Stored_only) {
                    return true;
                }
            }
            return false;
        }

        $scope.evaluateStoreOnly = function (h) {
            var parent = dataStoreManager.getById("HazardDto", h.Parent_hazard_id);
            /*
                hazard is stored only if all of its rooms 
                OR its parents room either don't contain the hazard or have a stored only status
                AND it is present in at least one room
             */

            if (h.IsPresent && (parent.InspectionRooms.every(function (r) { return r.Status == Constants.ROOM_HAZARD_STATUS.STORED_ONLY.KEY || !r.ContainsHazard })
                ||
                h.InspectionRooms.every(function (r) { return r.Status == Constants.ROOM_HAZARD_STATUS.STORED_ONLY.KEY || !r.ContainsHazard }))) {
                return h.Stored_only = true;
            }
            return false;
        }

        $rootScope.getGrayed = function (arr) {
            if (!$rootScope.selectedRoomIds) return;
            return arr.every(function (r) {
                return $rootScope.selectedRoomIds.indexOf(r.Key_id) == -1
            })
        }

        $scope.filterRooms = function () {
            var modalData = { };
            modalData.PI = $scope.PI;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/room-selection.html',
                controller: 'RoomFilterController'
            });

            modalInstance.result.then(function (r) {
                if (r) {
                    $location.search()["room[]"] = $rootScope.selectedRoomIds = r;
                    $scope.onSelectPi($scope.PI, $rootScope.selectedRoomIds);
                }
            });
        }

        $scope.openBiosafetyCabinetInfoModal = function (pi) {
            console.log(pi);
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/equipment-info.html',
                controller: 'EquipmentInfoCtrl',
                resolve: {
                    cabs: function () {
                        var url = '../ajaxaction.php?action=getCabinetsByPi&id=' + pi.Key_id + '&callback=JSON_CALLBACK';

                        return $scope.loadingCabs = $q.all([convenienceMethods.getDataAsDeferredPromise(url)]).then(
                            function (cabs) {
                                return [pi,cabs[0]];
                            });
                    }
                    
                }
            });
        }

    })
    .controller('HazardInventoryModalCtrl', function ($scope, convenienceMethods, $rootScope, $q, $http, applicationControllerFactory, $modalInstance, $modal, roleBasedFactory) {
        $scope.constants = Constants;
        $scope.convenienceMethods = convenienceMethods;
        var af = applicationControllerFactory;
        var rbf = roleBasedFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.dataStoreManager = dataStoreManager;
        $scope.USER = GLOBAL_SESSION_USER;

        if ($rootScope.PrincipalInvestigatorsBusy) {
            $scope.modalData.inspectionsPendings = true;
            $rootScope.PrincipalInvestigatorsBusy.then(function () {
                $scope.modalData.hasNoOpenInspections = true;
                setTimeout(function () {
                    $scope.modalData.inspectionsPendings = false;
                    $scope.$apply();                    
                }, 10);
                $scope.pi = $scope.modalData.PI;
                for (var i = 0; i < $scope.modalData.PI.Inspections.length; i++) {
                    var insp = $scope.modalData.PI.Inspections[i];
                    if (!insp.Date_closed) {
                        $scope.modalData.hasNoOpenInspections = true;
                        break;
                    }
                }
            })
        }

        function openSecondaryModal(modalData) {
            $modalInstance.dismiss();
            setTimeout(function () {
                modalData.inspectorIds = [];
                for (var i = 0; i < modalData.Inspection.Inspectors.length; i++) {
                    modalData.inspectorIds[modalData.Inspection.Inspectors[i].Key_id] = modalData.Inspection.Inspectors[i].Key_id;
                }
                if (modalData.inspectorIds.indexOf(rbf.getUser().Inspector_id) < 0) modalData.inspectorIds[rbf.getUser().Inspector_id] = rbf.getUser().Inspector_id;

                af.setModalData(modalData);
                var modalInstance = $modal.open({
                    templateUrl: 'views/modals/inspection-changes.html',
                    controller: 'SecondaryModalController'
                });
            }, 500);            
        }

        $scope.validateInspection = function (inspection) {
            $scope.loading = af.getAllInspectors().then(function (allInspectors) {
                var inspectors = inspection.Inspectors;
                var inspectorIncluded = checkInspectors(inspection, rbf.getUser());

                var modalData = { "Inspection": inspection };
                modalData.current = rbf.getUser();
                modalData.allInspectors = dataStoreManager.get("Inspector");

                var moreThanOne = inspection.Inspectors.length > 1;
                if (inspectorIncluded) {
                    if (moreThanOne) {
                        //open modal
                        modalData.message = "There are multiple inspectors assigned for this lab.  All assigned inspectors will appear as auditors in the inspection report.  Make any necessary changes to assigned inspectors below before continuing to the checklist.";
                    } else {
                        af.navigateToInspection(inspection);
                        return false;
                    }
                } else {
                    modalData.message = "You are not assigned as an inspector for this lab.  Continuing will add you as an auditor in the inspection report.  Make any necessary changes to assigned inspectors below before continuing.";
                }
                modalData.PI = $scope.modalData.PI;
                openSecondaryModal(modalData);
                return false;
            })
            
        }

        $scope.checkRad = function (pi, id) {
            $scope.confirmer = {};
            $scope.confirmer.needsRadConfirmation = false;
            if (GLOBAL_SESSION_ROLES.userRoles.indexOf(Constants.ROLE.NAME.RADIATION_INSPECTOR) > -1) {
                af.initialiseInspection(pi, id, false, true);
            } else {
                $scope.confirmer.needsRadConfirmation = true;
            }

        }

        $scope.checkSafetyInspector = function (pi, id) {
            $scope.confirmer = {};
            $scope.confirmer.needsConfirmation = false;
            if (GLOBAL_SESSION_ROLES.userRoles.indexOf(Constants.ROLE.NAME.SAFETY_INSPECTOR) > -1) {
                af.initialiseInspection(pi, id, false, false);
            } else {
                $scope.confirmer.needsConfirmation = true;
            }

        }


        var checkInspectors = function (inspection, currentUser) {
            if (!currentUser.Inspector_id) return false;
            var is = false;
            for (var i = 0; i < inspection.Inspectors.length; i++) {
                if (currentUser.Inspector_id == inspection.Inspectors[i].Key_id) {
                    return true;
                }
            }

            return false;
        }


        $scope.processRooms = function (inspection, rooms) {
            if (inspection.processed) return;

            for (var j = 0; j < inspection.Rooms.length; j++) {
                inspection.Rooms[j][inspection.Key_id + "checked"] = true;
            }
            for (var k = 0; k < rooms.length; k++) {
                if (!convenienceMethods.arrayContainsObject(inspection.Rooms, rooms[k])) {
                    inspection.Rooms.push(rooms[k]);
                }
            }
            inspection.processed = true;
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

        $scope.sortRooms = function (rooms) {
            _.sortBy(rooms, ["Building_name", "Room_name"]);
            return rooms;
        }

        


    })
    .controller('SecondaryModalController', function ($scope, $q, $http, applicationControllerFactory, $modalInstance, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var af = applicationControllerFactory;
        var rbf = roleBasedFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.dataStoreManager = dataStoreManager;
        $scope.addInspector = function (int) {
            $scope.modalData.inspectorIds[int] = int;
        }
        $scope.removeInspector = function (int) {
            $scope.modalData.inspectorIds[int] = null;
        }
        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }


    })
    .controller('RoomFilterController', function ($scope, applicationControllerFactory, $modalInstance, $rootScope) {
        $scope.constants = Constants;
        var af = applicationControllerFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        $scope.getChecked = function (rooms) {
            return rooms.every(function (r) {                
                return $rootScope.selectedRoomIds.indexOf(r.Key_id) != -1;
            })
        }

        $scope.handleBuildingSelect = function (bldg, room) {
            bldg.forEach(function(r) { r.checked = room.allChecked})
        }

        $scope.close = function (rooms) {
            if(rooms) {
                var roomIds =[];
                rooms.forEach(function (r) {
                    if(r.checked) roomIds.push(r.Key_id)
                })
                $modalInstance.close(roomIds);
            } else {
                $modalInstance.dismiss();
            }
        }

    })
    .controller('EquipmentInfoCtrl', function ($scope, $modalInstance, cabs) {
        console.log(cabs);
        $scope.cabs = cabs[1];
        $scope.pi = cabs[0];
        console.log($scope.pi);
        $scope.close = function () {            
            $modalInstance.dismiss();            
        }

});
    function CommentsCtrl($scope, $modalInstance, convenienceMethods, $q, applicationControllerFactory, roleBasedFactory) {

    $scope.tinymceOptions = {
                            plugins: 'link lists',
                                toolbar: 'bold | italic | underline | link | lists | bullist | numlist',
        menubar: false,
        elementpath: false,
        content_style: "p,ul li, ol li {font-size:14px}"
        };

    $scope.constants = Constants;
        var af = applicationControllerFactory;
    var rbf = roleBasedFactory;
    $scope.af = af;
    $scope.modalData = af.getModalData();
    $scope.dataStoreManager = dataStoreManager;
    $scope.USER = GLOBAL_SESSION_USER;

    $scope.pi = $scope.modalData.PI;
    $scope.piCopy = {
            Key_id: $scope.pi.Key_id,
            Is_active: $scope.pi.Is_active,
            User_id: $scope.pi.User_id,
            Inspection_notes: $scope.pi.Inspection_notes,
        Class : "PrincipalInvestigator"
        };

        $scope.close = function () {
        $modalInstance.dismiss();
        };

            $scope.edit = function (state) {
    $scope.pi.editNote = state;
    }

    $scope.saveNote = function () {
        $scope.savingNote = true;
    $scope.error = null;
    $scope.close();

    af.savePI($scope.pi, $scope.piCopy)
      .then(
            function (returnedPi) {
            $scope.savingNote = false;
            $scope.pi.editNote = false;
            $scope.pi.Inspection_notes = returnedPi.Inspection_notes;
            },
            function () {
            $scope.savingNote = false;
            $scope.error = "The Inspection Comments could not be saved.  Please check your internet connection and try again."
            }
          )
    }

}
