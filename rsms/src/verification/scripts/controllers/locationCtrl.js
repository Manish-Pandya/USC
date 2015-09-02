angular
    .module('VerificationApp')
    .controller('LocationCtrl', function ($scope, $rootScope, applicationControllerFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        var id = 1;
    
        $scope.rooms = [];
        $scope.room;

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllBuildings);

        function getVerification(id){
            return ac.getVerification(id)
                    .then(
                        function(){
                            $scope.verification = dataStoreManager.getById("Verification",id);
                            return $scope.verification.Principal_investigator_id;
                        }
                    )
        }

        function getPI(id){
           return ac.getPI(id)
                    .then(
                        function(){
                            $scope.PI = dataStoreManager.getById("PrincipalInvestigator",id);
                            console.log(dataStore);
                        }
                    )
        }
    
        function getAllBuildings(){
           return ac.getAllBuildings()
                    .then(
                        function( buildings ){
                            $scope.allBuildings = buildings;
                        },
                        function(){
                            $scope.error = "Couldn't get the users";
                            return false;
                        }
                    );
        }
    
        $scope.onBuildingSelect = function(item) {
            if (item) $scope.rooms = item.Rooms;
        }
        
        $scope.onRoomSelect = function(item) {
            if (item) {
                item.PendingRoomChangeCopy.New_status = "Added";
                $scope.room = item;
            }
        }

    });
