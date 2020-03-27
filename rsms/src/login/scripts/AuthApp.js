angular
    .module('ng-AuthApp', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'angular.filter',
    'ui.router'
])
.config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
    console.debug("ng-AuthApp configuring...");

    $urlRouterProvider.otherwise('/login');

    $stateProvider
        .state('login', {
            url: '/login',
            templateUrl: `views/login.html`,
            controller: function ($scope, AuthAPI){
                console.debug("login ctrl");
            }
        })
})
.controller('AuthAppCtrl', function($rootScope, $state){
    console.debug("ng-AuthApp running");

    $rootScope.GLOBAL_WEB_ROOT = GLOBAL_WEB_ROOT;
    $rootScope.auth_errors = auth_errors;

    if( window.user_access_request ){
        $state.go('request-access');
    }
})
.factory('AuthAPI', function(){
    return {
        getDepartments: function(){ return []; },
        getPrincipalInvestigators: function(){ return []; },
        submitRequest: function(){}
    };
});