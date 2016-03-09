angular
  .module('SideNav',[])
    .directive('sideNav', function ($parse) {
        return {
            restrict: 'A',
            scope: {
                sideNav: "@",
                width: "@"
            },
            link: function (scope, element, attrs, controller) {

                if (!element.attr('width') && scope.sideNav == 'open') {
                    element.attr('width', element.width());
                }

                var width;
                element.find('.nav-toggle').on("click", function () {
                    if (scope.sideNav == 'open') {
                        scope.sideNav = 'closed';
                        width = element.attr('narrow-width') || 50
                        element.attr('side-nav', 'closed');
                    } else {
                        scope.sideNav = 'open';
                        width = element.attr('width') || 300
                        element.attr('side-nav', 'open');
                    }
                    element.animate({
                        width: width
                    }, { duration: 200, queue: false });
                    $('.right-column').animate({
                        marginLeft: width
                    }, { duration: 200, queue: false });
                    
                })
                
            }
        }
    })