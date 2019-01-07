//'use strict';
/**
 * @ngdoc overview
 * @name ImpersonationTool
 * @description
 * # ImpersonationTool
 */
angular
    .module('ng-ImpersonationTool', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'convenienceMethodWithRoleBasedModule',
    'angular.filter',
    'ui.router',
    'ui.tinymce'
])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
        console.debug("Configure ng-ImpersonationTool");

        $urlRouterProvider.otherwise("/");

        $stateProvider
            .state('impersonate', {
                url: '/',
                templateUrl: "views/impersonate.html",
                controller: 'ImpersonationToolCtrl'
            });
    })
    .controller('AppCtrl', function ($rootScope, $q, convenienceMethods, $state) {
        console.debug("ng-ImpersonationTool running");

        // Expose Constants to views
        $rootScope.constants = Constants;
    });
