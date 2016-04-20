angular
    .module('VerificationApp')
    .controller('HazardVerificationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;

        $scope.buildingIdx = 0;
        $scope.roomIdx = 0;

        $scope.incrementRoom = function (int) {
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
                             // IDEA: Lets just store key_ids and use dataStoreManager.getById method.
                             // That way, we can selectively push based on array.indexOf
                             var hazard,
                                 leafParentHazards = [],
                                 parentMap = [];
                             var len = hazards.length;
                             for (var n = 0; n < len; n++) {
                                 hazard = hazards[n];
                                 hazard.loadSubHazards();
                                 var parent = dataStoreManager.getById("HazardDto", hazards[n].Parent_hazard_id);
                                 if(parent && hazard.ActiveSubHazards && !hazard.ActiveSubHazards.length  && !parent.pushed){
                                     leafParentHazards.push(parent);
                                     parentMap.push(parent);
                                     parent.pushed = true;
                                 }
                             }
                             //http://erasmus.graysail.com/rsms/src/verification/#/inventory
                             var categorizedHazards = {
                                 "1": [],
                                 "10009": [],
                                 "10010": [],
                             };

                             for (var x = 0; x < leafParentHazards.length; x++) {
                                recurseUpTree(leafParentHazards[x]);
                             }
                             
                             function recurseUpTree(hazard) {
                                 if ( categorizedHazards.hasOwnProperty(hazard.Key_id) || categorizedHazards.hasOwnProperty(hazard.Parent_hazard_id) ) {
                                     if (categorizedHazards.hasOwnProperty(hazard.Parent_hazard_id)) categorizedHazards[hazard.Parent_hazard_id].push(hazard);
                                     return false;
                                 } else {
                                     var p = dataStoreManager.getById("HazardDto", hazard.Parent_hazard_id);
                                     //why is there a hazard with the same key_id as its parent_id???
                                     if (!p || hazard.Key_id == hazard.Parent_hazard_id) return false;                                     
                                     return recurseUpTree(p);
                                 }
                                 return false;
                             }
                             
                            
                             $scope.selectedCategory = 1;
                             $scope.allHazards = categorizedHazards;
                             console.log(id, categorizedHazards);
                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }

    });
