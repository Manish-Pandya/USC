angular.module('ng-IBC')
.directive("emailTemplate", function () {
    return {
        restrict: 'E',
        scope: {
            email: "=",
            saveHandler: "&?"
        },
        replace: false,
        transclude: true,
        templateUrl: "./scripts/directives/ibc-email-template.html",
        link: function (scope, element, attrs) {
            if (!('saveHandler' in attrs)) scope.saveHandler = null;

            // hackish but needed 1ms pause for tinymce to fire first
            setTimeout(() => {
                // establish the context menu
                element.contextMenu(ibc.IBCEmailGen.contextMenuMacros);

                // locate the iframe tinymce uses
                var iframe = element.find('iframe').contents();

                // catch right-click and mouseup events in the iframe
                iframe.contextmenu((eData: any): void => {
                    console.log(eData);
                    eData.preventDefault();
                    // open context menu at provided offset
                    element.contextMenu('open', { top: eData.screenY - 91, left: eData.screenX });
                }).mouseup((eData: any): void => {
                    console.log(eData);
                    element.trigger(eData);
                    // close context menu
                    element.contextMenu('close');
                });;
            }, 1);
        },
        controller: ($scope) => {
            
        }
    }
});