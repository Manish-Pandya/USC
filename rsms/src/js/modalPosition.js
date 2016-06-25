//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly
angular.module('modalPosition', [])

.directive('modal', ['$window', function ($window) {
    return {
        restrict: 'C',
        link: function (scope, element, attributes) {
            var onResize = function () {
                var topMargin = $window.innerHeight - element[0].clientHeight;
                $(element[0]).css({ maxHeight: $window.innerHeight * .95, minHeight: '250px' });
                $(element[0]).find('.modal-content').css({ maxHeight: ($window.innerHeight * .95 - 50), minHeight: '250px' });
                $(element[0]).css({ top: (topMargin / 2) - 20, marginTop: -10 });
                $(element[0]).find('.modal-body').css({ overflowY: 'auto', maxHeight: $window.innerHeight * .85 - 150 });
                $(element[0]).find('.modal-body ul').css({ maxHeight: $window.innerHeight * .85 - 210 });

                if ($('.wide-modal').length) {
                    if ($window.innerWidth > 1370) {
                        $(element[0]).width($window.innerWidth * .8);
                        $(element[0]).css({ 'left': $window.innerWidth * .1 + 'px', 'marginLeft': 0 });
                    } else {
                        $(element[0]).width($window.innerWidth * .98);
                        $(element[0]).css({ 'left': $window.innerWidth * .005 + 'px', 'marginLeft': 0 });
                    }
                }
            }


            onResize();

            angular.element($(element[0])).bind('DOMNodeInserted', function () {
                onResize();
            })

            angular.element($window).bind('resize', function () {
                onResize();
            });

            var relevantSelectMatches,
            selectMap = [],
            relevantSelectArrows,
            arrowMap = [];
            var body = $(element[0]).find('.modal-body');

            function isElementInViewport(el) {

                //special bonus for those using jQuery
                if (typeof jQuery === "function" && el instanceof jQuery) {
                    el = el[0];
                }

                var rect = el.getBoundingClientRect();
                console.log(rect.top);
                console.log(body.innerHeight());
                return (
                    rect.top >= 0 &&
                    rect.bottom <= (body.outerHeight() + 83)  /*or $(window).height() */
                );
            }


            var drops;
            var positionUISelects = function () {

                scope.things = body.find(".ui-select-container");
                var setDrops = function (dropDowns) {
                    drops = dropDowns;
                }
                setDrops(scope.things);
                drops.each(function (i) {
                    var $top = body.scrollTop();
                    var $this = $(this);
                    var arrow = $this.find(".icon-arrow-down.dropdown-arrow");
                    var match = $this.find(".ui-select-match");
                    var drop = $this.find(".ui-select-dropdown");

                    if (!isElementInViewport($this)) {
                        arrow.css({ "visibility": "hidden", 'top': $this.position().top });
                        match.css({ "visibility": "hidden", 'top': $this.position().top });
                        drop.css({ "visibility": "hidden", 'top': $this.position().top });
                    } else {
                        arrow.css({ "visibility": "visible", 'top': $this.position().top });
                        match.css({ "visibility": "visible", 'top': $this.position().top });
                        drop.css({ "visibility": "visible", 'top': $this.position().top + 28 });
                    }
                });
                return false;


            }

            body.on('scroll', function () {
                positionUISelects();
            });
            positionUISelects();

            $(window).on("orientationchange", function () {
                window.setTimeout(function () { onResize(); }, 300);
            });
        }
    }
}]);