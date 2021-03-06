//This module provides a directive to calculate the height of a bootstrap modal and position it accordingly
angular.module('modalPosition', [])

.directive('modal', ['$window', function ($window) {
    return {
        restrict: 'C',
        link: function (scope, element, attributes) {
            var onResize = function () {
                var topMargin = $window.innerHeight - element[0].clientHeight;
                $(element[0]).css({ maxHeight: $window.innerHeight * .95, minHeight: '200px' });
                $(element[0]).find('.modal-content').css({ maxHeight: ($window.innerHeight * .95 - 50), minHeight: '200px' });
                $(element[0]).css({ top: (topMargin / 2) - 20, marginTop: -10 });
                $(element[0]).find('.modal-body').css({ maxHeight: $window.innerHeight * .85 - 150 });
                //overflowY: 'auto',
                if ($(element[0]).find('.modal-body').css('overflowY') != 'visible') {
                    $(element[0]).find('.modal-body').css({ overflowY: 'auto'});
                }
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
                if ($('.very-wide-modal').length) {
                    $(element[0]).width($window.innerWidth * .98);
                    $(element[0]).css({ 'left': $window.innerWidth * .005 + 'px', 'marginLeft': 0 });
                }
                if ($('.use-log-modal').length) {
                    $(element[0]).width(800);
                    $(element[0]).css({ 'left': ($window.innerWidth - 800) / 2 + 'px', 'marginLeft': 0, maxHeight: $window.innerHeight * .95 });
                    if ($(".max-height").length) {
                        $(element[0]).find('.modal-body').css({ maxHeight: $window.innerHeight * .85 });
                    }
                    var topMargin = $window.innerHeight - element[0].clientHeight;

                    $(element[0]).css({ top: (topMargin / 2) - 20, marginTop: -10 });
                }

                if ($('.multiple-disposal-modal').length) {
                    $(element[0]).width(900);
                    $(element[0]).css({ 'left': ($window.innerWidth - 900) / 2 + 'px', 'marginLeft': 0, maxHeight: $window.innerHeight * .95 });
                    $(element[0]).find('.modal-body').css({ maxHeight: $window.innerHeight * .85 });
                    var topMargin = $window.innerHeight - element[0].clientHeight;
                    $(element[0]).css({ top: (topMargin / 2) - 20, marginTop: -10 });
                }
            }


            onResize();


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
                return (
                    $(el).position().top >= 40 &&
                    (rect.bottom <= (body.outerHeight() + 83)  || rect.bottom <= $(window).height() )
                );
            }


            var drops;
            var positionUISelects = function () {
                scope.things = body.find(".ui-select-container");
                if (!scope.things || !scope.things.length) return;
                if (!body.hasClass("scrolled")) {
                    body.addClass("scrolled");
                    setTimeout(function () {
                        onResize();
                        var h = body.height();
                        body.css({ 'height': 10000 + 'px' });
                        body.animate({
                            scrollTop: 2000
                        }, .1);
                        body.animate({
                            scrollTop: 0
                        }, .1);
                        body.css({ 'height': h + 'px' });
                        onResize();
                    }, 301)

                }
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
                        arrow.css({ "visibility": "hidden", 'top': $this.position().top});
                        match.css({ "visibility": "hidden", 'top': $this.position().top, "width":$this.width()*.9 });
                        drop.css({ "visibility": "hidden", 'top': $this.position().top });
                    } else {
                        arrow.css({ "visibility": "visible", 'top': $this.position().top });
                        match.css({ "visibility": "visible", 'top': $this.position().top, "width":$this.width()*.9 });
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
            angular.element($(element[0])).bind('DOMNodeInserted', function () {
                onResize();
                positionUISelects();
            })
            angular.element($window).bind('resize', function () {
                onResize();
                positionUISelects();
            });

        }
    }
}]);