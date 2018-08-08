//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly
angular.module('uploadContainer', [])

.directive('uploadContainer', ['$window', function ($window) {
    return {
        restrict: 'C',
        scope: {
            clickTarget: "=",
            urlFragment:"@"
        },
        link: function (scope, elem) {
            var target = elem[0].getElementsByTagName("input")[0];
            elem[0].addEventListener("click", function (e) {
                target.dispatchEvent( new MouseEvent("click", function () {
                    return {
                        "view": window,
                        "bubbles": false,
                        "cancelable": true
                    }
                }));
                $(target).blur();
            })
            $(target).bind('change', function () {
                var data = {};
                if(target.files.length == 0){
                    // Don't bother if there's no file to upload...
                    return;
                }

                var formData = new FormData();
                formData.append('file', target.files[0]);
                $(target).blur();
                $("label[for='" + $(target).attr('id') + "']").blur();
                data.clickTarget = scope.clickTarget;
                data.formData = formData;
                if (scope.urlFragment) {
                    data.path = scope.urlFragment;
                }

                scope.$emit("fileUpload", data);
                return;
            });
        }
    }
}])
