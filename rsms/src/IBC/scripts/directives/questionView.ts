angular.module('ng-IBC')
    .directive("questionView", function () {
        var templateSwitcher = () => {

        }

        return {
            restrict: 'E',
            scope: {
                question: "=",
                questionType: "@",
                revision: "=",
                revisionId: "@"
            },
            link: (scope, elem, attrs) => {
                //console.log(scope.question);
                //console.log(scope.revision);

                scope.showQuestion = false;
                if (scope.revision.Status == Constants.IBC_PROTOCOL_REVISION.STATUS.RETURNED_FOR_REVISION) {
                    let preliminaryCommentsMap = scope.revision.preliminaryCommentsMapped[scope.question.UID];
                    let primaryCommentsMap = scope.revision.primaryCommentsMapped[scope.question.UID];
                    if ((preliminaryCommentsMap && preliminaryCommentsMap.length) || (primaryCommentsMap && primaryCommentsMap.length)) {
                        scope.showQuestion = true;
                    }
                } else {
                    scope.showQuestion = true;
                }
                scope.constants = Constants;
                scope.question.IBCPossibleAnswers.forEach((pa: ibc.IBCPossibleAnswer) => {
                    if (!scope.revision.responsesMapped[pa.UID]) {
                        let response = new ibc.IBCResponse();
                        response["Answer_id"] = pa.UID.toString();
                        response["Revision_id"] = scope.revisionId;
                        response["Question_id"] = scope.question.UID;
                        response["Is_selected"] = false;
                        response["Is_active"] = true;

                        response["Class"] = (<any>response.thisClass).name;
                        scope.revision.responsesMapped[pa.UID] = [response];
                    }
                })
                /*
                scope.$watch('revision.IBCResponses', (newValue, oldValue) => {
                    console.log(newValue);
                    scope.revision.getResponsesMapped();
                })
                */
                scope.responses = scope.revision.responsesMapped;

                scope.addComment = function (): void {
                    scope.preliminaryComment = new ibc.IBCPreliminaryComment();
                    scope.preliminaryComment.Revision_id = scope.revision.UID;
                    scope.preliminaryComment.Question_id = scope.question.UID;
                    scope.state.commentShown = true;
                }

                scope.saveComment = function (): void {
                    scope.state.commentShown = false;
                    scope.revision.preliminaryCommentsMapped = {}; // reset the object to blank
                    if (!scope.revision.IBCPreliminaryComments) scope.revision.IBCPreliminaryComments = [];
                    if (!scope.revision.IBCPreliminaryComments.length || scope.revision.IBCPreliminaryComments.slice(-1) != scope.preliminaryComment) {
                        scope.revision.IBCPreliminaryComments.push(scope.preliminaryComment);
                    }
                    scope.revision.getPreliminaryCommentsMapped(); // rebuild mapping
                }

                scope.cancelComment = function (): void {
                    scope.state.commentShown = false;
                    scope.preliminaryComment.Text = "";
                    if (scope.revision.IBCPreliminaryComments && scope.revision.IBCPreliminaryComments.slice(-1) == scope.preliminaryComment) {
                        scope.revision.IBCPreliminaryComments.splice(-1, 1);
                    }
                }

                scope.state = { commentShown: false };
            },
            replace: false,
            transclude: true,
            templateUrl: (elem, attrs, scope) => {
                //console.log(elem, attrs);
                return "./scripts/directives/ibc-question-template.html";
            }
        }
    });