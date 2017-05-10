angular.module('ng-IBC')
    .directive("collapsibleCard", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            scoped: "=",
            openHandler: "&",
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/collapsible-card.html",
        controller: function ($scope) {
            console.log($scope);
            console.log("I run immediately", $scope.closed, closed);
            if (typeof $scope.closed == 'undefined')
                $scope.closed = true;
            $scope.open = function (param) {
                if (!$scope.closed)
                    $scope.openHandler().apply(void 0, $scope.scoped);
                console.log("I only run when opened.", $scope.closed);
            };
        }
    };
});
