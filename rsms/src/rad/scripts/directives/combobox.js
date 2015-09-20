angular
  .module('00RsmsAngularOrmApp')
    .directive('combobox', function( $parse ) {
        return {
            restrict: 'E',
            scope: {
                options: "=options",
                model: "=model",
                modelProp: "=modelprop"
            },
            template: '<span style="position:relative" class="combobox">'+
                        '<input ng-model="model[modelProp]" ng-focus="model.showDropDown = true"/><i class="icon-arrow-down"></i>'+
                            '<ul style="width:140px" class="dropdown-menu show" ng-if="model.showDropDown">'+
                                '<li ng-repeat="option in options"><a ng-click="onClick(option);">{{option}}</a></li>'+
                            '</ul>'+
                       '</span>',
            replace: true,
            transclude: false,
            link: function (scope, element, attrs, controller) {
                //pseudo-blur.  if we click outside the parent element, hide the dropdown.  This way we don't cancel the click event on the li
                $("body").on('click blur focus focusin focusout',function(e) {
                    if (!$(e.target).hasClass('combobox') && !$(e.target).parents('.combobox').size()) {
                       scope.model.showDropDown = false;
                       scope.$apply();
                       console.log(e.target);
                    }else{
                       console.log(e.target);
                    }
                });

                scope.onClick = function (option) {
                    scope.model[scope.modelProp] = option;
                    scope.model.showDropDown = false
                }
            }
        }
    });
