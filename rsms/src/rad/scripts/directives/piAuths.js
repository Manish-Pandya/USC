var myApp = angular.module('00RsmsAngularOrmApp');
myApp.directive("piAuths", function () {
    return {
        restrict: 'E',
        templateUrl: "views/admin/piAuths.html",
        scope: {
            pi: "="
        },
        transclude: true,
        link: function (scope, element, attrs, controller) {
            
        }
     }
});