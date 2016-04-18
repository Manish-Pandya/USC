//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly
angular.module('modalPosition', [])

.directive('modal', ['$window', function($window) {
    return {
	    restrict : 'C',
	    link : function(scope, element, attributes) {
	    	var onResize = function() {
	            var topMargin = $window.innerHeight - element[0].clientHeight;
                $(element[0]).css({maxHeight: $window.innerHeight*.95, minHeight:'250px'});	    
                $(element[0]).find('.modal-content').css({maxHeight: ($window.innerHeight*.95-50), minHeight:'250px'});          
                $(element[0]).css({top: (topMargin/2)-20, marginTop:-10});
                $(element[0]).find('.modal-body').css({overflowY:'auto', maxHeight:$window.innerHeight*.85-150});
                $(element[0]).find('.modal-body ul').css({ maxHeight:$window.innerHeight*.85-210});

                if( $('.wide-modal').length ){
                    if($window.innerWidth > 1370){
                        $(element[0]).width($window.innerWidth * .8);
                        $(element[0]).css({'left':$window.innerWidth * .1+'px', 'marginLeft': 0});
                    }else{
                        $(element[0]).width($window.innerWidth*.98);
                        $(element[0]).css({'left':$window.innerWidth*.005+'px', 'marginLeft': 0});
                    }
                }
	    	}

        	onResize();

            angular.element($(element[0])).bind('DOMNodeInserted', function() {
                onResize();
            })

            angular.element($window).bind('resize', function() {
                onResize();
            });

            $(window).on("orientationchange", function () {
                //window.setTimeout(function () { onResize(); }, 300);
            });
      	}
    }
 }]);