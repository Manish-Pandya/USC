'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:HazardHubCtrl
 * @description
 * # HazardHubCtrl
 * Controller of the 00RsmsAngularOrmApp Hazard Hub
 */
angular.module('00RsmsAngularOrmApp')
    .filter("everyThirdChecked", function () {
        return function (hazards) {
            if (!hazards) return;

            var i = hazards.length;
            while (i--) {
                var h = hazards[i];
                if ((i == 0 || i % 3 == 0)) {
                    h.checked = true;
                }
                if(i % 3 == 0 && i % 5 == 0){
                    h.Status = "Stored Only";
                }
            }

            return hazards;
        }
    })
    .controller('HazardInventoryCtrl', function ($scope, $q, $http, actionFunctionsFactory, $modal) {

        //do we have access to action functions?
        $scope.af = actionFunctionsFactory;
        var af = actionFunctionsFactory;

        var getAllPIs = function () {
                return actionFunctionsFactory
                    .getRadPIById(1)
                    .then(function (pis) {
                            //we have to set this equal to the promise rather than the getter, because the getter will return a promise, and that breaks the typeahead because of a ui-bootstrap bug
                            return pis;
                        },
                        function () {
                            $scope.error = 'There was a problem getting the list of Principal Investigators.  Please check your internet connection.'
                        });
            },
            getHazards = function () {
                return actionFunctionsFactory
                    .getAllHazardDtos()
                    .then(
                        function (hazards) {
                            return hazards
                        },
                        function () {
                            $scope.error = 'Couldn\'t get all hazards.'
                        }
                    ).then(
                        function (hazards) {
                            console.log(dataStore);
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

                    );

            },
            setInspection = function (pi) {
                //fill the PI select field with the selected PI's name
                $scope.customSelected = pi.User.Name;

                //now that we have a PI, we can initialize the inspection
                var PIKeyID = pi.Key_id;

                //todo:  when we do user siloing, give the user a way to add another inspection
                //dummy value for inspector ids
                inspectorIds = [1];

                //if we are accessing an inspection that has already been started, we get it's get ID from the $location.search() property (AngularJS hashed get param)
                if ($location.search().inspectionId) {
                    inspectionId = $location.search().inspectionId
                } else {
                    inspectionId = '';
                }

                //set up our $q object so that we can either return a promise on success or break the promise chain on error
                var inspectionDefer = $q.defer();

                hazardInventoryFactory
                    .initialiseInspection(PIKeyID, inspectorIds, inspectionId)
                    .then(function (inspection) {
                            //set our get params so that this inspection can be quickly accessed on page reload
                            $location.search('inspectionId', inspection.Key_id);
                            $location.search("pi", inspection.PrincipalInvestigator.Key_id);

                            //set up our list of buildings
                            $scope.buildings = hazardInventoryFactory.parseBuildings(inspection.Rooms);

                            //set our inspection scope object
                            $scope.inspection = inspection;

                            //we return the inspection's rooms so that we can query for hazards
                            inspectionDefer.resolve(inspection.Rooms);
                        },
                        function (noRooms) {
                            if (noRooms) {
                                //there was no error, but this PI doesn't have any rooms, so we can't inspect
                                $scope.noRoomsAssigned = true;
                            } else {
                                $scope.error = "There was a problem creating the Inspection.  Please check your internet connection and try selecting a Principal Investigator again.";
                            }
                            //call our $q object's reject method to break the promise chain
                            inspectionDefer.reject();
                        });

                return inspectionDefer.promise;
            },
            resetInspectionRooms = function (roomIds, inspectionId) {
                //set up our $q object so that we can either return a promise on success or break the promise chain on error
                var resetInspectionDefer = $q.defer();
                $scope.hazards = [];
                $scope.hazardsLoading = true;
                hazardInventoryFactory
                    .resetInspectionRooms(roomIds, inspectionId)
                    .then(function (hazards) {
                            if (!hazards.InspectionRooms) hazards.InspectionRooms = [];
                            $scope.hazards = hazards.ActiveSubHazards;
                            $scope.hazardsLoading = false;
                            $scope.needNewHazards = false;
                            //angular.forEach($scope.hazards, function(hazard, key){
                            //if(hazard.IsPresent)$scope.getShowRooms(hazard);
                            //});

                            resetInspectionDefer.resolve(hazards);
                        },
                        function () {
                            $scope.error = 'There was a problem getting the new list of hazards.  Please check your internet connection and try again.';
                            resetInspectionDefer.reject();
                        });
                return resetInspectionDefer.promise;
            },
            initiateInspection = function (piKey_id) {
                //start our inspeciton creation/load process
                //chained promises to get a PI, Inspection, and Hazards
                getPi(piKey_id)
                    .then(setInspection)
                    .then(getHazards);
            };
        $scope.piPromise = getAllPIs()
            .then(
                function (pis) {
                    $scope.pis = pis;
                    $scope.PI = dataStoreManager.getById("PrincipalInvestigator",1)
                    $scope.hazardsPromise = getHazards();
                }
            );


        $scope.onSelectPi = function (pi) {
            $scope.pi = pi;
        }

        //local functions for ordering hazards.  in controller because it's only for the view ordering
        $scope.order = function (hazard) {
            return parseFloat(hazard.Order_index);
        }

        $scope.name = function (hazard) {
            return parseFloat(hazard.getName());
        }

        //view filter for displaying hazards with the matching Is_active state
        $scope.hazardFilter = function (hazard) {
            if ($scope.hazardFilterSetting.Is_active == 'both') {
                return true;
            } else if ($scope.hazardFilterSetting.Is_active == 'active') {
                if (hazard.Is_active == true) return true;
            } else if ($scope.hazardFilterSetting.Is_active == 'inactive') {
                if (hazard.Is_active == false) return true;
            }
            return false;
        }

        //view filter for displaying hazards with the matching Is_active state
        $scope.hazardFilter = function (hazard) {
            if ($scope.hazardFilterSetting.Is_active == 'both') {
                return true;
            } else if ($scope.hazardFilterSetting.Is_active == 'active') {
                if (hazard.Is_active == true) return true;
            } else if ($scope.hazardFilterSetting.Is_active == 'inactive') {
                if (hazard.Is_active == false) return true;
            }
            return false;
        }

        $scope.openSubsModal = function (hazard) {
            hazard.loadSubHazards();
            var modalData = {};
            modalData.Hazard = hazard;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'hazard-inventory-modals/sub-hazards-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }

        $scope.openRoomsModal = function (hazard) {
            hazard.loadSubHazards();
            hazard.InspectionRooms = $scope.PI.Rooms;
            var modalData = {};
            modalData.Hazard = hazard;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'hazard-inventory-modals/rooms-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }

        $scope.openMultiplePIsModal = function (hazard) {
            hazard.loadSubHazards();
            hazard.InspectionRooms = $scope.PI.Rooms;
            var modalData = {};
            modalData.Hazard = hazard;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'hazard-inventory-modals/pis-modal.html',
                controller: 'HazardInventoryModalCtrl'
            });
        }


    })
    .controller('HazardInventoryModalCtrl', function ($scope, $q, $http, actionFunctionsFactory, $modalInstance) {
        var af = actionFunctionsFactory;
        $scope.modalData = af.getModalData();

        $scope.pis = [{
            Name: 'Shayne Barlow'
        }, {
            Name: 'Maria "Marj" Pena'
        }, {
            Name: 'Jeffery Twiss'
        }];

        $scope.close = function () {
            $modalInstance.dismiss();
            af.deleteModalData();
        }
    });
