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

	        var relevantSelectMatches,
            selectMap = [],
            relevantSelectArrows,
            arrowMap = [];
	        var body = $(element[0]).find('.modal-body');

	        var positionUISelects = function () {
	            var h = body.height() + 60;
	            var t = body[0].offsetTop;
	            //console.log(body);

	            if (!relevantSelectMatches) {
	                relevantSelectMatches = body.find(".ui-select-match");
	                relevantSelectArrows = body.find(".icon-arrow-down.dropdown-arrow");
	                //the first time through, build a map of the offsetTops of each matched element so we can updated them quickly
	                $(relevantSelectMatches).each(function (x) {
	                    var $this = $(this);
	                    selectMap[x] = $this[0].offsetTop - 5;
	                    arrowMap[x] = $(relevantSelectArrows[x])[0].offsetTop - 5;
	                });

	            } else {
	                $top = body.scrollTop();
                    //console.log("top")
	                //console.log(t);
	                $(relevantSelectMatches).each(function (x) {
	                    var $this = $(this);
	                    var $that = $(relevantSelectArrows[x])
	                    if (selectMap[x] === 0) {
	                        selectMap[x] = $this[0].offsetTop - 5;
	                    }

	                    if (arrowMap[x] === 0) {
	                        arrowMap[x] = $that[0].offsetTop - 5;
	                    }
	                    $this.css({ "top": selectMap[x] - $top + "px" });
	                    $that.css({ "top": arrowMap[x] - $top + "px" });
	                    var top = $that[0].offsetTop - 5;
	                    if (top > h || top < t) {
	                        $this.css({ "visibility": "hidden" });
	                        $that.css({ "visibility": "hidden" });
	                    } else {
	                        $this.css({ "visibility": "visible" });
	                        $that.css({ "visibility": "visible" });
	                    }
	                });
	            }
	        }

	        body.on('scroll', function () {
	            positionUISelects();	            
	        });

            $(window).on("orientationchange", function () {
                //window.setTimeout(function () { onResize(); }, 300);
            });
      	}
    }
 }]);