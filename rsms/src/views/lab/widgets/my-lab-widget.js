angular.module('myLab')
    .directive("myLabWidget", function (widgetModalActionFactory, widgetFunctionsFactory) {
    return {
        restrict: 'E',
        scope: {
            headerText: "@",
            subheaderText: "@",
            headerIcon: "@",
            headerImage: "@",
            groupName: "@",
            contentTemplateName: "@",
            toolbar: '=',
            widget: '=',
            fullWidth: "=",
            stretchContent: "=",
            alerts: "=",
            data: "=",
            api: "=",
            afterSave: "&"
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

            if( scope.stretchContent ){
                element.addClass("stretch-content");
            }
        },
        controller: function ($scope, widgetModalActionFactory, widgetFunctionsFactory){
            $scope.GLOBAL_WEB_ROOT = window.GLOBAL_WEB_ROOT;
            $scope.Constants = Constants;
            $scope.adminView = GLOBAL_SESSION_ROLES.userRoles.includes(Constants.ROLE.NAME.ADMIN);

            $scope.contentLoaded = function contentLoaded(){
                $scope.loaded = true;
            }

            if( $scope.contentTemplateName ){
                $scope.contentTemplate = './widgets/' + $scope.contentTemplateName + '.html';

                if( $scope.widget.Toolbar ){
                    $scope.toolbarTemplate = './widgets/' + $scope.contentTemplateName + '.toolbar.html';
                }
            }
            else {
                $scope.contentLoaded();
            }

            console.log('content-template:', $scope.contentTemplate);

            if( $scope.widget.ActionWidgets ){
                console.log($scope.widget.ActionWidgets );
                for( var i = 0; i < $scope.widget.ActionWidgets.length; i++){
                    var actionWidget = $scope.widget.ActionWidgets[i];
                    actionWidget.Data = $scope.data;
                    widgetModalActionFactory.addAction(this, actionWidget);
                }
            }

            /** Phone-number masking configuration */
            $scope.phoneMaskConfig = widgetFunctionsFactory.getPhoneMaskConfig();

            $scope.prepareEdit = function(data){
                $scope.editData = angular.copy(data);
            }

            $scope.cancelEdit = function(){
                $scope.editData = undefined;
                $scope.validation = undefined;
            }

            $scope.save = function(data, api_fn, validator_fn){
                $scope.validation = validator_fn(data);
                if( !$scope.validation.valid ){
                    // There are validation errors
                    return;
                }

                $scope.saving = true;
                var promise = api_fn(data).then(
                    saved => {
                        // Save completed successfully
                        $scope.saving = false;

                        // Completely reset our data?
                        $scope.data = saved;

                        // Clear out the edit form
                        $scope.cancelEdit();
                        return $scope.data;
                    },
                    err  => {
                        // Error in saving; keep the edit form open
                        $scope.saving = false;

                        // TODO: DISPLAY ERROR MESSAGE
                        $scope.error = "Something went wrong.";
                    }
                );

                if( $scope.afterSave ){
                    promise.then( $scope.afterSave );
                }

                return promise;
            }
        }
    };
});
