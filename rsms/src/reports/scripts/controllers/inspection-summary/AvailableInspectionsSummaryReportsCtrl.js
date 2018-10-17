'use strict';

angular.module('ng-Reports')
    .controller('AvailableInspectionsSummaryReportsCtrl', function($scope, $stateParams, reportsActionFunctionsFactory){
        console.debug("AvailableInspectionsSummaryReportsCtrl running");

        // Get all Departments which are available to the user
        $scope.loading = reportsActionFunctionsFactory.getAllAvailableDepartments()
            .then( departments => {
                $scope.Departments = departments;
                $scope.$apply();
            });
    });