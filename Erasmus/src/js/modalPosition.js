//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly
angular.module('modalPosition', [])

.directive('modal', ['$window', function($window) {
    return {
	    restrict : 'C',
	    link : function(scope, element, attributes) {
	    	scope.onResize = function() {
	            var topMargin = $window.innerHeight - element[0].clientHeight;
                $(element[0]).css({maxHeight: $window.innerHeight*.9, minHeight:'250px'});	    
                $(element[0]).find('.modal-content').css({maxHeight: $window.innerHeight*.9, minHeight:'250px'});          
                $(element[0]).css({top: topMargin/2, marginTop:-10});
                $(element[0]).find('.modal-body').css({overflowY:'auto', maxHeight:$window.innerHeight*.85-100});
                $(element[0]).find('.modal-body ul').css({ maxHeight:$window.innerHeight*.85-210});

                if( $('.wide-modal').length ){
                    console.log($window.innerWidth);
                    $(element[0]).width($window.innerWidth * .8);
                    $(element[0]).css({'left':$window.innerWidth * .1+'px', 'marginLeft': 0});
                }
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