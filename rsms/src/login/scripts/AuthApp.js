angular
    .module('ng-AuthApp', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'angular.filter',
    'ui.router',
    'ui.select', 'ngSanitize',
    'ngAnimate'
])
.config(function ($stateProvider, $urlRouterProvider) {
    console.debug("ng-AuthApp configuring...");

    if( window.user_access_request ){
        $urlRouterProvider.otherwise('/request-access');
    }
    else {
        $urlRouterProvider.otherwise('/login');
    }

    $stateProvider
        .state('login', {
            url: '/login',
            templateUrl: `views/login.html`,
            controller: function ($scope, AuthAPI){
                console.debug("login ctrl");
                $scope.data = { username:'', password:'' };

                $scope.login = function(){
                    let willLogIn = AuthAPI.login( $scope.data );
                    $scope.data = { username:'', password:'' };

                    return willLogIn;
                }
            }
        })
        .state('request-access', {
            url: '/request-access',
            templateUrl: 'views/request-access.html',
            controller: function($scope, $timeout, AuthAPI ){
                console.debug("request-access ctrl");

                $scope.data = {
                    candidate: window.user_access_request,
                    listing: [],

                    selection: {
                        username: null,
                        department: null,
                        pi: null,
                        submitting: false
                    }
                };

                // Load data
                AuthAPI.getNewUserDepartmentListing()
                .then(listing => {
                    $timeout(function(){
                        $scope.data.listing = listing;
                    });
                });

                // Scope functions
                $scope.submitRequest = function(){
                    if( !$scope.data.selection.pi.Key_id ){
                        // Invalid request; we need a PI's ID
                        return;
                    }

                    $scope.data.selection.submitting = true;

                    AuthAPI.submitRequest( $scope.data.selection.pi.Key_id )
                    .then(
                        result => {
                            console.info("Access-request result:", result);

                            $scope.data.selection.submitting = false;
                        },
                        error => {
                            console.error("Error submitting access request", error);

                            $scope.data.selection.submitting = false;
                        });
                }
            }
        });
})
.controller('AuthAppCtrl', function($rootScope, $state){
    console.debug("ng-AuthApp running");

    $rootScope.GLOBAL_WEB_ROOT = GLOBAL_WEB_ROOT;
    $rootScope.auth_errors = auth_errors;

    if( window.user_access_request ){
        $state.go('request-access');
    }
})
.factory('AuthAPI', function( $http ){
    return {
        endpoint_url: GLOBAL_WEB_ROOT + 'ajaxaction.php',
        api_headers: {'Content-Type': 'application/json' },
        formdata_headers: {'Content-Type': 'application/x-www-form-urlencoded' },

        getNewUserDepartmentListing: async function (){
            let cfg = {
                method:'GET',
                url: this.endpoint_url + '?action=getNewUserDepartmentListing',
            };

            try {
                let resp = await $http(cfg);
                return resp.data;
            }
            catch(error){
                console.error("Error loading department listing", error);
            }

        },

        login: function login(formData){
            if( !formData.action ){
                formData.action = 'loginAction';
            }

            let requestOptions = {
                method: 'POST',
                url: this.endpoint_url,
                headers: this.formdata_headers,
                data: $.param(formData),
            };

            $http( requestOptions)
            .success( (data, status, headers, config) => {
                console.log("LOGIN SUCCESS", data, status, config);
            })
            .error( (data, status, headers, config) => {
                console.error("LOGIN ERROR", data, status, config);
            });
        },

        submitRequest: async function( pi_id ){
            let formData = {
                action: 'submitAccessRequest',
                pi_id: pi_id
            };

            let requestOptions = {
                method: 'POST',
                url: this.endpoint_url,
                headers: this.formdata_headers,
                data: $.param(formData),
            };

            $http( requestOptions)
            .success( (data, status, headers, config) => {
                console.log("SUBMISSION SUCCESS", data, status, config);

                // if access request is saved, display its status
                // if access request isn't saved, display error and go nowhere
            })
            .error( (data, status, headers, config) => {
                console.error("SUBMISSION ERROR", data, status, config);
            });
        }
    };
});