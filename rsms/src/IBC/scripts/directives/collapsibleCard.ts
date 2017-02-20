angular.module('ng-IBC')
.directive("collapsibleCard", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            scoped: "=",
            open: "="
        },
        replace:false,
        transclude: true,
        templateUrl: "./scripts/directives/collapsible-card.html"
    }
});