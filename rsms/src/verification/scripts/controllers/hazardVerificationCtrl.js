angular
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
                                
                                 if (hazard.ActiveSubHazards && !hazard.ActiveSubHazards.length) {
                                    
                                     if (leafParentHazards.indexOf(hazard.Parent_hazard_id) == -1) {
                                         leafParentHazards.push(hazard.Parent_hazard_id);
                                     }
                                 }
                             }
                             //http://erasmus.graysail.com/rsms/src/verification/#/inventory
                             var categorizedHazards = {};
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.BIOLOGICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.CHEMICAL] = [];
                             categorizedHazards[Constants.MASTER_HAZARD_IDS.RADIATION] = [];
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
                                         idsMap.push(hazard.Key_id)
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
                             console.log(id, $scope.hazardCategories[$scope.categoryIdx]);
                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }

    });
