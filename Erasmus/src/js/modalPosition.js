//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly, vertically
angular.module('modalPosition', [])

.directive('modal', function($window) {
    return {
        restrict : 'C',
        link : function(scope, element, attributes) {
            var topMargin = $window.innerHeight - element[0].clientHeight;
            console.log(topMargin/2);
            $(element[0]).css({top: topMargin/2, marginTop:0});
      }
    }
 })