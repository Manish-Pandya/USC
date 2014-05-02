var hazardHub = angular.module('hazardHub', ['convenienceMethodModule','infinite-scroll']);
/*
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
                //alert('error');
                console.log(headers());
                console.log(status);
                console.log(config);
                onFailSave(data);
            });
    };

    return tempFactory;
});
    */
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
                        for (i=0; i < n; ++i) {

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

hazardHub.directive('buttongroup', function () {
     return {
        restrict: 'A',
        link: function (scope, element, attrs) {
         // Observe the element's dimensions.
         scope.$watch
         (
          function () {
           return {
             w:element.width(),
           };
          },
          function (newValue, oldValue) {
           if (newValue.w < 900 && newValue.w !== 0) {
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


hazardHub.controller('TreeController', function ($scope, $timeout, convenienceMethods) {

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
        console.log(data.SubHazards);
        $scope.SubHazards = data;
        $scope.doneLoading = true;
    }

    function onFailGet(){
        $scope.doneLoading = "failed";
        if(confirm('There was a problem when loading the Hazard Tree.  Try Again?')){
            window.location.reload();  
        }
    }

    $scope.toggleMinimized = function (child) {
        $scope.openedHazard = child;
        child.minimized = !child.minimized;
        if(!child.SubHazards){
            child.loadingChildren = true;
            convenienceMethods.getData('../../ajaxaction.php?action=getHazardTreeNode&id='+child.Key_id+'&callback=JSON_CALLBACK', onGetSubhazards, onFailGetSubhazards, child);
        }
    };


    //call back for asynch loading of a hazard's suhazards
    function onGetSubhazards (data, hazard){
        hazard.loadingChildren = false;
        console.log(hazard);
       
        hazard.SubHazardsHolder = data;
        hazard.SubHazardsHolder.sort(function(a,b) {return (a.Name > b.Name) ? 1 : ((b.Name > a.Name) ? -1 : 0);} );
        hazard.numberOfPossibleSubs = hazard.SubHazardsHolder.length;

        console.log( hazard.SubHazardsHolder );
        $scope.openedHazard = hazard;  

        var counter = Math.min(hazard.SubHazardsHolder.length-1, 15 );
        buildSubsArray(hazard, 0, counter);


    }

    function onFailGetSubhazards(){
        //child.loadingChildren = false;
        $scope.doneLoading = "failed";
        if(confirm('There was a problem when loading the Hazard Tree.  Try Again?')){
            window.location.reload();  
        }
    }

    $scope.setSubs = function(hazard,test){
       // console.log(test);
        if($scope.openedHazard && hazard.SubHazardsHolder){

            if(!$scope.openedHazard.SubHazards)$scope.openedHazard.SubHazards = [];
                
            //get the number of subhazards loaded, get the number of possible subhazards
            //if they are the same, do nothing because we have loaded all possible hazards
            if(hazard.SubHazardsHolder.length > hazard.SubHazards.length){
                if(test == 'addToBottom'){
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
/*
                if(test == 'spliceOutBottom'){
                     $scope.openedHazard.SubHazards.splice($scope.SubHazards.length,1);
                     hazard.lastIndex--;
                }
  */ 
             if(test == 'addToTop'){
                    console.log('to top');

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

                if(test == 'removeFromTop'){
                    
                }
                
            }
        }
    }

    function buildSubsArray(hazard, start, limit){
        //console.log('building');
        hazard.firstIndex = start;
        hazard.SubHazards = [];
        for(start; start<=limit; start++){
           // console.log(start);
            hazard.SubHazardsHolder[start].displayIndex = start;
            hazard.SubHazards.push(hazard.SubHazardsHolder[start]);
        }
        disabler.enable_scrolling();
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

        if(!child.hasOwnProperty('SubHazards')){
            child.SubHazards = [];
        }

        child.minimized = false;

        $scope.hazardCopy = {};

        $scope.parentHazard = child.Key_id;

	 $scope.parentHazardForSplice = child;



        child.SubHazards.unshift({
            isNew: true,
            isBeingEdited: true,
            Name: '',
            Parent_hazard_id: child.Key_id,
            SubHazards: [],
            Class: 'Hazard',
            Is_active: true
        });

        $scope.hazardCopy = angular.copy(child.SubHazards[child.SubHazards.length - 1]);
        console.log( $scope.hazardCopy );
    };

    $scope.saveEditedHazard = function(hazard){
        $scope.hazardCopy.Name = hazard.Name;
        console.log($scope.hazardCopy);
        if(!$scope.hazardCopy.Class){
            $scope.hazardCopy.Class = "Hazard";
        }
        if(!hazard.Name || hazard.Name.trim() == ''){
            hazard.Invalid = true;
        }else{
            hazard.IsDirty = true;
            var url = '../../ajaxaction.php?action=saveHazard';
            convenienceMethods.updateObject( $scope.hazardCopy, hazard, onSaveHazard, onFailSave, url );
        }
    }
      //if this function is called, we have received a successful response from the server
    function onSaveHazard( dto, hazard, test ){

        //temporarily use our hazard copy client side to bandaid server side bug that causes subhazards to be returned as indexed instead of associative
        dto = angular.copy($scope.hazardCopy);
        convenienceMethods.setPropertiesFromDTO( dto, hazard );
        hazard.isBeingEdited = false;
        hazard.IsDirty = false;
        hazard.Invalid = false;
    }

    function onFailSave(obj){
        alert('There was a problem saving '+obj.Name);
    }
   

    $scope.cancelHazardEdit = function(hazard, $index){
    	 console.log(hazard);
	 console.log($scope.parentHazardForSplice);
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

});  