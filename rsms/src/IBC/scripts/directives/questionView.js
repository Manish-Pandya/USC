angular.module('ng-IBC')
    .directive("questionView", function () {
    var templateSwitcher = function () {
    };
    return {
        restrict: 'E',
        scope: {
            question: "=",
            questionType: "@",
            revision: "=",
            revisionId: "@"
        },
        link: function (scope, elem, attrs) {
            console.log(scope.question);
            scope.constants = Constants;
            scope.question.IBCPossibleAnswers.forEach(function (pa) {
                if (!scope.revision.responsesMapped[pa.UID]) {
                    var response = new ibc.IBCResponse();
                    response["Answer_id"] = pa.UID.toString();
                    response["Revision_id"] = scope.revisionId;
                    response["Question_id"] = scope.question.UID;
                    response["Is_selected"] = false;
                    response["Is_active"] = true;
                    response["Class"] = response.thisClass.name;
                    scope.revision.responsesMapped[pa.UID] = [response];
                }
            });
            /*
            scope.$watch('revision.IBCResponses', (newValue, oldValue) => {
                console.log(newValue);
                scope.revision.getResponsesMapped();
            })
            */
            scope.responses = scope.revision.responsesMapped;
        },
        replace: false,
        transclude: true,
        templateUrl: function (elem, attrs, scope) {
            console.log(elem, attrs);
            return "./scripts/directives/ibc-question-template.html";
        }
    };
});
