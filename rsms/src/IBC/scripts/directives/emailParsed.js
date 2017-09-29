angular.module('ng-IBC')
    .directive("emailParsed", function () {
    return {
        restrict: 'E',
        scope: {
            email: "=",
            recipients: "=",
            sendHandler: "&?"
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/ibc-email-parsed.html",
        link: function (scope, element, attrs) {
            if (!('sendHandler' in attrs))
                scope.sendHandler = null;
        },
        controller: function ($scope) {
        }
    };
});
