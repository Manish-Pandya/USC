angular.module('ng-IBC')
.directive("collapsibleCard", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            scoped: "=",
            openHandler: "&?",
            isopen: "=" // tried camelCase 'isOpen' and 'isClosed', but then it stops working. Maybe those are reserved? Weird.
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/collapsible-card.html",
        link: function (scope, element, attrs) {
            if (!('openHandler' in attrs)) scope.openHandler = null;
        },
        controller: ($scope) => {
            $scope.$watch("isopen", () => {
                $scope.closed = !$scope.isopen;
                $scope.open();
            });

            $scope.open = (param): any => {
                if (!$scope.closed && $scope.openHandler) $scope.openHandler()(...$scope.scoped);
            }
        }
    }
});