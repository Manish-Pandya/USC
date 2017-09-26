angular.module('ng-IBC')
    .directive("emailTemplate", function () {
    return {
        restrict: 'E',
        scope: {
            email: "=",
            saveHandler: "&?"
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/ibc-email-template.html",
        link: function (scope, element, attrs) {
            if (!('saveHandler' in attrs))
                scope.saveHandler = null;
            $('#txtArea').first().contextMenu(ibc.IBCEmailGen.contextMenuMacros, { triggerOn: 'contextmenu' });
        },
        controller: function ($scope) {
        }
    };
});
