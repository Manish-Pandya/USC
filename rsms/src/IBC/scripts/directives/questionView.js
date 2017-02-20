angular.module('ng-IBC')
    .directive("questionView", function () {
    var templateSwitcher = function () {
    };
    return {
        restrict: 'E',
        scope: {
            question: "=",
            questionType: "@"
        },
        link: function (scope, elem, attrs) {
            scope.constants = Constants;
        },
        replace: false,
        transclude: true,
        templateUrl: function (elem, attrs, scope) {
            console.log(elem, attrs);
            return "./scripts/directives/ibc-question-template.html";
        }
    };
});
