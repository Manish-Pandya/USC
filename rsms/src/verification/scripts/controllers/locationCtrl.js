angular
    .module('VerificationApp')
    .controller('LocationCtrl', function ($scope, $rootScope, applicationControllerFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        var id = 1;
    
        $scope.rooms = [];
        $scope.room;
        $scope.addedRooms = [];

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllBuildings).then(getAllAddedRooms);

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
    
        function getAllAddedRooms(){
            var v = ac.getCachedVerification();
            for(var i = 0; i < v.PendingRoomChanges.length; i++){
                var pendingChange = v.PendingRoomChanges[i];
                if (pendingChange.New_status == "Added") {
                    var r = dataStoreManager.getById("Room", pendingChange.Parent_id);
                    // set the building
                    r.Building = dataStoreManager.getById("Building", r.Building_id);
                    
                    $scope.addedRooms.push(r);
                    console.log(r);
                }
            }
            return $scope.addedRooms;
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
