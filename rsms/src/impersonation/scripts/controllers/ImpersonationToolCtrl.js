'use strict';

angular.module('ng-ImpersonationTool')
    .controller('ImpersonationToolCtrl', function($rootScope, $scope, $q, $stateParams){
        console.debug("ImpersonationToolCtrl running");

        $scope.ImpersonationSessionActive = window.ImpersonationSessionActive
        if( $scope.ImpersonationSessionActive ){
            return;
        }

        // Load users
        $scope.loadImpersonatableUsers = function loadImpersonatableUsers( pageNum ){
            var segment = 'getImpersonatableUsernames';
            $scope.busyMessage = "Loading impersonatable users";
            $scope.loading = $q.all([
                XHR.GET(segment).then( users => {
                    $scope.ImpersonatableUsers = users.filter(u => u.Username);

                    return $scope.ImpersonatableUsers;
                })
            ]);
        };

        $scope.selectUser = function selectUser( user ){
            console.debug("Selecting user ", user);
            $scope.ImpersonateUser = user;
        };

        $scope.impersonateUser = function impersonateUser(){
            if( !$scope.ImpersonateUser ){
                return;
            }

            var username = $scope.ImpersonateUser.Username;
            console.log("Impersonate ", username);
            var segment = 'impersonateUserAction&impersonateUsername=' + username;
            $scope.busyMessage = "Impersonating " + username;
            $scope.loading = $q.all([
                XHR.POST(segment).then(
                    success => {
                        console.log("Impersonation successful");
                        $scope.ImpersonationSuccessful = true;
                        setTimeout($scope.goToDefaultPage, 3000);
                    },
                    failure => {
                        $scope.ImpersonationSuccessful = false;
                        console.error("Error impersonating user", failure);
                    }
                )
            ]);
        };

        $scope.goToDefaultPage = function goToDefaultPage(){
            window.location = defaultPage;
        }

        $scope.loadImpersonatableUsers();
    });