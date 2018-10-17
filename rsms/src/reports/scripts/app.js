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

        $urlRouterProvider.otherwise("/inspection-summary/available-reports");

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
            url: '/inspection-summary',
            template: "<ui-view/>",
            data: {
                reportName: 'Inspections Summary'
            }
        })

        .state('isr.available', {
            url: '/available-reports',
            templateUrl: "views/inspection-summary/available-reports.html",
            controller: "AvailableInspectionsSummaryReportsCtrl"
        })

        .state('isr.reports', {
            abstract: true,
            url: "/reports",
            template: "<ui-view/>"
        })

        .state('isr.reports.dept', {
            url: "/:departmentId",
            templateUrl: "views/inspection-summary/report.html",
            controller: "InspectionsSummaryReportCtrl"
        })

        .state('isr.reports.detail', {
            url: "/:departmentId/:year",
            templateUrl: "views/inspection-summary/report.html",
            controller: "InspectionsSummaryReportCtrl"
        });

        // TODO: Other reports?
    })
    .controller('AppCtrl', function ($rootScope, $q, convenienceMethods, $state) {
        console.debug("ng-Reports running");
        $rootScope.$on('$stateChangeSuccess',
            function (event, toState, toParams, fromState, fromParams) {
                if( toState.data && toState.data.reportName ){
                    $rootScope.reportName = toState.data.reportName;
                }
                else{
                    $rootScope.reportName = '';
                }
            }
        );
    });
