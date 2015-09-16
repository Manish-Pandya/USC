angular
    .module('VerificationApp')
    .controller('AdminCtrl', function ($scope, $rootScope, $modal, applicationControllerFactory, modelInflatorFactory, locationHubFactory, userHubFactory) {
        var ac = $scope.ac = applicationControllerFactory;
        var lf = $scope.lf = locationHubFactory;
        var uf = $scope.uf = userHubFactory;
        $scope.dataStoreManager = dataStoreManager;
    
        $scope.contactOptions  = ["In another PI's lab", "No longer at the university", "Still in this lab, but no longer a contact"];
        $scope.personnelOptions = ["In another PI's lab", "No longer at the university", "Still in this lab, but now a lab contact"];
        $scope.newUser;
        $scope.addedUsers = [];
        var id = 1;

        $rootScope.loading = getVerification(id)
                                .then(getPI).then(getAllUsers).then(uf.getAllRoles).then(uf.getAllUsers);
    

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
                item.PendingUserChangeCopy.New_status = "Added";
                item.PendingUserChangeCopy.Is_active = true;
                item.PendingUserChangeCopy.Name = item.Name;
                $scope.newUser = item;
            }
        }
        
        $scope.openCreateUserModal = function(roleName){
            var user = {Is_active:true, Roles:[], Supervisor_id:$scope.PI.Key_id, Supervisor:$scope.PI, Class:'User', Is_new:true};
            var i = uf.roles.length;
            while(i--){
                if(uf.roles[i].Name.indexOf(roleName)>-1) user.Roles.push(uf.roles[i]);
                if(uf.roles[i].Name.indexOf('Lab Personnel') > -1 && roleName == "Lab Contact") user.Roles.push(uf.roles[i]);
            }
            
            uf.setModalData(user);
            var modalInstance = $modal.open({
                templateUrl: '../views/hubs/userHubPartials/labPersonnelModal.html',
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
