angular.module('ng-IBC')
    .directive("questionView", function () {
        var templateSwitcher = () => {

        }

        return {
            restrict: 'E',
            scope: {
                question: "=",
                questionType: "@",
                responses: "="
            },
            link: (scope, elem, attrs) => {
                scope.constants = Constants;
            },
            replace: false,
            transclude: true,
            templateUrl: (elem, attrs, scope) => {
                console.log(elem, attrs);
                return "./scripts/directives/ibc-question-template.html";
            }
        }
    });