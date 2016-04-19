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
                             
                             $scope.allHazards = leafParentHazards;
                             console.log(id, leafParentHazards);
                         },
                         function () {
                             $scope.error = "Couldn't get the hazards";
                             return false;
                         }
                     );
        }

    });
