angular.module('ng-IBC')
    .directive("questionView", function () {
        var templateSwitcher = () => {

        }

        return {
            restrict: 'E',
            scope: {
                question: "=",
                questionType: "@",
                responses: "=",
                revisionId: "@"
            },
            link: (scope, elem, attrs) => {
                scope.constants = Constants;
                scope.question.IBCPossibleAnswers.forEach((pa: ibc.IBCPossibleAnswer) => {
                    if (!scope.responses[pa.UID]) {
                        let response = new ibc.IBCResponse();
                        response["Answer_id"] = pa.UID;
                        response["Revision_id"] = scope.revisionId;
                        response["Question_id"] = scope.question.UID;
                        response["Is_selected"] = false;
                        response["Is_active"] = true;

                        response["Class"] = (<any>response.thisClass).name;
                        scope.responses[pa.UID] = [response];
                    }
                })
            },
            replace: false,
            transclude: true,
            templateUrl: (elem, attrs, scope) => {
                console.log(elem, attrs);
                return "./scripts/directives/ibc-question-template.html";
            }
        }
    });