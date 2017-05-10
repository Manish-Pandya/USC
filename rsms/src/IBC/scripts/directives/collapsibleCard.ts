angular.module('ng-IBC')
.directive("collapsibleCard", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            scoped: "=",
            openHandler: "&",
            //closed: "="
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/collapsible-card.html",
        controller: ($scope) => {
            console.log($scope);
            console.log("I run immediately", $scope.closed, closed);
            if (typeof $scope.closed == 'undefined') $scope.closed = true;
            $scope.open = (param): any => {
                if (!$scope.closed) $scope.openHandler()(...$scope.scoped);
                console.log("I only run when opened.", $scope.closed);
            }
        }
    }
});