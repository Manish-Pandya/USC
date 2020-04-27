// Get the path to this script file so that we can assume path to template
// This allows the directive to be included by other modules, such as My Lab
var scripts = document.getElementsByTagName("script");
var userAccessRequestTableScriptPath = scripts[scripts.length-1].src;

angular.module('rsms-AuthDirectives', ['angular.filter'])
.directive('userAccessRequestStatusActions', function(){
    return {
        restrict: 'E',
        scope: {
            request: "="
        },
        replace: false,
        transclude: false,
        templateUrl: userAccessRequestTableScriptPath.replace('UserAccessRequestTable.js', 'UserAccessRequestStatusActions.html'),
        controller: function($scope, $http, $timeout){
            let endpoint_base = window.GLOBAL_WEB_ROOT + 'ajaxaction.php';
            $scope.resolveRequest = async function resolveRequest( request, approved ){
                let cfg = {
                    method: 'POST',
                    url: endpoint_base,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    data: $.param({
                        action: 'resolveAccessRequest',
                        request_id: request.Key_id,
                        newStatus: approved ? 'APPROVED' : 'DENIED'
                    })
                };

                request._saving = true;

                let result = null;
                try {
                    result = await $http( cfg );
                }
                catch( err ){
                    console.error("Error resolving access request", err);
                }
                finally {
                    $timeout(function(){
                        request._saving = undefined;
                        if( result ){
                            angular.extend(request, result.data);
                        }
                    });
                }
            }
        }
    };
})
.directive('userAccessRequestTable', function(){
    return {
        restrict: 'E',
        scope: {
            requests: "="
        },
        replace: false,
        transclude: false,
        templateUrl: userAccessRequestTableScriptPath.replace('UserAccessRequestTable.js', 'UserAccessRequestTable.html'),
        controller: function($scope){
            console.debug("UserAccessRequestTable controller");
            $scope.GLOBAL_WEB_ROOT = window.GLOBAL_WEB_ROOT;

            $scope.getDate = function getDate( d ){
                return new Date(d);
            }
        }
    };
});