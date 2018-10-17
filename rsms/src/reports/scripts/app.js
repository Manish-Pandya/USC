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
    'actionFunctionsModule',
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

        $urlRouterProvider.otherwise("/inspection-summary-report/");

        $stateProvider
            .state('reports', {
                url: "/",
                templateUrl: "views/report-types.html",
                controller: "ReportTypesCtrl"
            });

        // Inspection Summary Report routes
        $stateProvider
        .state('isr', {
            abstract: true,
            url: '/inspection-summary-report',
            template: '<ui-view/>'
        })

        .state('isr.dept', {
            url: "/:departmentId",
            templateUrl: "views/inspection-summary-report.html",
            controller: "InspectionsSummaryReportCtrl"
        })

        .state('isr.report', {
            url: "/:departmentId/:year",
            templateUrl: "views/inspection-summary-report.html",
            controller: "InspectionsSummaryReportCtrl"
        });

        // TODO: Other reports?
    })
    .controller('AppCtrl', function ($rootScope, $q, convenienceMethods, $state) {
        console.debug("ng-Reports running");
    });
