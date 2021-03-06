var hazardHub = angular.module('hazardHub', ['convenienceMethodWithRoleBasedModule','infinite-scroll','once','angular.filter', 'cgBusy']);

hazardHub.filter('makeUppercase', function () {
  return function (item) {
    return item.toUpperCase();
  };
});

hazardHub.directive('buttongroup', ['$window', function($window) {
    return {
        restrict: 'A',
        link: function(scope, elem, attrs) {
            console.log(elem.find('.hazarNodeButtons').length);
            scope.onResize = function() {
                w = elem.width();
                if(w<1200 && $($window).width()>1365){
                     elem.addClass('small');
                }else if(w<1140 && $($window).width()<1365){
                     elem.addClass('small');
                }else{
                    elem.removeClass('small');
                }

                //this code ensures that the hazard names, buttons and toggle buttons all line up properly, displaying cleanly even with linebreaks

                //get the width of the container element of for our buttons
                var btnWidth  = elem.children().children().children('.hazarNodeButtons').width();
                //set the width of all the elements on the left side of our hazard li elements
                var leftWidth = w - btnWidth - 50;
                elem.children().children().children('.leftThings').width(leftWidth);
                elem.children().children().children('.leftThings').children('span').css({width:leftWidth-90+'px'});

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
        var url = "../../ajaxaction.php?action=saveHazardWithoutReturningSubHazards";
        var deferred = $q.defer();
        convenienceMethods.saveDataAndDefer(url, hazard).then(
            function(promise){
                console.log(promise);
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

hazardHub.controller('TreeController', function ($scope, $q, $modal, $rootScope, $timeout, $location, $anchorScroll, convenienceMethods, hazardHubFactory, roleBasedFactory, $rootScope, $http) {

    init();

    $scope.getPisAndRoomsByHazard = function (hazard) {
        var url = '../../ajaxaction.php?action=getPisAndRoomsByHazard&id=' + hazard.Key_id + "&callback=JSON_CALLBACK";
        $http.jsonp(url).then(function (data) {
            $rootScope.selectedHazard = hazard;
            $rootScope.rels = data;
            console.log(data.data.length);
            var modalInstance = $modal.open({
                templateUrl: './hazard-pi-modal.html',
                controller: 'HazarPiCtrl'
            });
            modalInstance.result.then(function (arr) {

            });
        }).then
    }
    //call the method of the factory to get users, pass controller function to set data inot $scope object
    //we do it this way so that we know we get data before we set the $scope object
    //
    function init() {
        $rootScope.rbf = roleBasedFactory;
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
        $scope.error = null;
        $scope.openedHazard = child;
        child.minimized = !child.minimized;
        if(!child.SubHazards){
            child.loadingChildren = true;
            convenienceMethods.getDataAsDeferredPromise('../../ajaxaction.php?action=getHazardTreeNode&id='+child.Key_id+'&callback=JSON_CALLBACK')
                .then(function(subs){
                    child.SubHazards = subs;
                    child.loadingChildren = false;
                }, function(){
                    child.loadingChildren = false;
                    $scope.error = "There was a problem loading the list of Subhazard for " +child.Name+ ".  Please check your internet connection."
                });
        }
    };


    //call back for asynch loading of a hazard's suhazards
    function onGetSubhazards (data, hazard, adding){
        hazard.loadingChildren = false;

        hazard.SubHazardsHolder = data;
        hazard.numberOfPossibleSubs = hazard.SubHazardsHolder.length;
        hazard.SubHazardsHolder[hazard.SubHazardsHolder.length-1].lastSub = true;

      //  //console.log( hazard.SubHazardsHolder[hazard.SubHazardsHolder.length-1].Name);
        $scope.openedHazard = hazard;

       // var counter = Math.min(hazard.SubHazardsHolder.length-1, 2000 );
       // if(adding)buildSubsArray(hazard, 0, counter, adding);
        //if(!adding)buildSubsArray(hazard, 0, counter);

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
        if(!$scope.hazardCopy.Class){
            $scope.hazardCopy.Class = "Hazard";
        }
        if(!hazard.Name || hazard.Name.trim() == ''){
            hazard.Invalid = true;
        }else{
            hazard.IsDirty = true;

            // server lazy loads subhazards, save any subhazards present to re-add manually.
            var previousSubHazards = hazard.SubHazards || [];

            hazardHubFactory.saveHazard($scope.hazardCopy).then(
                function(returnedHazard){
                    hazard.isBeingEdited = false;
                    hazard.IsDirty = false;
                    hazard.Invalid = false;
                    $scope.hazardCopy = {};

                    if(previousSubHazards !== null && previousSubHazards.length !== 0) {
                        // restore subhazards
                        returnedHazard.SubHazards = previousSubHazards;
                        onGetSubhazards(previousSubHazards, hazard);
                    }

                    angular.extend(hazard, returnedHazard);
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
        console.log(hazard);
        console.log(dto);
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
        hazard.IsDirty = true;

        // server lazy loads subhazards, save any subhazards present to re-add manually.
        var previousSubHazards = hazard.SubHazards || [];

        hazardHubFactory.saveHazard($scope.hazardCopy).then(
            function(returnedHazard){
                hazard.isBeingEdited = false;
                hazard.IsDirty = false;
                hazard.Invalid = false;
                $scope.hazardCopy = {};

                if(previousSubHazards !== null && previousSubHazards.length !== 0) {
                    // restore subhazards
                    returnedHazard.SubHazards = previousSubHazards;
                    onGetSubhazards(previousSubHazards, hazard);
                }

                angular.extend(hazard, returnedHazard);
                hazard.Key_id = returnedHazard.Key_id;
            },
            function(){
                hazard.error = hazard.Name + ' could not be saved.  Please check your internet connection and try again.'
            }
        )
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

    $scope.moveHazard = function(idx, parent, direction, filteredSubHazards){
        //Make a copy of the hazard we want to move, so that it can be temporarily moved in the view
        var clickedHazard   = angular.copy(parent.SubHazards[idx]);
        filteredSubHazards[idx].IsDirty = true;
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
        var hazardId       = filteredSubHazards[idx].Key_id;

        //if we are moving the hazard up to the first spot, the index for the before hazard will be - 1, so we can't get a key_id
        if(beforeHazardIdx > -1){
            var beforeHazardId = filteredSubHazards[beforeHazardIdx].Key_id;
        }else{
            var beforeHazardId = null
        }

        //if we are moving the hazard down to the last spot, the index for the before hazard will out of range, so we can't get a key_id
        if(afterHazardIdx < filteredSubHazards.length){
            var afterHazardId = filteredSubHazards[afterHazardIdx].Key_id;
       }else{
            var afterHazardId = null;
       }

        var url = '../../ajaxaction.php?action=reorderHazards&hazardId='+hazardId+'&beforeHazardId='+beforeHazardId+'&afterHazardId='+afterHazardId;

        //make the call
        $scope.loading = convenienceMethods.saveDataAndDefer(url, clickedHazard).then(
            function(promise){
                filteredSubHazards[idx].IsDirty = false;
                filteredSubHazards[idx].Order_index = promise.Order_index;
            },
            function(){
                filteredSubHazards.error = true;
                $scope.error="The hazard could not be moved.  Please check your internet connection.";
            }
        );
    }

    $scope.order = function(hazard){
        return parseFloat(hazard.Order_index);
    }

    
});
hazardHub.controller('HazarPiCtrl', function ($scope, $rootScope, $modalInstance) {
    $scope.cancel = function () { $modalInstance.dismiss() }
    console.log(_);
    $scope.calculatePis = function(pis) {
        //pis.filt
        return _.uniqBy(pis, "Principal_investigator_id").length;
    }
    $scope.calculateRooms = function (rooms) {
        return _.uniqBy(rooms, "Room_id").length;
    }
})