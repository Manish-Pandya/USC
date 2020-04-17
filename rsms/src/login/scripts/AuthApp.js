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
            }
        })
        .state('request-access', {
            url: '/request-access',
            templateUrl: 'views/request-access.html',
            controller: function($scope, $state, $timeout, $filter, AuthAPI ){
                console.debug("request-access ctrl");

                if( !window.user_access_request ){
                    $state.go('login');
                    return;
                }

                $scope.data = {
                    candidate: window.user_access_request,
                    listing: [],

                    selection: {
                        username: null,
                        department: null,
                        pi: null,

                        submitting: false,
                        submission_complete: false
                    }
                };

                // Look at candidate's 'current' request, if any
                // Load data if we can submit a new request
                if( !$scope.data.candidate.Current_access_request || $scope.data.candidate.Current_access_request.Status != 'PENDING' ){
                    console.debug("Candidate has no active reqeusts; load listing data");
                    AuthAPI.getNewUserDepartmentListing()
                    .then(listing => {
                        $timeout(function(){
                            console.debug("Listing data loaded");
                            $scope.data.selection.username = $scope.data.candidate.Username;
                            $scope.data.listing = listing;
                        });
                    });
                }
                else{
                    console.debug("Candidate has active reqeust(s); do not load listing data");
                }

                // Scope functions
                $scope.getDate = function(d){
                    return new Date(d);
                };

                $scope.submitRequest = function(){
                    if( !$scope.data.selection.pi.Key_id ){
                        // Invalid request; we need a PI's ID
                        return;
                    }

                    $scope.data.selection.submitting = true;

                    AuthAPI.submitRequest( $scope.data.selection.pi.Key_id )
                    .then(
                        result => {
                            let request = result.data;
                            console.info("Access-request result:", result);

                            // if access request is saved, display its status
                            // if access request isn't saved, display error and go nowhere
                            $timeout(function(){
                                // Push the newly-created request into our model
                                $scope.data.candidate.Current_access_request = request;

                                // Flag that the request is complete
                                $scope.data.selection.submission_complete = true;
                            });
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

    // Disregard requested URL and decide where to go based on data:
    if( window.user_access_request ){
        // Candidate data exists
        $state.go('request-access');
    }
    else {
        // No data; login
        $state.go('login');
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

            return $http( requestOptions)
            .success( (data, status, headers, config) => {
                console.log("SUBMISSION SUCCESS", data, status, config);
            })
            .error( (data, status, headers, config) => {
                console.error("SUBMISSION ERROR", data, status, config);
            });
        }
    };
})
.filter('pendingRequests', function(){
    return function( requests ){
        return requests.filter(r => r.Status == 'PENDING');
    }
});