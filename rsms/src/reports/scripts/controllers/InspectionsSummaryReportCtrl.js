'use strict';

angular.module('ng-Reports')
    .controller('InspectionsSummaryReportCtrl', function($scope, $stateParams, reportsActionFunctionsFactory){
        console.debug("InspectionsSummaryReport running");

        // Set up configuration settings
        $scope.config = {
            // Default table sort details
            orderByField: 'Principal_investigator_name',
            reverseSort: false,

            // Display the Department name (useful for multi-dept reports)
            list_department: false
        };

        /**
         * Reorder the report results by the given field.
         * Repeat calls to the same field will reverse sort order
         */
        $scope.reorder = function reorder(field){
            if( $scope.config.orderByField == field ){
                $scope.config.reverseSort = !$scope.config.reverseSort;
            }
            else{
                $scope.config.orderByField = field;
                $scope.config.reverseSort = false;
            }
        }

        /**
         * Reads parameters for the requested report
         */
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