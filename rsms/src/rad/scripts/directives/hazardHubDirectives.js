//handles sizing of elements within hazard hub list items
angular
  .module('00RsmsAngularOrmApp')
    .directive('hazardHubLi', ['$rootScope','$window','$q', function($rootScope,$window, $q) {
        return {
            restrict: 'A',
            link: function(scope, elem, attrs) {
                scope.onResize = function() {
                    w = elem.width();
                    if(w<1200 && $($window).width()>1365){
                        elem.addClass('small');
                    }else if(w<1140 && $($window).width()<1365){
                        elem.addClass('small');
                    }else{
                        elem.removeClass('small');
                    }

                    //this code ensures that the hazard names, buttons and toggle buttons all line up properly, displaying cleanly even with linebreaks

                    //get the width of the container element of for our buttons
                    var btns = elem.find('.hazarNodeButtons');
                    var leftThings = elem.find('.leftThings');

                    var btnWidth  = btns.width();

                    //set the width of all the elements on the left side of our hazard li elements
                    var leftWidth = w - btnWidth - 100;
                    leftThings.width(leftWidth);
                    leftThings.find('span').css({width:leftWidth-70+'px'});
                }
                scope.onResize();

                scope.$watch(
                    function(){
                        return scope.onResize();
                    }
                )
                angular.element($window).bind('resize', function() {
                    scope.onResize();
                });
            }
        }
    }])