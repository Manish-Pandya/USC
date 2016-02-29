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
    })
    .directive('tableError', function ($parse) {
        return {
            restrict: 'A',
            scope: {
                obj: "=obj",
            },
            
            transclude: false,
            link: function (scope, element, attrs, controller) {
                
                
                scope.$watch('obj.error',function(newVal, oldVal){
                    if (newVal != oldVal) {
                        addOrRemoveErrorDisplay(newVal);
                    }
                })
                

                function addOrRemoveErrorDisplay(error) {
                    console.log('fired');
                    var parent = element.parents('tbody') || element.parents('table');
                    var cols = parent.children('tr').children('td').length;
                    parent.children('tr.alert-danger.error').remove();
                    if (error) {
                        //find parent table
                        var template = '<tr style="position:relative" class="danger alert-danger error">' +
                                        '<td colspan="' + cols + '" style="background-color:#d00 !important; color:white !important;"><h3 style="text-align:center">' + error + '</h3></td>' +
                                      '</tr>';
                        parent.children('tbody tr.edit').after(template);
                    } 
                }
            }
        }
    });
