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
            if (!('openHandler' in attrs))
                scope.openHandler = null;
        },
        controller: function ($scope) {
            $scope.$watch("isopen", function () {
                $scope.closed = !$scope.isopen;
                $scope.open();
            });
            $scope.open = function (param) {
                if (!$scope.closed && $scope.openHandler)
                    $scope.openHandler().apply(void 0, $scope.scoped);
            };
        }
    };
});
