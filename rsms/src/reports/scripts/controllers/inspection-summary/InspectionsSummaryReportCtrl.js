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

        $scope.hasHazard = function hasHazard( item, field ){
            return parseInt( item[field] ) > 0;
        };

        // Helper mappings for generating 'friendly' status texts
        var friendlyText = [];
        friendlyText[Constants.INSPECTION.STATUS.CLOSED_OUT] = "Closed Out";
        friendlyText[Constants.INSPECTION.STATUS.NOT_ASSIGNED] = "Not Yet Inspected";
        friendlyText[Constants.INSPECTION.STATUS.NOT_SCHEDULED] = "Not Yet Inspected";
        friendlyText[Constants.INSPECTION.STATUS.SCHEDULED] = "Not Yet Inspected";
        friendlyText[Constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION] = "Scheduling Inspection";
        friendlyText[Constants.INSPECTION.STATUS.OVERDUE_CAP] = "Overdue Corrective Actions";
        friendlyText[Constants.INSPECTION.STATUS.INCOMPLETE_INSPECTION] = "Inspection In Progress";
        friendlyText[Constants.INSPECTION.STATUS.INCOMPLETE_CAP] = "Incomplete Corrective Actions";
        friendlyText[Constants.INSPECTION.STATUS.SUBMITTED_CAP] = "Submitted Corrective Actions";
        friendlyText[Constants.INSPECTION.STATUS.INSPECTED] = "Inspected";

        /**
         * Gets the 'friendly' status text for a given status code
         */
        $scope.getStatusText = function getStatusText(inspection){
            return friendlyText[inspection.Inspection_status] || inspection.Inspection_status;
        };

        /**
         * Return true if a Link to the inspection should be shown in the report table
         */
        $scope.showInspectionLink = function showInspectionLink(inspection){
            switch(inspection.Inspection_status){
                case Constants.INSPECTION.STATUS.NOT_ASSIGNED:
                case Constants.INSPECTION.STATUS.NOT_SCHEDULED:
                case Constants.INSPECTION.STATUS.SCHEDULED:
                case Constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION:
                    return false;
                default:
                    return true;
            }
        }

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