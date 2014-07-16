var hazardHub = angular.module('hazardHub', ['convenienceMethodModule','infinite-scroll','once']);

hazardHub.filter('makeUppercase', function () {
  return function (item) {
    return item.toUpperCase();
  };
});

hazardHub.directive('yaTree', function () {

    return {
        restrict: 'A',
        transclude: 'element',
        priority: 1000,
        terminal: true,
        compile: function (tElement, tAttrs, transclude) {

            var repeatExpr, childExpr, rootExpr, childrenExpr;

            repeatExpr = tAttrs.yaTree.match(/^(.*) in ((?:.*\.)?(.*)) at (.*)$/);
            childExpr = repeatExpr[1];
            rootExpr = repeatExpr[2];
            childrenExpr = repeatExpr[3];
            branchExpr = repeatExpr[4];

            return function link(scope, element, attrs) {
                console.log(scope);
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

                scope.$watch("SubHazards", function (root) {
                    var currentCache = [];
                    // Recurse the data structure
                    (function walk(SubHazards, parentNode, parentScope, depth) {
                        //console.log(children);
                        var i = 0,
                            n = SubHazards.length - 1,
                            last = n - 1,
                            cursor,
                            child,
                            cached,
                            childScope,
                            grandchildren;

                        // Iterate the children at the current level
                        for (i=0; i < n; ++i) {

                            // We will compare the cached element to the element in 
                            // at the destination index. If it does not match, then 
                            // the cached element is being moved into this position.
                            cursor = parentNode.childNodes[i];

                            child = SubHazards[i];
                            //console.log(child);
                            scope.getShowHazard(child);
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
                            if(child) grandchildren = child[childrenExpr];    
                           
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
/*

hazardHub.directive('uiNestedSortable', ['$parse', function ($parse) {

    'use strict';

    var eventTypes = 'Create Begin Sort Change BeforeStop Stop Update Receive Remove Over Out Activate Deactivate'.split(' ');

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

hazardHub.directive('buttongroup', function ($window) {
     return {
        restrict: 'A',
        link: function (scope, element, attrs) {
         // Observe the element's dimensions.
         scope.$watch(
            function () {
                return {
                    w:element.width(),
                };
            },
            function (newValue, oldValue) {
                if (newValue.w < 1200 && newValue.w !== 0) {
                    console.log(newValue.w);
                    element.addClass('small');
                }else{
                    element.removeClass('small');
                }
            },
            true
        );
    }
 }

});
*/
hazardHub.directive('buttongroup', ['$window', function($window) {
    return {
        restrict: 'A',
        link: function(scope, elem, attrs) {
            scope.onResize = function() {
                console.log('resize');
                w = elem.width();
                console.log(w+" | "+$($window).width());
                if(w<1200 && $($window).width()>1365){
                     elem.addClass('small');
                }else if(w<1140 && $($window).width()<1365){
                     elem.addClass('small');
                }else{
                    elem.removeClass('small');
                }

                console.log(elem.children().children().children('.leftThings').children());

                var btnWidth  = elem.children().children().children('.hazarNodeButtons').width();
                var leftWidth = w - btnWidth - 50;
                elem.children().children().children('.leftThings').width(leftWidth);
                elem.children().children().children('.leftThings').children('span').css({width:leftWidth-50+'px'});

            }
            scope.onResize();

            scope.$watch(
                function(){
                    return scope.onResize();
                }
                
            )

            angular.element($window).bind('resize', function() {
                scope.onResize();
            });
        }
    }
}])

hazardHub.factory('hazardHubFactory', function(convenienceMethods,$q){
    var factory = {};
    factory.saveHazard = function(hazard){
        var url = "../../ajaxaction.php?action=saveHazard";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, hazard).then(
            function(promise){
                deferred.resolve(promise);
            },
            function(promise){
                deferred.reject();
            }
        );  
        return deferred.promise;
    }
    return factory;
});

hazardHub.controller('TreeController', function ($scope, $timeout, convenienceMethods, hazardHubFactory) {

    init();
  
    //call the method of the factory to get users, pass controller function to set data inot $scope object
    //we do it this way so that we know we get data before we set the $scope object
    //
    function init(){
        $scope.doneLoading = false;
        //we pass 10000 as the id to this request because 10000 will always be the key_id of the root hazard
        convenienceMethods.getData('../../ajaxaction.php?action=getHazardTreeNode&id='+10000+'&callback=JSON_CALLBACK', onGetHazards, onFailGet);
    }
    //grab set user list data into the $scrope object
    function onGetHazards (data) {
        delete data.doneLoading;
        $scope.SubHazards = data;
        $scope.doneLoading = true; 
    }

    function onFailGet(){
        $scope.doneLoading = "failed";
        if(confirm('There was a problem when loading the Hazard Tree.  Try Again?')){
            window.location.reload();  
        }
    }

    $scope.toggleMinimized = function (child, adding) {
        $scope.openedHazard = child;
        child.minimized = !child.minimized;
        if(!child.SubHazards){
            child.loadingChildren = true;
            if(adding)convenienceMethods.getData('../../ajaxaction.php?action=getHazardTreeNode&id='+child.Key_id+'&callback=JSON_CALLBACK', onGetSubhazards, onFailGetSubhazards, child, adding);
            if(!adding)convenienceMethods.getData('../../ajaxaction.php?action=getHazardTreeNode&id='+child.Key_id+'&callback=JSON_CALLBACK', onGetSubhazards, onFailGetSubhazards, child);
        }
    };


    //call back for asynch loading of a hazard's suhazards
    function onGetSubhazards (data, hazard, adding){
        hazard.loadingChildren = false;
        console.log(data);
       
        hazard.SubHazardsHolder = data;
        hazard.numberOfPossibleSubs = hazard.SubHazardsHolder.length;
        hazard.SubHazardsHolder[hazard.SubHazardsHolder.length-1].lastSub = true;

        console.log( hazard.SubHazardsHolder[hazard.SubHazardsHolder.length-1].Name);
        $scope.openedHazard = hazard;  

        var counter = Math.min(hazard.SubHazardsHolder.length-1, 2000 );
        if(adding)buildSubsArray(hazard, 0, counter, adding);
        if(!adding)buildSubsArray(hazard, 0, counter);
       // sorticus(hazard,adding);
       
    }

    function onFailGetSubhazards(){
        $scope.doneLoading = "failed";
        if(confirm('There was a problem when loading the Hazard Tree.  Try Again?')){
            window.location.reload();  
        }
    }

    $scope.setSubs = function(hazard, handlerCheck, adding){
       // console.log(test);
        if($scope.openedHazard && hazard.SubHazardsHolder){

            if(!$scope.openedHazard.SubHazards)$scope.openedHazard.SubHazards = [];
                
            //get the number of subhazards loaded, get the number of possible subhazards
            //if they are the same, do nothing because we have loaded all possible hazards
            if(hazard.SubHazardsHolder.length > hazard.SubHazards.length){
                if(handlerCheck == 'addToBottom'){
                    hazard.firstIndex += 5;
                    var numberOfHazardsLeftToPush = hazard.SubHazardsHolder.length - (hazard.firstIndex+15);
                    var start = hazard.numberOfPossibleSubs-(hazard.firstIndex+15);
                   
                    if(numberOfHazardsLeftToPush < 15){
                        start = hazard.SubHazardsHolder.length-15;
                    }else{
                        start = hazard.firstIndex;
                    }

                    limit = start + 14;
                    buildSubsArray(hazard, start, limit);
                    
                }
 
             if(handlerCheck == 'addToTop'){

                   if(hazard.firstIndex > 4){
                      hazard.firstIndex -= 5;  
                    }else{
                      hazard.firstIndex = 0;
                    }
                    var numberOfHazardsLeftToPush = hazard.SubHazardsHolder.length - (hazard.firstIndex+15);
                    var start = hazard.firstIndex;
                   
                    limit = start + Math.min(15,hazard.SubHazardsHolder.length);
                    buildSubsArray(hazard, start, limit);

                }
                
            }
        }
    }

    function buildSubsArray(hazard, start, limit, adding){
        //console.log('building');
        hazard.firstIndex = start;
        hazard.SubHazards = [];
        for(start; start<=limit; start++){
            hazard.SubHazardsHolder[start].displayIndex = start;
            hazard.SubHazards.push(hazard.SubHazardsHolder[start]);
        }
        if(adding)addChildCallback(hazard);
    }

    $scope.SubHazards = {
        SubHazards: []
    }
  
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

    $scope.addChild = function (child) {
        $scope.parentHazard = {};

        if(!child.HasChildren){
            child.SubHazards = [];
            addChildCallback(child);
        }else{
            if(!child.SubHazards){
               $scope.toggleMinimized(child, true);
            }else{
               addChildCallback(child);
            }
        }

    };

    function addChildCallback(child, copy){

        child.minimized = false;

        $scope.hazardCopy = {};

        $scope.parentHazard = child.Key_id;

        $scope.parentHazardForSplice = child;

        $scope.hazardCopy = {
            isNew: true,
            isBeingEdited: true,
            Name: '',
            Parent_hazard_id: child.Key_id,
            SubHazards: [],
            Class: 'Hazard',
            Is_active: true,
            HasChildren:false
        }

        child.SubHazards.unshift($scope.hazardCopy);
        child.HasChildren = true;
        console.log( $scope.hazardCopy );
    }

    $scope.saveEditedHazard = function(hazard){
        console.log($scope.hazardCopy);
        if(!$scope.hazardCopy.Class){
            $scope.hazardCopy.Class = "Hazard";
        }
        if(!hazard.Name || hazard.Name.trim() == ''){
            hazard.Invalid = true;
        }else{
            hazard.IsDirty = true;
            //var url = '../../ajaxaction.php?action=saveHazard';
           // convenienceMethods.updateObject( $scope.hazardCopy, hazard, onSaveHazard, onFailSave, url );

            hazardHubFactory.saveHazard($scope.hazardCopy).then(
                function(returnedHazard){
                    hazard.isBeingEdited = false;
                    hazard.IsDirty = false;
                    hazard.Invalid = false;
                    $scope.hazardCopy = {};
                    hazard.Name = returnedHazard.Name;
                    hazard.Key_id = returnedHazard.Key_id;
                },
                function(){
                    hazard.error = hazard.Name + ' could not be saved.  Please check your internet connection and try again.'
                }
            )

        }
    }
      //if this function is called, we have received a successful response from the server
    function onSaveHazard( dto, hazard, test ){

        //temporarily use our hazard copy client side to bandaid server side bug that causes subhazards to be returned as indexed instead of associative
        convenienceMethods.setPropertiesFromDTO( dto, hazard );
        hazard.isBeingEdited = false;
        hazard.IsDirty = false;
        hazard.Invalid = false;
        $scope.hazardCopy = {};
    }

    function onFailSave(obj){
        alert('There was a problem saving '+obj.Name);
    }
   

    $scope.cancelHazardEdit = function(hazard, $index){
    	console.log(hazard);
        if(hazard.isNew === true){
            return  $scope.parentHazardForSplice.SubHazards.splice( $scope.parentHazardForSplice.SubHazards.indexOf( hazard ), 1 );
        }

        hazard.isBeingEdited = false;
        $scope.hazardCopy = {};

    }

    $scope.handleHazardActive = function(hazard){
        hazard.IsDirty = true;
        $scope.hazardCopy = angular.copy(hazard);
        $scope.hazardCopy.Is_active = !$scope.hazardCopy.Is_active;
        if($scope.hazardCopy.Is_active === null)hazard.Is_active = false;
        var url = '../../ajaxaction.php?action=saveHazard';
        convenienceMethods.updateObject( $scope.hazardCopy, hazard, onSaveHazard, onFailSave, url );
    }

    //called when a hazard drag event is begun
    $scope.start = function(event, ui){
        $scope.event = event;
        $scope.ui = ui;

        var root = event.target,
            item = ui.item,
            parent = item.parent(),
            target =  (parent[0] === root) ? $scope.SubHazards : parent.scope(),
            child = item.scope().child,
            index = item.index();

        $scope.hazardsCopy = angular.copy($scope.SubHazards);

        $scope.previousParent  = target.child.KeyId;
    }

    //called when a Hazard drag has stopped
    $scope.update = function (event, ui) {
        $scope.event = event;
        $scope.ui = ui;

        var root = event.target,
            item = ui.item,
            parent = item.parent(),
            target =  (parent[0] === root) ? $scope.SubHazards : parent.scope(),
            child = item.scope().child,
            index = item.index();

            console.log(target);

        hazardDTO = {
            Class:         'Hazard',
            Key_id:         child.Key_id,
            Parent_hazard_id:  target.child.Key_id,
            index:         index,
            Name:          child.Name,
            Is_active:     child.Is_active,
            update:        true 
        }

        console.log(hazardDTO);

        //REST calls
        hazard.IsDirty;
        var url = '../../ajaxaction.php?action=saveHazard';
        convenienceMethods.updateObject( hazardDTO, child, onMoveHazard, onFailMove, url, hazardDTO ) ;
    };

    //called when a hazard is moved and the server successfully udpates accordingly
    onMoveHazard = function( hazardDTO, hazard ){
      
        convenienceMethods.setPropertiesFromDTO( hazardDTO, hazard );
        hazard.IsDirty = false;

        event = $scope.event;
        ui    = $scope.ui;

        var root = event.target,
            item = ui.item,
            parent = item.parent(),
            target =  (parent[0] === root) ? $scope.SubHazards : parent.scope(),
            child = item.scope().child,
            index = item.index();

        //if the location we are moving to has no subhazards, set up an empty array for our moved hazard to live in
        target.SubHazards || (target.SubHazards = []);
        
        //loop through the new parent
        function walk(target, child) {
         
            var children = target.SubHazards,
            i;

            if (children) {
                //console.log('here');
                i = children.length;
                while (i--) {
                    if (children[i] === child) {
                        //if we find a match for the element, splice if FROM the scope to prevent duplicates
                        //console.log('match found');   
                        return children.splice(i, 1);
                    } else {
                        //recurse down and look again for duplicate, assuring we never duplicate an object we mean to move
                        walk(children[i], child);
                    }
                }
            }
        }

        walk(target, child);
        
        //add the child to the $scope, placing it in the subhazards array of the parent target $scope object
        target.child.SubHazards.splice(index, 0, child);
    }

    //called when a hazard is moved and the server sends an error response
    onFailMove = function( hazard ){
        hazard.IsDirty = false;
        //set a flag property to indicate that we have tried to move this hazard.  This will call our watch expression to fire and reset the DOM tree of subhazards
        hazard.update = hazardDTO.update;
        alert('Something went wrong moving '+hazard.Name);
    }

    //by default, this is true.  This means that we will display hazards with a TRUE Is_active property
    //returns boolean to determine if a hazard should be shown or hidden based on user input and the hazard's Is_active property
    $scope.getShowHazard = function(hazard){
        hazard.show = false;
        //return true for all hazards if $scope.SubHazards.activeMatch is null or undefined
        if(!$scope.SubHazards[$scope.SubHazards.length-1].activeMatch) hazard.show = true;
        //if we have a $scope.activeMatch, return true for the hazards that have a matchin Is_active property.
        //i.e. display only hazards with a FALSE for Is_active if $scope.SubHazards.activeMatch is false 
        //The server will give us 0 or 1 boolean for these values.  0 and 1 are not actual boolean values in JS, so we must to a two step check here.
        if(hazard.Is_active == 0 || hazard.Is_active == false && $scope.SubHazards[$scope.SubHazards.length-1].activeMatch === false){
            hazard.show = true;
            return
        }
        if(hazard.Is_active == 1 || hazard.Is_active == true && $scope.SubHazards[$scope.SubHazards.length-1].activeMatch === true){
            hazard.show = true;
            return;
        }
        console.log(hazard);
       
    }

    $scope.hazardFilter = function(hazard){
      if($scope.hazardFilterSetting.Is_active == 'both'){
        return true;  
      }else if($scope.hazardFilterSetting.Is_active == 'active'){
        if(hazard.Is_active == true)return true;
      }else if($scope.hazardFilterSetting.Is_active == 'inactive'){
        if(hazard.Is_active == false)return true;
      }
      return false;
    }

    $scope.moveHazard = function(idx, parent, direction){
        
        //Make a copy of the hazard we want to move, so that it can be temporarily moved in the view
        var clickedHazard   = angular.copy(parent.SubHazards[idx]);
        parent.SubHazards[idx].IsDirty = true;
        console.log(clickedHazard);
        if(direction == 'up'){
            //We are moving a hazard up. Get the indices of the two hazards above it.
            var afterHazardIdx = idx-1;
            var beforeHazardIdx = idx-2;
        }else if(direction == 'down'){
            //We are moving a hazard down.  Get the indices of the two hazards below it.
            var beforeHazardIdx = idx+1;
            var afterHazardIdx = idx+2;
        }else{
            return
        }

        //get the key_ids of the hazards involved so we can build the request.
        var hazardId       = parent.SubHazards[idx].Key_id;
        var beforeHazardId = parent.SubHazards[beforeHazardIdx].Key_id;
        var afterHazarId   = parent.SubHazards[afterHazardIdx].Key_id;
        var url = '../../ajaxaction.php?action=reorderHazards&hazardId='+hazardId+'&beforeHazardId='+beforeHazardId+'&afterHazarId='+afterHazarId;

        //make the call
        convenienceMethods.saveDataAndDefer(url, clickedHazard).then(function(promise){
            console.log(promise);
            parent.SubHazards[idx].IsDirty = false;
            parent.SubHazards = promise;
        });
    }


    $scope.order = function(hazard){
        return parseFloat(hazard.Order_index);
    }
});  