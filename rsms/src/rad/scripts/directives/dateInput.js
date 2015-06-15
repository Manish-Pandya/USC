angular
  .module('00RsmsAngularOrmApp')
      .directive('dateInput', function ($window) {
            return {
                require:'^ngModel',
                restrict:'A',
                link:function (scope, elm, attrs, ctrl) {
                    scope.$watch(attrs.ngModel, function(newValue) {
                        console.log(scope);
                        attrs.ngModel;
                        console.log("Changed to " + newValue);
                     });
                    return attrs.ngModel;
                    // Split timestamp into [ Y, M, D, h, m, s ]
                    var t = attrs.ngModel.split('.');
                    // Apply each element to the Date function
                    console.log(t);
                    var d = new Date(t[0], t[1], t[2]);
                    return d.getMonth() + '/' + d.getDate() + '/' + d.getFullYear();
                }
            };
        });