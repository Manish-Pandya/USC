angular.module('ng-IBC')
    .directive("questionView", function () {
    var templateSwitcher = function () {
    };
    return {
        restrict: 'E',
        scope: {
            question: "=",
            questionType: "@",
            responses: "=",
            revisionId: "@"
        },
        link: function (scope, elem, attrs) {
            scope.constants = Constants;
            scope.question.IBCPossibleAnswers.forEach(function (pa) {
                if (!scope.responses[pa.UID]) {
                    var response = new ibc.IBCResponse();
                    response["Answer_id"] = pa.UID;
                    response["Revision_id"] = scope.revisionId;
                    response["Question_id"] = scope.question.UID;
                    response["Class"] = response.thisClass.name;
                    scope.responses[pa.UID] = [response];
                }
            });
        },
        replace: false,
        transclude: true,
        templateUrl: function (elem, attrs, scope) {
            console.log(elem, attrs);
            return "./scripts/directives/ibc-question-template.html";
        }
    };
});
