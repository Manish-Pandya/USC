angular
    .module('VerificationApp')
    .controller('PersonnelCtrl', function ($scope, $rootScope, applicationControllerFactory, modelInflatorFactory, $stateParams) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        $scope.dataStoreManager = dataStoreManager;
    
        $scope.contactOptions = [Constants.PENDING_CHANGE.USER_STATUS.MOVED_LABS, Constants.PENDING_CHANGE.USER_STATUS.LEFT_UNIVERSITY, Constants.PENDING_CHANGE.USER_STATUS.NO_LONGER_CONTACT];
        $scope.personnelOptions = [Constants.PENDING_CHANGE.USER_STATUS.MOVED_LABS, Constants.PENDING_CHANGE.USER_STATUS.LEFT_UNIVERSITY, Constants.PENDING_CHANGE.USER_STATUS.NOW_A_CONTACT];
        $scope.newUser;
        $scope.addedUsers = [];
        var id = $stateParams.id;

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
                if(!item.PendingUserChangeCopy)item.PendingUserChangeCopy = modelInflatorFactory.instantiateObjectFromJson(new window.PendingUserChange);
                item.PendingUserChangeCopy.New_status = Constants.PENDING_CHANGE.USER_STATUS.ADDED;
                item.PendingUserChangeCopy.Is_active = true;
                item.PendingUserChangeCopy.Name = item.Name;
                $scope.newUser = item;
            }
        }

    });
