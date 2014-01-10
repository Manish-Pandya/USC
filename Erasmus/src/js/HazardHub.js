var hazardHub = angular.module('hazardHub', ['ui.bootstrap']);

hazardHub.factory('hazardHubFactory', function($http){
    
    //initialize a factory object
    var tempFactory = {};
    
    //simple 'getter' to grab data from service layer
    tempFactory.getHazardData = function(onSuccess, url){
    //user jsonp method of the angularjs $http object to request data from service layer
        $http.jsonp(url)
            .success( function(data) {  
               console.log(url);
               onSuccess(data);
            })
            .error(function(data, status, headers, config){
                alert('error');
                console.log(headers());
                console.log(status);
                console.log(config);
            });
    };
    return tempFactory;
});
    
hazardHub.directive('yaTree', function () {

    return {
        restrict: 'A',
        transclude: 'element',
        priority: 1000,
        terminal: true,
        compile: function (tElement, tAttrs, transclude) {

            console.log(transclude);

            var repeatExpr, childExpr, rootExpr, childrenExpr;

            repeatExpr = tAttrs.yaTree.match(/^(.*) in ((?:.*\.)?(.*)) at (.*)$/);
            childExpr = repeatExpr[1];
            rootExpr = repeatExpr[2];
            childrenExpr = repeatExpr[3];
            branchExpr = repeatExpr[4];

            return function link(scope, element, attrs) {

                var rootElement = element[0].parentNode,
                    cache = [];

                // Reverse lookup object to avoid re-rendering elements
                function lookup(child) {
                    var i = cache.length;
                    while (i--) {
                        if (cache[i].scope[childExpr] === child) {
                            return cache.splice(i, 1)[0];
                        }
                    }
                }

                scope.$watch(rootExpr, function (root) {
                    var currentCache = [];

                    // Recurse the data structure
                    (function walk(SubHazards, parentNode, parentScope, depth) {
                        //console.log(children);
                        var i = 0,
                            n = SubHazards.length,
                            last = n - 1,
                            cursor,
                            child,
                            cached,
                            childScope,
                            grandchildren;

                        // Iterate the children at the current level
                        for (; i < n; ++i) {

                            // We will compare the cached element to the element in 
                            // at the destination index. If it does not match, then 
                            // the cached element is being moved into this position.
                            cursor = parentNode.childNodes[i];

                            child = SubHazards[i];

                            //console.log(child);

                            // See if this child has been previously rendered
                            // using a reverse lookup by object reference
                            cached = lookup(child);

                            // If the parentScope no longer matches, we've moved.
                            // We'll have to transclude again so that scopes 
                            // and controllers are properly inherited
                            if (cached && cached.parentScope !== parentScope) {
                                cache.push(cached);
                                cached = null;
                            }

                            // If it has not, render a new element and prepare its scope
                            // We also cache a reference to its branch node which will
                            // be used as the parentNode in the next level of recursion
                            if (!cached) {
                                transclude(parentScope.$new(), function (clone, childScope) {

                                    childScope[childExpr] = child;

                                    cached = {
                                        scope: childScope,
                                        parentScope: parentScope,
                                        element: clone[0],
                                        branch: clone.find(branchExpr)[0]
                                    };

                                    // This had to happen during transclusion so inherited 
                                    // controllers, among other things, work properly
                                    if (!cursor) parentNode.appendChild(cached.element);
                                    else parentNode.insertBefore(cached.element, cursor);


                                });
                            } else if (cached.element !== cursor) {
                                if (!cursor) parentNode.appendChild(cached.element);
                                else parentNode.insertBefore(cached.element, cursor);

                            }

                            // Lets's set some scope values
                            childScope = cached.scope;

                            // Store the current depth on the scope in case you want 
                            // to use it (for good or evil, no judgment).
                            childScope.$depth = depth;

                            // Emulate some ng-repeat values
                            childScope.$index = i;
                            childScope.$first = (i === 0);
                            childScope.$last = (i === last);
                            childScope.$middle = !(childScope.$first || childScope.$last);

                            // Push the object onto the new cache which will replace
                            // the old cache at the end of the walk.
                            currentCache.push(cached);

                            // If the child has children of its own, recurse 'em.             
                            grandchildren = child[childrenExpr];
                           // console.log(childrenExpr);
                            if (grandchildren && grandchildren.length) {
                                walk(grandchildren, cached.branch, childScope, depth + 1);
                            }
                        }
                    })(root, rootElement, scope, 0);

                    // Cleanup objects which have been removed.
                    // Remove DOM elements and destroy scopes to prevent memory leaks.
                    i = cache.length;

                    while (i--) {
                        cached = cache[i];
                        if (cached.scope) {
                            cached.scope.$destroy();
                        }
                        if (cached.element) {
                            cached.element.parentNode.removeChild(cached.element);
                        }
                    }

                    // Replace previous cache.
                    cache = currentCache;

                }, true);
            };
        }
    };
});


hazardHub.directive('uiNestedSortable', ['$parse', function ($parse) {

    'use strict';

    var eventTypes = 'Create Start Sort Change BeforeStop Stop Update Receive Remove Over Out Activate Deactivate'.split(' ');

    return {
        restrict: 'A',
        link: function (scope, element, attrs) {

            var options = attrs.uiNestedSortable ? $parse(attrs.uiNestedSortable)() : {};

            angular.forEach(eventTypes, function (eventType) {

                var attr = attrs['uiNestedSortable' + eventType],
                    callback;

                if (attr) {
                    callback = $parse(attr);
                    options[eventType.charAt(0).toLowerCase() + eventType.substr(1)] = function (event, ui) {
                        scope.$apply(function () {
                            callback(scope, {
                                $event: event,
                                $ui: ui
                            });
                        });
                    };
                }

            });
            
            //note the item="{{child}}" attribute on line 17
            options.isAllowed = function(item, parent) {
                if (!parent) return false;
                var attrs = parent.context.attributes;
                parent = attrs.getNamedItem('item');
                attrs = item.context.attributes;
                item = attrs.getNamedItem('item');
               // console.log(item, parent);
                //if ( ... ) return false;
               return true;
                };

            element.nestedSortable(options);

        }
    };
}]);  


hazardHub.controller('TreeController', function ($scope, $timeout, hazardHubFactory) {

    init();
  
    //call the method of the factory to get users, pass controller function to set data inot $scope object
    //we do it this way so that we know we get data before we set the $scope object
    //
    function init(){
      hazardHubFactory.getHazardData(onGetHazards,'http://erasmus.graysail.com/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&hazards=true');
    }
    //grab set user list data into the $scrope object
    function onGetHazards (data) {
        $scope.SubHazards = data;
    }

    $scope.SubHazards = {
        SubHazards: []
    }
    $scope.toggleMinimized = function (child) {
        child.minimized = !child.minimized;
    };

    $scope.addChild = function (child) {

        $scope.parentHazard = {};

        if(!child.hasOwnProperty('SubHazards')){
            child.SubHazards = [];
        }

        child.minimized = false;

        $scope.hazardCopy = {};

        $scope.parentHazard = child;

        child.SubHazards.unshift({
            isNew: true,
            isBeingEdited: true,
            title: '',
            SubHazards: []
        });
    };

    $scope.remove = function (child) {
        function walk(target) {
            var children = target.SubHazards,
                i;
            if (children) {
                i = children.length;
                while (i--) {
                    if (children[i] === child) {
                        return children.splice(i, 1);
                    } else {
                        walk(children[i])
                    }
                }
            }
        }
        walk($scope.SubHazards);
    }

    $scope.editHazard = function(hazard){
    
        hazard.isBeingEdited = true;
        $scope.hazardCopy = angular.copy(hazard);

    }

    $scope.saveEditedHazard = function(hazard){

        hazard.isBeingEdited = false;
        hazard.Name = $scope.hazardCopy.Name;

    }

    $scope.cancelHazardEdit = function(hazard, $index){
     
        if(hazard.isNew === true){
            console.log(hazard);
            console.log($scope.parentHazard);
            return $scope.parentHazard.SubHazards.splice( $scope.parentHazard.SubHazards.indexOf( hazard ), 1 );
        }

        hazard.isBeingEdited = false;
        $scope.hazardCopy = {};

    }

    $scope.update = function (event, ui) {
       // console.log(ui);

        var root = event.target,
            item = ui.item,
            parent = item.parent(),
            target = $scope.SubHazards,
            child = item.scope().child,
            index = item.index();

           console.log('item');
           console.log(item);

        //if the location we are moving to has no subhazards, set up an empty array for our moved hazard to live in
        target.SubHazards || (target.SubHazards = []);
        
        //loop through the new parent
        function walk(target, child) {
            //console.log('target');
            //console.log(target);
            var children = target.SubHazards,
                i;

            if (children) {
                console.log('here');
                i = children.length;
                while (i--) {
                    if (children[i] === child) {
                        //if we find a match for the element, splice if FROM the scope to prevent duplicates
                        console.log('match found');
                        console.log(children)
                        return children.splice(i, 1);
                    } else {
                        //recurse down and look again for duplicate, assuring we never duplicate an object we mean to move
                        console.log('rewalking');
                        walk(children[i], child);
                    }
                }
            }
        }

        walk(target, child);

        //add the child to the $scope, placing it in the subhazards array of the parent target $scope object
        target.SubHazards.splice(index, 0, child);
    };

});  