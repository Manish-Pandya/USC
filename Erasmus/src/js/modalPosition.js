//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly, vertically
angular.module('modalPosition', [])

.directive('modal', ['$window', function($window) {
    return {
	    restrict : 'C',
	    link : function(scope, element, attributes) {
	    	scope.onResize = function() {
	            var topMargin = $window.innerHeight - element[0].clientHeight;
                $(element[0]).css({maxHeight: $window.innerHeight*.9});	            
                $(element[0]).css({top: topMargin/2, marginTop:-10, overflowY:'hidden'});
                $(element[0]).find('.modal-body').css({overflowY:'auto', maxHeight:$window.innerHeight*.85-30});
        	}
        	scope.onResize();

            angular.element($(element[0])).bind('DOMNodeInserted', function() {
                scope.onResize();
            })

            angular.element($window).bind('resize', function() {
                scope.onResize();
            });
      	}
    }
 }]);