﻿angular
    .module('VerificationApp')
    .controller('HazardVerificationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;

        $scope.categoryIdx = 0;
        $scope.buildingIdx = 0;
        $scope.roomIdx = 0;
        $scope.hazardCategories = [Constants.MASTER_HAZARD_IDS.BIOLOGICAL, Constants.MASTER_HAZARD_IDS.CHEMICAL, Constants.MASTER_HAZARD_IDS.RADIATION];

        $scope.incrementRoom = function (int) {
            if ($scope.categoryIdx + int > -1) {
                if ($scope.categoryIdx + int < $scope.hazardCategories.length) {
                    $scope.categoryIdx += int;
                } else {
                    $scope.categoryIdx = 0;

                    $scope.buildingMax = false;
                    var bldg = $scope.PI.Buildings[$scope.buildingIdx];
                    if ($scope.roomIdx + int > -1) {
                        if ($scope.roomIdx + int < bldg.Rooms.length) {
                            $scope.roomIdx += int;
                        } else {
                            $scope.roomIdx = 0;
                            $scope.buildingIdx++;
                        }
                    }
                    else if ($scope.buildingIdx > 0) {
                        $scope.buildingIdx--;
                        var bldg = $scope.PI.Buildings[$scope.buildingIdx];
                        $scope.roomIdx = bldg.Rooms.length - 1;
                    }
                }
            }
        }
        /*
        $scope.selectRoom = function (idx) {
            var room;
            for(var i = 0; )
                
        }
        */

       // var hazardCategories = [Constants.];

        var id = 1; // TODO: This shouldn't be set here. Just for testing.

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllHazards);
        $scope.dsm = dataStoreManager;

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
                             var hazard,
                                 leafParentHazards = [];
                             var len = hazards.length;
                             for (var n = 0; n < len; n++) {
                                 hazard = hazards[n];
                                 hazard.loadSubHazards();
                                 var parent = dataStoreManager.getById("HazardDto", hazards[n].Parent_hazard_id);
                                 if (parent && hazard.ActiveSubHazards && !hazard.ActiveSubHazards.length && !parent.pushed) {
                                     if (leafParentHazards.indexOf(hazard.Key_id) == -1 && leafParentHazards.indexOf(parent.Key_id) == -1) {
                                         leafParentHazards.push(parent.Key_id);
                                         parent.pushed = true;
                                     }
                                 }
                             }
                             //http://erasmus.graysail.com/rsms/src/verification/#/inventory
                             var categorizedHazards = {};
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.BIOLOGICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.CHEMICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.RADIATION] = [];

                             for (var x = 0; x < leafParentHazards.length; x++) {
                                 recurseUpTree(leafParentHazards[x]);
                             }
                             
                             function recurseUpTree(hazardId) {
                                 var hazard = dataStoreManager.getById("HazardDto", hazardId);
                                 if ( categorizedHazards.hasOwnProperty(hazard.Key_id) || categorizedHazards.hasOwnProperty(hazard.Parent_hazard_id) ) {
                                     if (categorizedHazards.hasOwnProperty(hazard.Parent_hazard_id)) {
                                        categorizedHazards[hazard.Parent_hazard_id].push(hazard);
                                        return false;
                                     }
                                 } else {
                                     var p = dataStoreManager.getById("HazardDto", hazard.Parent_hazard_id);
                                     //why is there a hazard with the same key_id as its parent_id???
                                     if (!p || hazard.Key_id == hazard.Parent_hazard_id) return false;                                 
                                     return recurseUpTree(p);
                                 }
                                 return false;
                             }
                             
                             $scope.allHazards = categorizedHazards;
                             console.log(id, $scope.hazardCategories[$scope.categoryIdx]);
                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }

    });
