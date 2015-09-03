angular
    .module('VerificationApp')
    .controller('LocationCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;
        var id = 1;
    
        $scope.rooms = [];
        $scope.room;

        $rootScope.loading = getVerification(id)
                                .then(getPI)
                                .then(getAllBuildings);

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
            $scope.selectedBuilding = item;
        }
        
        $scope.onRoomSelect = function(item) {
            if (item) {
                
                if(!item.PendingRoomChangeCopy)item.PendingRoomChangeCopy = modelInflatorFactory.instantiateObjectFromJson(new window.PendingRoomChange);
                
                item.PendingRoomChangeCopy.New_status = "Added";
                item.PendingRoomChangeCopy.Is_active = true;
                item.PendingRoomChangeCopy.Name = item.Name
                item.Building_name = dataStoreManager.getById("Building", item.Building_id).Name;
                $scope.room = item;
            }
        }

    });
