angular
    .module('VerificationApp')
    .controller('AdminCtrl', function ($scope, $rootScope, $modal, applicationControllerFactory, modelInflatorFactory, userHubFactory, locationHubFactory) {
        var ac = $scope.ac = applicationControllerFactory;
        var uf = $scope.uf = userHubFactory;
        var lf = $scope.lf = locationHubFactory;
        
        $scope.dataStoreManager = dataStoreManager;
        
        $scope.contactOptions = [Constants.PENDING_CHANGE.USER_STATUS.MOVED_LABS, Constants.PENDING_CHANGE.USER_STATUS.LEFT_UNIVERSITY, Constants.PENDING_CHANGE.USER_STATUS.NO_LONGER_CONTACT];
        $scope.personnelOptions = [Constants.PENDING_CHANGE.USER_STATUS.MOVED_LABS, Constants.PENDING_CHANGE.USER_STATUS.LEFT_UNIVERSITY, Constants.PENDING_CHANGE.USER_STATUS.NOW_A_CONTACT];
        $scope.newUser;
        $scope.addedUsers = [];
        var id = 1;
    
        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllUsers).then(uf.getAllRoles);
    

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
                item.PendingUserChangeCopy.New_status = "Added"; // TODO: "Added" isn't a valid status for a pendingUserChange. What gives?
                item.PendingUserChangeCopy.Is_active = true;
                item.PendingUserChangeCopy.Name = item.Name;
                $scope.newUser = item;
            }
        }
        
        $scope.openCreateUserModal = function(roleName) {
            var user = {Is_active:true, Roles:[], Class:'User', Supervisor_id:$scope.PI.Key_id, Supervisor:$scope.PI, Is_new:true};
            var i = uf.roles.length;
            while(i--){
                if(uf.roles[i].Name.indexOf(roleName)>-1) user.Roles.push(uf.roles[i]);
                if(uf.roles[i].Name.indexOf(Constants.ROLES.NAME.LAB_PERSONNEL)>-1) var labPersonnel = uf.roles[i];
            }
            if(roleName == Constants.ROLES.NAME.LAB_CONTACT) user.Roles.push(labPersonnel);
            
            // Prevent circular structure by removing user.Supervisor.LabPersonnel
            //user.Supervisor.LabPersonnel = user.Supervisor.Buildings = user.Supervisor.CurrentVerifications = user.Supervisor.Pi_authorization = user.Supervisor.User = null;
            console.log(roleName, user);
            
            uf.setModalData(user);
            var modalInstance = $modal.open({
              templateUrl: '../views/hubs/userHubPartials/labContactModal.html',
              controller: modalCtrl
            });
            modalInstance.result.then(function (returnedUser) {
              if(user.Key_id){
                angular.extend(user, returnedUser)
              }else{
                uf.users.push(returnedUser);
              }
            });
        }

    });
