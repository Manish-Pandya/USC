angular
    .module("poptop", [])
        .directive("poptop", function(){
            return {
                restrict: 'E',
                scope: {
                    content: "=",
                    label: "@",
                    title: "@",
                    wait: '@',
                    event: '@',
                    duration: '@'
                },
                template: '<span class="poptop-label"><a ng-if="label">{{label}}</a><div class="poptop" style="color:#555"><div class="poptop-title" ng-if="title">{{title}}<a class="icon-cancel-2 close pull-right" ng-if="event == \'click\' || event == \'touchstart\'"></a></div><div class="poptop-content">{{content}}</div></div></span>',
                replace:true,
                link: function (scope, element, attrs, controller) {
                    var event = scope.event || 'mouseover || mouseout';
                    //always support touch events

                    event = event + ' touchstart || touchend';

                    var wait = scope.wait || 100;
                    var duration = scope.duraction || 100;
                    var h = element.outerHeight();
                    var p = element.find(('.poptop'));

                    //position, then hide, the poptop
                    positionPopTop(element, p);
                    p.hide();
                    
                    if (event.indexOf('mouse') > -1) {
                        if (typeof element.hoverIntent == "function") {
                            element.hoverIntent(function () { $('.poptop').removeClass('popper-open'); p.toggle(duration).addClass('popper-open'); $('.poptop').not($(".popper-open")).hide(); }, function () { p.toggle(duration).removeClass('popper-open') });
                        } else {
                            element.hover(function () { $(".poptop").hide(); p.toggle(duration) }, function () { p.toggle(duration) });
                        }
                    }

                    element.on(event, function (e) { console.log('asdf'); if ($(e.target).hasClass('poptop-label') || $(e.target).parent().hasClass('poptop-label') || $(e.target).hasClass('close')) { $('.poptop').removeClass('popper-open'); p.toggle(duration).addClass('popper-open'); $('.poptop').not($(".popper-open")).hide(); } });
                    

                    function positionPopTop(e,p) {
                        window.setTimeout(function () {
                            p.css({ marginTop: -(p.height() + h + 35), marginLeft: -(p.outerWidth() / 2) + 50, position: 'absolute' });
                        }, 10);
                    }
                    //reposition if content changes
                    scope.$watch("content", function (newVal, oldVal) {
                        if (oldVal != newVal) {
                            p.show();
                            positionPopTop(element, p);
                            p.hide();
                        }
                    })

                   
                }
            }
        });