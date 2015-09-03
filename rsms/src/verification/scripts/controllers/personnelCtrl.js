angular
    .module('VerificationApp')
    .controller('PersonnelCtrl', function ($scope, $rootScope, applicationControllerFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;
    
        $scope.contactOptions  = ["In another PI's lab", "No longer at the university", "Still in this lab, but no longer a contact"];
        $scope.personnelOptions = ["In another PI's lab", "No longer at the university", "Still in this lab, but now a lab contact"];
        $scope.newUser;
        $scope.addedUsers = [];
        var id = 1;

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllUsers);
    

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
                        }
                    )
        }
    
        function getAllUsers(){
           return ac.getAllUsers()
                    .then(
                        function( users ){
                            $scope.allUsers = users;
                        },
                        function(){
                            $scope.error = "Couldn't get the users";
                            return false;
                        }
                    );
        }
    
        $scope.onUserSelect = function(item) {
            if (item) {
                item.PendingUserChangeCopy.New_status = "Added";
                $scope.newUser = item;
            }
        }

    });
