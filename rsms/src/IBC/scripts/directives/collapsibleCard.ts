angular.module('ng-IBC')
.directive("collapsibleCard", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            scoped: "=",
            openHandler: "&",
            //closed: "@"
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/collapsible-card.html",
        controller: ($scope) => {
            if (typeof $scope.closed == 'undefined') $scope.closed = true;

            $scope.open = (param): any => {
                if(!$scope.closed)$scope.openHandler()(...$scope.scoped);
            }
        }
    }
});