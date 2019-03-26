angular.module('myLab')
    .directive("myLabWidget", function () {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            headerIcon: "@",
            headerImage: "@",
            groupName: "@",
            contentTemplateName: "@",
            data: "="
        },
        replace: false,
        transclude: true,
        templateUrl: "./widgets/my-lab-widget.html",
        link: function(scope, element, attrs){
            element.addClass("widget").addClass("well");
        },
        controller: function ($scope){
            $scope.GLOBAL_WEB_ROOT = window.GLOBAL_WEB_ROOT;

            if( $scope.contentTemplateName ){
                $scope.contentTemplate = './widgets/' + $scope.contentTemplateName + '.html';
            }

            console.log('content-template:', $scope.contentTemplate);
        }
    };
});
