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
            fullWidth: "=",
            alerts: "=",
            data: "=",
            api: "="
        },
        replace: false,
        transclude: true,
        templateUrl: "./widgets/my-lab-widget.html",
        link: function(scope, element, attrs){
            element.addClass("widget")
                .addClass("well");

            if( scope.fullWidth ){
                element.addClass("full");
            }
        },
        controller: function ($scope){
            $scope.GLOBAL_WEB_ROOT = window.GLOBAL_WEB_ROOT;
            $scope.Constants = Constants;

            if( $scope.contentTemplateName ){
                $scope.contentTemplate = './widgets/' + $scope.contentTemplateName + '.html';
            }

            console.log('content-template:', $scope.contentTemplate);

            $scope.prepareEdit = function(data){
                $scope.editData = angular.copy(data);
            }

            $scope.cancelEdit = function(){
                $scope.editData = undefined;
            }

            $scope.save = function(data, api_fn){
                $scope.saving = true;
                api_fn(data).then(
                    data => {
                        // Save completed successfully
                        $scope.saving = false;

                        // Clear out the edit form
                        $scope.cancelEdit();
                    },
                    err  => {
                        // Error in saving; keep the edit form open
                        // TODO: DISPLAY ERROR MESSAGE
                        $scope.saving = false;
                    }
                );
            }
        }
    };
});
