angular.module('ng-IBC')
.directive("collapsibleCard", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            scoped: "=",
            closed: "@"
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/collapsible-card.html",
        link: function (scope) {
            if (typeof scope.closed == 'undefined') scope.closed = true;
        }
    }
});