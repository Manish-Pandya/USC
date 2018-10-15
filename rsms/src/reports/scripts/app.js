//'use strict';
/**
 * @ngdoc overview
 * @name InspectionsSummaryReport
 * @description
 * # InspectionsSummaryReport
 *
 * Main module of the application.
 */
angular
    .module('ng-Reports', [
    'cgBusy',
    'ui.bootstrap',
    'once',
    'modalPosition',
    'convenienceMethodWithRoleBasedModule',
    'angular.filter',
    'ui.router',
    'ngQuickDate'
])
    .config(function ($stateProvider, $urlRouterProvider, $httpProvider) {
        console.debug("Configure ng-Reports");

        $urlRouterProvider.otherwise("/report");

        // Inspection Summary Report routes
        $stateProvider
            .state('isr', {
            abstract: true,
            url: '',
            template: '<ui-view/>'
        })
        .state('isr.report-admin', {
            url: "/report",
            templateUrl: "views/report.html",
            controller: "InspectionsSummaryReportCtrl"
        })
        .state('isr.report-department', {
            url: "/report/:year/department/:departmentId",
            templateUrl: "views/report.html",
            controller: "InspectionsSummaryReportCtrl"
        });

        // TODO: Other reports?
    })
    .controller('AppCtrl', function ($rootScope, $q, convenienceMethods, $state) {
        console.debug("ng-Reports running");
    });
