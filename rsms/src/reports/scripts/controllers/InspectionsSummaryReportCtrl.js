'use strict';

angular.module('ng-Reports')
    .controller('InspectionsSummaryReportCtrl', function($scope, $stateParams, reportsActionFunctionsFactory){
        console.debug("InspectionsSummaryReport running");

        $scope.getReportParams = function(){
            var selection = {};

            // Check for specified parameters
            if( $stateParams.year ){
                console.debug("Year is specified:", $stateParams.year);
                selection.year = $stateParams.year;
            }

            if( $stateParams.departmentId ){
                console.debug("Department is specified:", $stateParams.departmentId);
                selection.departmentId = $stateParams.departmentId;
            }

            $scope.selection = selection;
            // End param check
        };

        // Get parameters
        $scope.getReportParams();

        $scope.data = {};
        $scope.loading = reportsActionFunctionsFactory.getDepartmentInfo($scope.selection.departmentId)
            .then( function(department){
                $scope.data.Department = department;

                $scope.$apply();
            })
            .then( function(){
                return reportsActionFunctionsFactory.getInspectionSummaryReport($scope.selection.year, $scope.data.Department.Key_id)
            })
            .then(function(report) {
                $scope.data.ReportItems = report;
                $scope.$apply();
            });
    });