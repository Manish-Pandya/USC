$(".collapse").bind("transition webkitTransition oTransition MSTransition", function(){
    alert('transitions');
});


var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu', 'convenienceMethodWithRoleBasedModule', 'once', 'angular.filter', 'cgBusy'])
.filter('categoryFilter', function () {
    return function (items, category ) {
            if( !category ) return false;
            var i = items.length;
            var filtered = [];
            while(i--){
                var item = items[i];
                if( item.Master_hazard.toLowerCase().indexOf(category.toLowerCase()) > -1 )	filtered.unshift( item );
            }
            return filtered;

    }
})
.filter('countRecAndObs', function () {
    return function ( questions ) {
            if( !questions ) return;
            var i = questions.length;
            while(i--){
                var question = questions[i];
                question.checkedRecommendations = 0;
                if(question.Responses && question.Responses.Recommendations)question.checkedRecommendations = question.Responses.Recommendations.length;
                if(question.Responses && question.Responses.SupplementalRecommendations){
                    var j = question.Responses.SupplementalRecommendations.length;
                    while(j--){
                        if(question.Responses.SupplementalRecommendations[j].Is_active)question.checkedRecommendations++;
                    }
                }

                question.checkedNotes = 0;
                if(question.Responses && question.Responses.Observations)question.checkedNotes = question.Responses.Observations.length;
                if(question.Responses && question.Responses.SupplementalObservations){
                    var j = question.Responses.SupplementalObservations.length;
                    while(j--){
                        if(question.Responses.SupplementalObservations[j].Is_active)question.checkedNotes++;
                    }
                }
            }
            return questions;
    }
})
.filter('roomChecked', function (checklistFactory) {
    return function (rooms, question, deficiency ) {
        if (!rooms) return;
        matches = [];
        for (var i = 0; i < rooms.length; i++) {
            if (checklistFactory.evaluateDeficiencyRoomChecked(rooms[i], question, deficiency)) {
                rooms[i].checked = true;
                matches.push(rooms[i]);
            } else {
                rooms[i].checked = false;
            }
        }
        return matches;
    }
})
.filter('evaluateChecklist', function () {
    return function (questions, checklist) {
            checklist.completedQuestions = 0;
            if(!checklist.Questions) return questions;
            var i = checklist.Questions.length;
            checklist.activeQuestions = [];
            while(i--){
                var question = checklist.Questions[i];
                if(question.Is_active){
                    if( !question.Responses ){
                        question.isComplete = false;
                    }
                    else if( !question.Responses.Answer ){
                        question.isComplete = false;
                        //question doesn't have an answer but does have one or more recommendations selected
                        if(question.Responses.Recommendations && question.Responses.Recommendations.length){
                            question.isComplete = true;
                        }
                        if(question.Responses.SupplementalRecommendations && question.Responses.SupplementalRecommendations.length){
                            var j = question.Responses.SupplementalRecommendations.length;
                            while(j--){
                                if(question.Responses.SupplementalRecommendations.Is_active)question.isComplete = true;
                            }
                        }
                        if(question.isComplete){
                            checklist.completedQuestions++;
                        }
                    }
                    else if( question.Responses.Answer.toLowerCase() == "yes" || question.Responses.Answer.toLowerCase() == "n/a" ){
                        question.isComplete = true;
                        checklist.completedQuestions++;
                    }
                    //question is answered "no"
                    else{
                        //question has no deficiencies to select
                        if( !question.Responses.DeficiencySelections ){
                            question.isComplete = false;
                        }
                        //question has no deficiencies selected
                        else if( !question.Responses.DeficiencySelections.length ){
                            question.isComplete = false;
                        }
                        //question has one or more deficiencies selected
                        else{
                            question.isComplete = true;
                            checklist.completedQuestions++;
                        }
                    }
                    checklist.activeQuestions.unshift(question);
                }
            }
            return checklist.activeQuestions;
    }
})
.filter("showNavItem", function(checklistFactory){
        return function (items, inspection){
            if(!items)return;
            var relevantItems = [];
            var lists = checklistFactory.inspection.Checklists;
            if(!lists)return;
            for (var i = 0; i < items.length; i++) {
                var push = false;

                for (var j = 0; j < lists.length; j++){
                    if(lists[j].Is_active && lists[j].Master_id == items[i].Key_id){
                        if(!checklistFactory.selectedCategory)checklistFactory.selectCategory( items[i] );
                        push = true;
                    }
                }
                var skips = [];
                if (inspection) {
                    if (inspection.Is_rad) {
                        skips = ["Biological", "Chemical", "General"];
                        //select radiation category
                        if (items[i].Label == "Radiation") {
                            checklistFactory.selectCategory(items[i]);
                        }
                    } else {
                        skips = ["Radiation"];
                    }

                    if (skips.indexOf(items[i].Label) > -1) {
                        push = false;
                    }
                }
               
                if (push) relevantItems.push(items[i]);
            }
            return relevantItems;
        }
    }
)
.filter("relevantLists", function(checklistFactory){
        return function (checklists){
            if(!checklists) return;
            if(!checklistFactory.selectedCategory)return;
            var relevantLists = [];
            for(var i = 0; i < checklists.length; i++){
                var push = false;
                if(checklists[i].Is_active && checklists[i].Master_id == checklistFactory.selectedCategory.Key_id){
                    relevantLists.push( checklists[i] );
                }
            }
            return relevantLists;
        }
    }
)
.directive("otherDeficiency",["checklistFactory", function(checklistFactory){
    return {
        restrict: "E"  , //E = element, A = attribute, C = class, M = comment
        replace: true,
        scope:{
            //scope variables we pass from view
            // i.e.  thing: '=' means that scope.thing, within the scope of the directive will be whatever you set the thing attribute of the directive markup to (<other-dificiency thing="someStuffFromTheViewScope")
            // thing: "="  //local scope.thing is a two-way bound reference to view scope
            // thing: "@"  //local scope.thing is bound one way and our local scope is isolated from the view
            // thing: "&"  //use this when passing a method of the view scope that you want to call in the directive
            selectionChange:"&",
            selectedTitle:"=",
            unselectedTitle:"@",
            textAreaContent:"@",
            param: "=",
            paramChild: "=",
            checkedOnInit:"&",
            textareaPlaceholder:"@",
            saveCall: "&"
        },
        templateUrl:'otherDeficiencyComponent.html',  //path to template
        link:function(){
            //stuff we want to do to the view
            //jQuery style DOM manipulation
        },
        controller: function($scope, checklistFactory, $parse){
            $scope.selectionChange = $parse($scope.selectionChange);
            //create a referenceless copy of the thing we want to edit
            $scope.checkboxChanged = function() {
                $scope.selectionChange();
            }
            $scope.$watch("selectedTitle", function(){
                $scope.param.Other_text = $scope.selectedTitle;
            })

            $scope.edit = function(){
                $scope.param.edit = true;
                $scope.param.freeText = $scope.param.Other_text;
            }

            $scope.cancel = function(){
                $scope.param.edit = false;
                $scope.param.selected = false;
            }
        }
    }
}])
.factory('checklistFactory', function(convenienceMethods,$q,$rootScope,$timeout,$location,$anchorScroll){

        var factory = {};
        factory.inspection = [];
        factory.categories = Constants.CHECKLIST_CATEGORIES_BY_MASTER_ID;

        factory.getHasOtherDeficiencyies = function (question) {
            alert('wtf')
            if (question.Responses && question.Responses.DeficiencySelections) {
                if (!question.otherDefIds) question.otherDefIds = [];
                var i = question.Responses.DeficiencySelections.length;
                while(i--){
                    if (question.Responses.DeficiencySelections[i].Other_text && question.otherDefIds.indexOf(question.Responses.DeficiencySelections[i].Key_id) < 0) {
                        var otherDef = {
                            Class: "Deficiency",
                            Is_active: true,
                            Question_id: question.Key_id,
                            Other_text: question.Responses.DeficiencySelections[i].Other_text,
                            Deficiency_selection_id: question.Responses.DeficiencySelections[i].Key_id,
                            Text: "Other",
                            Key_id: Constants.INSPECTION.OTHER_DEFICIENCY_ID, //the id of the "Other" deficiency,
                            Selected: question.Responses.DeficiencySelections[i].Is_active
                        }
                        
                        otherDef.saved = true;

                        question.Deficiencies.push(otherDef);
                        question.otherDefIds.push(question.Responses.DeficiencySelections[i].Key_id);
                        console.log(question.otherDefIds)
                        console.log(question.Responses.DeficiencySelections[i].Other_text)
                        //question.Other_text = question.Responses.DeficiencySelections[i].Other_text;
                        //question.selected = question.Responses.DeficiencySelections[i].Is_active;
                        //if(question.Responses.DeficiencySelections[i].Is_active)return true;
                    }
                }
                if (!question.otherDefIds || !question.otherDefIds.length && !question.hasOther) {
                    var otherDef = {
                        Class: "Deficiency",
                        Is_active: true,
                        Question_id: question.Key_id,
                        Text: "Other",
                        Key_id: Constants.INSPECTION.OTHER_DEFICIENCY_ID, //the id of the "Other" deficiency,
                    }
                    question.hasOther = true;
                    question.Deficiencies.push(otherDef);
                }
            }
            return false;
        }

        factory.getInspection = function( id )
        {
            var deferred = $q.defer();
            //lazy load
            if(this.inspection.length){
                deferred.resolve(this.inspection);
            }else{
                var url = '../../ajaxaction.php?action=resetChecklists&id='+id+'&callback=JSON_CALLBACK';
                $rootScope.loading = convenienceMethods.getDataAsDeferredPromise(url).then(
                    function(promise){
                        deferred.resolve(promise);
                    },
                    function(promise){
                        deferred.reject();
                    }
                );
            }
            deferred.promise.then(
                function(inspection){
                    factory.inspection = inspection;
                }
            )
            return deferred.promise;

        }

        factory.conditionallySaveOtherDeficiency = function( question, room, deficiency )
        {
//            var deficiency = question.activeDeficiencies[question.activeDeficiencies.length -1];
            //set saving flag so view displays spinner
            question.saving = true;

            //find the right DeficiencySelection and update it's other text or Is_active property
            //On the c
            //do we already have  DeficiencySelection for this Other Deficiency?
            //if, it will have been set by the client
            if (deficiency.Deficiency_selection_id) {
            } else {
            }


            if(question.Responses.DeficiencySelections && question.Responses.DeficiencySelections.length){
                var i = question.Responses.DeficiencySelections.length;
                while(i--){
                    if(question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id){
                        var defSelection = question.Responses.DeficiencySelections[i];
                    }
                }
            }else{
                question.Responses.DeficiencySelections = [];
            }


            //grab a collection of room ids
            if( !deficiency.InspectionRooms || !deficiency.InspectionRooms.length) deficiency.InspectionRooms = convenienceMethods.copyObject( factory.inspection.Rooms );
            var i = deficiency.InspectionRooms.length;
            var roomIds = [];
            if(!room){
                //we haven't passed in a room, so we should set relationships for all possible rooms
                while(i--){
                    roomIds.push( deficiency.InspectionRooms[i].Key_id );
                }
            }
            else{
                this.room = room;
                while(i--){
                    if( deficiency.InspectionRooms[i].checked )roomIds.push( deficiency.InspectionRooms[i].Key_id );
                }
            }
            if (defSelection) {
                //find the right DeficiencySelection and update it's other text or Is_active property
                var i = question.Responses.DeficiencySelections.length;
                if(question.selected){
                    defSelection.Is_active = true;
                }else{
                    defSelection.Is_active = false;
                }

            } else {
                //no deficiency selection yet, build one
                var defSelection = {
                    Class: "DeficiencySelection",
                    Response_id: question.Responses.Key_id,
                    Deficiency_id: deficiency.Key_id,
                    Is_active: true,
                    Show_rooms: false
                }

            }
            defSelection.Other_text = question.freeText ? question.freeText : defSelection.Other_text;
            defSelection.RoomIds = roomIds;
            //make save call
            var url = '../../ajaxaction.php?action=saveOtherDeficiencySelection';
             return $rootScope.saving = convenienceMethods.saveDataAndDefer(url, defSelection).then(
                    function(returnedSelection){
                        if(!question.saved){
                            question.Responses.DeficiencySelections.push(returnedSelection);
                            factory.inspection.Deficiency_selections[0].push(returnedSelection.Deficiency_id);
                            var i = returnedSelection.Rooms.length;
                            while(i--){
                                returnedSelection.Rooms[i].checked = true;
                            }
                        }
                        question.edit = false;
                        question.freeText = returnedSelection.Other_text;
                        question.Other_text = returnedSelection.Other_text;
                        question.saving = false;
                        question.saved = true;
                        question.selected = returnedSelection.Is_active;
                    });

        }

        factory.setImage = function( id ) {
                if( id == 1 ){
                        return 'biohazard-largeicon.png';
                }else if( id == 10009  ){
                        return 'chemical-safety-large-icon.png';
                }else if( id == 9999 ){
                        return 'gen-hazard-large-icon.png';
                }else{
                        return 'radiation-large-icon.png';
                }
        }

        factory.selectCategory = function( category ) {
                $rootScope.loading = true;
                $rootScope.image = factory.setImage( category.Key_id );
                $rootScope.inspection = factory.inspection
                $rootScope.category = category;
                factory.selectedCategory = category;
                $rootScope.loading = false;

        }
        //pulls matching items out of an array and puts them in another array
        factory.findChecklistArray = function(checklists, parentId, idx){
            console.log(idx);
            console.log(checklists[idx]);
            var matches = [];
            for(var i = idx; i<checklists.length; i++){
                if(checklists[i].Parent_hazard_id == parentId)
                    matches.push(checklists[i]);
            }
            var i = matches.length;
            while(i--){
                checklists.splice(idx,0,matches[i]);
            }
        }

        factory.getParentIds = function(checklists){
            if(factory.parentIds == null){
                factory.parentIds = [];
                var i = checklists.length;
                while(i--){
                    if(factory.parentIds.indexOf(checklists[i].Parent_hazard_id < 0)){
                        factory.parentIds.push(checklists[i].Parent_hazard_id);
                    }
                }
            }
            return factory.parentIds;
        }

        factory.evaluateCategories = function ()
        {
                var i = this.inspection.Checklists.length;
                while(i--){
                    var list = this.inspection.Checklists[i].Master_hazard;
                    $rootScope[list.substring(0, list.indexOf(' ')).toLowerCase()] = true;
                }
        }

        factory.showRecommendations = function( question ){
            if(!question.showRecommendations)return;
            if(!question.Responses){
                question.showRecommendations = false;
                factory.saveResponse(question)
                    .then(
                        function(){
                            question.showRecommendations = true;
                        }
                    )
            }

        }

        factory.saveResponseSwitch = function( question )
        {
                var defer = $q.defer();

                if(question.Responses && question.Responses.Key_id){
                    defer.resolve(question.Responses.Key_id);
                    return defer.promise;
                }
                //the question doesn't have a reponse, so make a new one
                else{
                    return factory.saveResponse( question )
                        .then(
                            function(returnedResponse){
                                return returnedResponse.Key_id;
                            }
                        )
                }


        }

        factory.saveResponse = function( question )
        {
                question.error='';
                var copy = convenienceMethods.copyObject(question);
                if(!question.Responses){
                    question.Responses = {
                        Class: "Response",
                        Question_id: question.Key_id,
                    }
                }
                var copy = convenienceMethods.copyObject(question);

                var response = copy.Responses;

                question.IsDirty = true;

                var url = '../../ajaxaction.php?action=saveResponse';

                responseDto = convenienceMethods.copyObject(response);
                if(!response.Inspection_id)responseDto.Inspection_id = this.inspection.Key_id;
                if(!response.Question_id)responseDto.Question_id = question.Key_id;
                responseDto.Class = "Response";

                if(!responseDto.Answer)responseDto.Answer = '';

                question.Responses.Answer = null;
                var deferred = $q.defer();
                return $rootScope.saving = convenienceMethods.saveDataAndDefer(url, responseDto).then(
                    function(promise){
                        deferred.resolve(promise);
                        return deferred.promise
                            .then(
                                function(returnedResponse){
                                    question.IsDirty = false;
                                    response = convenienceMethods.copyObject( returnedResponse );
                                    if(!question.Responses.SupplementalObservations)question.Responses.SupplementalObservations = [];
                                    if(!question.Responses.SupplementalRecommendations)question.Responses.SupplementalRecommendations = [];
                                    if(!question.Responses.Observations)question.Responses.Observations = [];
                                    if(!question.Responses.Observations)question.Responses.Observations = [];
                                    question.Responses.Key_id = returnedResponse.Key_id;
                                    question.Responses.Answer = responseDto.Answer;
                                    return returnedResponse;
                                }
                            )
                    },
                    function(promise){
                        question.IsDirty = false;
                        deferred.reject();
                        question.error = "The response could not be saved.  Please check your internet connection and try again."
                    }
                );

        }

        factory.evaluateDeficiency = function( def, question ){
                if(!question.Responses || !question.Responses.DeficiencySelections || !question.Responses.DeficiencySelections.length)return false;
                var i = question.Responses.DeficiencySelections.length;
                var id = def.Key_id;
                while(i--){
                    if( id == question.Responses.DeficiencySelections[i].Deficiency_id ){
                        def.selected = true;
                        return true;
                    }
                }
                return false;

        }

        factory.evaluateDeficienyShowRooms = function( id ){
                var i = this.inspection.Deficiency_selections[2].length;
                while(i--){
                    if( id == this.inspection.Deficiency_selections[2][i] )return true;
                }
                return false;

        }

        factory.saveDeficiencySelection = function( deficiency, question, checklist, room )
        {
                deficiency.IsDirty = true;
                question.error =  '';
                if( !deficiency.InspectionRooms || !deficiency.InspectionRooms.length) deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
                //grab a collection of room ids
                var i = deficiency.InspectionRooms.length;
                var roomIds = [];
                if(!room){
                    //we haven't passed in a room, so we should set relationships for all possible rooms
                    while(i--){
                        roomIds.push( deficiency.InspectionRooms[i].Key_id );
                    }
                }
                else{
                    while(i--){
                        if( deficiency.InspectionRooms[i].checked )roomIds.push( deficiency.InspectionRooms[i].Key_id );
                    }
                    room.checked = !room.checked;
                    this.room = room;

                }

                var showRooms = false;
                if(roomIds.length < deficiency.InspectionRooms.length){
                    showRooms = true;
                }

                var defDto = {
                    Class: "DeficiencySelection",
                    RoomIds: roomIds,
                    Deficiency_id:  deficiency.Key_id,
                    Response_id: question.Responses.Key_id,
                    Inspection_id: this.inspection.Key_id,
                    Show_rooms:  showRooms
                }

                //make sure we are persisting the state of Other deficiency selections
                if(deficiency.Text == "Other"){
                    //find the right DeficiencySelection and update it's other text or Is_active property
                    var i = question.Responses.DeficiencySelections.length;
                    while(i--){
                        if(question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id){
                           defDto = question.Responses.DeficiencySelections[i];
                           defDto.Show_rooms = deficiency.Show_rooms;
                           defDto.RoomIds    = roomIds;
                        }
                    }
                }


                if( deficiency.selected || deficiency.Text == "Other"  /*we never delete "Other" deficiency selections, only deactivate them*/){
                        if(question.Responses && question.Responses.DeficiencySelections){
                            var j = question.Responses.DeficiencySelections.length;
                            while(j--){
                                var ds = question.Responses.DeficiencySelections[j];
                                if(deficiency.Key_id == ds.Deficiency_id)defDto.Key_id = ds.Key_id;
                            }
                        }

                        var url = '../../ajaxaction.php?action=saveDeficiencySelection';
                        $rootScope.saving = convenienceMethods.saveDataAndDefer(url, defDto)
                            .then(
                                function (returnedDeficiency) {
                                    deficiency.IsDirty = false;
                                    deficiency.selected = true;
                                    if( factory.inspection.Deficiency_selections[0].indexOf( deficiency.Key_id ) < 0){
                                        factory.inspection.Deficiency_selections[0].push( deficiency.Key_id );
                                    }
                                    if(!question.Responses.DeficiencySelections)question.Responses.DeficiencySelections = [];

                                    if(factory.room){
                                        room.checked = !room.checked;
                                        factory.room = !factory.room;
                                        //f no rooms are left checked for this deficiency, we remove it's key id from the Inspection's array of deficiency_selection ids
                                        if(roomIds.length == 0){
                                            factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections.indexOf( deficiency.Key_id, 1 ) )
                                        }
                                    } else {
                                        console.log(returnedDeficiency);
                                        for (var i = 0; i < returnedDeficiency.Rooms.length; i++) {
                                            returnedDeficiency.Rooms[i].checked = true;
                                        }
                                        question.Responses.DeficiencySelections.push(returnedDeficiency);
                                    }

                                },
                                function(promise){
                                    question.IsDirty = false;
                                    deferred.reject();
                                    deficiency.selected = false;
                                    question.error = "The response could not be saved.  Please check your internet connection and try again."
                                }
                            );
                }else{
                    var j = question.Responses.DeficiencySelections.length;
                    //get the key_id for our DeficiencySelection
                    while(j--){
                    if( question.Responses.DeficiencySelections[j].Deficiency_id == defDto.Deficiency_id ){
                          defDto.Key_id = question.Responses.DeficiencySelections[j].Key_id;
                          var defSelectIdx = j;
                        }
                    }
                    var url = '../../ajaxaction.php?action=removeDeficiencySelection';
                      $rootScope.saving = convenienceMethods.saveDataAndDefer( url, defDto )
                          .then(
                              function(returnedBool){
                                  deficiency.IsDirty = false;
                                deficiency.selected = false;
                                factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections[0].indexOf( deficiency.Key_id ), 1 );
                                 question.Responses.DeficiencySelections.splice( defSelectIdx, 1 );
                              },
                              function(error){
                                deficiency.IsDirty = false;
                                deficiency.selected = true;
                                question.error = "The response could not be saved.  Please check your internet connection and try again."
                              }
                          )
                }
        }

        factory.handleCorrectedDurringInspection = function( deficiency, question )
        {
            question.error='';
            deficiency.IsDirty = true;
            var def_id = deficiency.Key_id;
            //deficiency.correctedDuringInspection = !deficiency.correctedDuringInspection
            if( deficiency.correctedDuringInspection ){
              //we set corrected during inspection
              var url = '../../ajaxaction.php?action=addCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+this.inspection.Key_id+'&callback=JSON_CALLBACK';
            }else{
              //we unset corrected during inspection
              var url = '../../ajaxaction.php?action=removeCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+this.inspection.Key_id+'&callback=JSON_CALLBACK';
            }

            convenienceMethods.getDataAsPromise( url )
                  .then(
                      function(){
                          deficiency.IsDirty = false;
                      },
                      function(){
                          deficiency.correctedDuringInspection = !deficiency.correctedDuringInspection;
                          question.error = 'The deficiency could not be saved.  Please check your internet connection and try again.';
                          deficiency.IsDirty = false;
                      }
                  );
        }

        factory.changeChecklist = function( checklist )
        {
            checklist.currentlyOpen = !checklist.currentlyOpen;
            var insp = $location.search().inspection;
            //$location.hash(checklist.Key_id);
            $location.search('inspection',insp);
            $anchorScroll();
        }

        factory.evaluateDeficiencyRoomChecked = function( room, question, deficiency )
        {
            if (!question.Responses.DeficiencySelections) return false;
            var i = question.Responses.DeficiencySelections.length;
            while(i--){
                if( question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id ){
                    var j = question.Responses.DeficiencySelections[i].Rooms.length;
                    while(j--){
                        if( question.Responses.DeficiencySelections[i].Rooms[j].Key_id == room.Key_id ){
                            if(room.checked != false)return true;
                        }
                    }
                }
            }
            return false;
        }

        factory.copyForEdit = function( question, objectToCopy )
        {
            $rootScope[objectToCopy.Class+'Copy'] = convenienceMethods.copyObject( objectToCopy );
            $rootScope[objectToCopy.Class+'Copy'].edit = true;
            objectToCopy.edit = true;
            question.edit = true;
/*
            if(objectToCopy.Class.indexOf("Sup") < 0){
                question[objectToCopy.Class+'s'].push($rootScope[objectToCopy.Class+'Copy']);
            }
            else{
                question.Responses[objectToCopy.Class+'s'].push($rootScope[objectToCopy.Class+'Copy']);
            }
*/
        }

        factory.objectNullifactor = function( objectToNullify, question )
        {
            objectToNullify.edit = false;
            question.edit = false;
            $rootScope[objectToNullify.Class] = {};
        }

        factory.createRecommendation = function( question, id )
        {
            $rootScope.RecommendationCopy = {
                Class: "Recommendation",
                Question_id: question.Key_id,
                Text: question.newRecommendationText,
                edit: true,
                new: true,
                push: true,
                Is_active: true,
            }

            this.saveRecommendation( question, $rootScope.RecommendationCopy );

        }

        factory.createObservation = function( question )
        {
            $rootScope.ObservationCopy = {
                Class: "Observation",
                Question_id: question.Key_id,
                Text: question.newObservationText,
                edit: true,
                new: true,
                push: true,
                Is_active: true
            }

            this.saveObservation( question, $rootScope.ObservationCopy )
        }

        factory.saveObservation = function( question, observation )
        {
                if($rootScope.ObservationCopy.push)question.savingNew = true;
                question.error = '';
                observation.IsDirty = true;
                var url = '../../ajaxaction.php?action=saveObservation';
                      $rootScope.saving = convenienceMethods.saveDataAndDefer( url, $rootScope.ObservationCopy )
                          .then(
                              function(returnedObservation){
                                  factory.objectNullifactor($rootScope.ObservationCopy, question)
                                  if(!$rootScope.ObservationCopy.push){
                                      observation.edit = false;
                                      angular.extend(observation, returnedObservation)
                                  }
                                  else{
                                      returnedObservation.new = true;
                                      question.Observations.push(returnedObservation);
                                      question.newObservationText = '';
                                  }
                                  returnedObservation.IsDirty = false;
                                  returnedObservation.edit = false;
                                  returnedObservation.checked = true;
                                  observation.IsDirty = false;
                                  if(!observation.Key_id)factory.saveObservationRelation( question, returnedObservation );
                                  question.edit = false;
                                  question.savingNew = false;
                                  question.addNote = false;
                              },
                              function(error){
                                  returnedObservation.IsDirty = false;
                                question.error = "The note could not be saved.  Please check your internet connection and try again."
                                question.savingNew = false;
                              }
                          )

        }

        factory.saveRecommendation = function( question, recommendation )
        {
            if($rootScope.RecommendationCopy.push)question.savingNew = true;
            question.error = '';
            recommendation.IsDirty = true;
            var url = '../../ajaxaction.php?action=saveRecommendation';
                  $rootScope.saving = convenienceMethods.saveDataAndDefer( url, $rootScope.RecommendationCopy )
                      .then(
                          function(returnedRecommendation){
                              factory.objectNullifactor($rootScope.RecommendationCopy, question)
                              if(!$rootScope.RecommendationCopy.push){
                                  recommendation.edit = false;
                                  angular.extend(recommendation, returnedRecommendation);
                              }
                              else{
                                  returnedRecommendation.new = true;
                                  question.Recommendations.push(returnedRecommendation);
                                  question.newRecommendationText = '';
                              }
                              returnedRecommendation.IsDirty = false;
                              returnedRecommendation.edit = false;
                              returnedRecommendation.checked = true;
                              recommendation.IsDirty = false;
                              if(!recommendation.Key_id)factory.saveRecommendationRelation( question, returnedRecommendation );
                              question.edit = false;
                              question.savingNew = false;
                              question.addRec = false;
                          },
                          function(error){
                              returnedRecommendation.IsDirty = false;
                            question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                            question.savingNew = false;
                          }
                      )
        }

        factory.saveSupplementalObservation = function( question, isNew, so )
        {
            if(!question.Responses.SupplementalObservations)question.Responses.SupplementalObservations=[];
            var soDto = {
                Class: "SupplementalObservation",
                Text: question.newObservationText,
                response_id: question.Responses.Key_id
            }
            if(isNew){
                soDto.Is_active = true;
                question.savingNew = true;
            }
            else{
                soDto.Is_active = so.checked;
                so.IsDirty = false;
                soDto.Text = $rootScope.SupplementalObservationCopy.Text;
                so.IsDirty = true;
                soDto.Key_id = so.Key_id
            }
            question.error = '';
            var url = '../../ajaxaction.php?action=saveSupplementalObservation';
                  $rootScope.saving = convenienceMethods.saveDataAndDefer( url, soDto )
                      .then(
                          function( returnedSupplementalObservation ){
                              if( so ){
                                  soDto.checked = returnedSupplementalObservation.Is_active
                                  angular.extend(so, returnedSupplementalObservation)
                                  so.IsDirty = false;
                                  so.edit=false;
                              }
                              else{
                                  returnedSupplementalObservation.checked = true;
                                question.Responses.SupplementalObservations.push(returnedSupplementalObservation);
                                question.savingNew = false;
                              }
                              question.addNote = false;
                              if($rootScope.SupplementalObservationCopy)factory.objectNullifactor($rootScope.SupplementalObservationCopy, question)
                          },
                          function(error){
                              question.savingNew = false;
                              if(so)so.IsDirty = false;
                            question.error = "The note could not be saved.  Please check your internet connection and try again."
                          }
                      )

        }

        factory.saveSupplementalRecommendation = function( question, isNew, sr )
        {
            if(!question.Responses.SupplementalRecommendations)question.Responses.SupplementalRecommendations=[];
            var srDto = {
                Class: "SupplementalRecommendation",
                Text: question.newRecommendationText,
                response_id: question.Responses.Key_id
            }
            if(isNew){
                srDto.Is_active = true;
                question.savingNew = true;
            }
            else{
                srDto.Is_active = sr.checked
                srDto.Text = $rootScope.SupplementalRecommendationCopy.Text;
                sr.IsDirty = true;
                srDto.Key_id = sr.Key_id
            }
            question.error = '';
            var url = '../../ajaxaction.php?action=saveSupplementalRecommendation';
                  $rootScope.saving = convenienceMethods.saveDataAndDefer( url, srDto )
                      .then(
                          function( returnedSupplementalRecommendation ){
                            question.addRec = false;
                              if( sr ){
                                  srDto.checked = returnedSupplementalRecommendation.Is_active
                                  angular.extend(sr, returnedSupplementalRecommendation);
                                  sr.edit = false;
                                  sr.IsDirty = false;
                              }
                              else{
                                  returnedSupplementalRecommendation.checked = true;
                                question.Responses.SupplementalRecommendations.push(returnedSupplementalRecommendation);
                                question.savingNew = false;
                              }
                              question.newRecommendationText = '';
                              if($rootScope.SupplementalRecommendationCopy)factory.objectNullifactor($rootScope.SupplementalRecommendationCopy, question)
                          },
                          function(error){
                              question.savingNew = false;
                              if(sr)sr.IsDirty = false;
                            question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                          }
                      )
        }

        factory.saveRecommendationRelation = function( question, recommendation )
        {
            factory.saveResponseSwitch( question )
                .then(function(responseId){
                    recommendation.IsDirty = true;
                    recommendation.checked = !recommendation.checked;
                    question.error = ''
                    var relationshipDTO = {
                        Class:          "RelationshipDto",
                        Master_id :     responseId,
                        Relation_id:    recommendation.Key_id,
                        add:            !recommendation.checked
                    }
                    var url = '../../ajaxaction.php?action=saveRecommendationRelation';
                    $rootScope.saving = convenienceMethods.saveDataAndDefer( url, relationshipDTO )
                          .then(
                              function(){
                                  recommendation.checked = !recommendation.checked;
                                  //if the recommendation was checked, it should be added to the response so we can track the number of recommendations selected
                                  if(recommendation.checked){
                                      question.Responses.Recommendations.push(recommendation);
                                  }
                                  //if the recommendation was unchecked, we removed it from the response
                                  else{
                                      var i = question.Responses.Recommendations.length;
                                      while(i--){
                                          if(question.Responses.Recommendations[i].Key_id == recommendation.Key_id){
                                              question.Responses.Recommendations.splice(i,1);
                                          }
                                      }
                                  }
                                  recommendation.IsDirty = false;
                              },
                              function(error){
                                  recommendation.IsDirty = false;
                                question.error = "The recommendation could not be saved.  Please check your internet connection and try again."
                              }
                          )
                });

        }

        factory.saveObservationRelation = function(question, observation)
        {
            observation.IsDirty = true;
            observation.checked = !observation.checked;
            question.error = ''
            var relationshipDTO = {
                Class:          "RelationshipDto",
                Master_id :     question.Responses.Key_id,
                Relation_id:    observation.Key_id,
                add:            !observation.checked
            }
            var url = '../../ajaxaction.php?action=saveObservationRelation';
            $rootScope.saving = convenienceMethods.saveDataAndDefer( url, relationshipDTO )
                      .then(
                          function(){
                              observation.checked = !observation.checked;
                              if(observation.checked){
                                  question.Responses.Observations.push(observation);
                              }
                              //if the recommendation was unchecked, we removed it from the response
                              else{
                                  var i = question.Responses.Observations.length;
                                  while(i--){
                                      if(question.Responses.Observations[i].Key_id == observation.Key_id){
                                          question.Responses.Observations.splice(i,1);
                                      }
                                  }
                              }
                              observation.IsDirty = false;
                          },
                          function(error){
                              observation.IsDirty = false;
                            question.error = "The observation could not be saved.  Please check your internet connection and try again."
                          }
                      )
        }

        factory.getRecommendationChecked = function( question, recommendation )
        {
            if(!question.Responses)return false;
            if(recommendation.checked)return true;
            if(!question.Responses.Recommendations)question.Responses.Recommendations=[];
            var i = question.Responses.Recommendations.length;
            if(i==0)return false;
            var ids = [];
            while(i--)
            {
                ids.push(question.Responses.Recommendations[i].Key_id);
            }
            if( ids.indexOf(recommendation.Key_id ) >-1 )return true;
            return false;

        }

        factory.getObservationChecked = function( question, observation )
        {
            if(!question.Responses)return false;
            if(observation.checked)return true;
            if(!question.Responses.Observations)question.Responses.Observations=[];
            var i = question.Responses.Observations.length;
            if(i==0)return false;
            var ids = [];
            while(i--)
            {
                ids.push(question.Responses.Observations[i].Key_id);
            }
            if( ids.indexOf(observation.Key_id ) >-1 )return true;
            return false;

        }

        factory.supplementalRecommendationChanged = function( question, recommendation )
        {
            $rootScope.SupplementalRecommendationCopy = convenienceMethods.copyObject(recommendation)
            this.saveSupplementalRecommendation( question, false, recommendation, true );
        }

        factory.supplementalObservationChanged = function( question, observation )
        {
            $rootScope.SupplementalObservationCopy = convenienceMethods.copyObject(observation)
            this.saveSupplementalObservation( question, false, observation, true );
        }
        factory.savePi = function(pi)
        {
        var url = "../../ajaxaction.php?action=savePI";
        var deferred = $q.defer();
          $rootScope.saving = convenienceMethods.saveDataAndDefer(url, pi)
            .then(
              function(promise){
                deferred.resolve(promise);
              },
              function(promise){
                deferred.reject();
              }
            );
            return deferred.promise
        }

        return factory;
});

function checklistController($scope,  $location, $anchorScroll, convenienceMethods, $window, checklistFactory, $modal) {
    var cf = $scope.cf = checklistFactory;
    $scope.constants = Constants;

    if($location.search().inspection){
      $scope.inspId = $location.search().inspection;
      checklistFactory.getInspection( $scope.inspId )
          .then(
              function( inspection ){
                  checklistFactory.evaluateCategories();
              },
              function( error ){
                  $scope.error = "The system couldn't find the selected inspeciton.  Please check your internet connection and try again."
              }
          )
      }else{
          $scope.error = "No inspection specified."
      }

    $scope.showRooms = function( event, deficiency, element, checklist, question ){
        if(!deficiency.InspectionRooms){
            if(!checklist.InspectionRooms || !checklist.InspectionRooms.length)checklist.InspectionRooms = convenienceMethods.copyObject( cf.inspection.Rooms );
            //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
            deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
            console.log(checklist.InspectionRooms);
        }
       // checklistFactory.evaluateDeficiecnyRooms( question, checklist );

        event.stopPropagation();
        calculateClickPosition(event,deficiency,element);
        deficiency.showRoomsModal = !deficiency.showRoomsModal;
    }

    $scope.getNeedsRooms = function(deficiency, checklist, question){
        if(!deficiency.InspectionRooms){
            if(!checklist.InspectionRooms || !checklist.InspectionRooms.length)checklist.InspectionRooms = convenienceMethods.copyObject( cf.inspection.Rooms );
            //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
            deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
        }else{
            for (var i = 0; i < deficiency.InspectionRooms.length; i++ ){
                if(!cf.evaluateDeficiencyRoomChecked( deficiency.InspectionRooms[i], question, deficiency )) return true;
            }
                
        }
        return false;
        
        console.log(deficiency);
    }
    
    //get the position of a mouseclick, set a properity on the clicked hazard to position an absolutely positioned div
    function calculateClickPosition(event, deficiency, element){
        var x = event.clientX;
        var y = event.clientY+$window.scrollY;

        deficiency.calculatedOffset = {};
        deficiency.calculatedOffset.x = x-110;
        deficiency.calculatedOffset.y = y-185;
    }


  $scope.openNotes = function(){
     var modalInstance = $modal.open({
        templateUrl: 'hazard-inventory-modals/inspection-notes-modal.html',
        controller: commentsController
      });

      modalInstance.result.then(function () {

      });
  }



}

function commentsController ($scope, checklistFactory, $modalInstance, convenienceMethods, $q){
  $scope.cf=checklistFactory;
  var pi = checklistFactory.inspection.PrincipalInvestigator;
  $scope.pi = pi;
  $scope.piCopy = {
    Key_id: $scope.pi.Key_id,
    Is_active: $scope.pi.Is_active,
    User_id: $scope.pi.User_id,
    Inspection_notes: $scope.pi.Inspection_notes,
    Class:"PrincipalInvestigator"
  };


  $scope.close = function () {
    $modalInstance.dismiss();
  };

  $scope.edit = function(state){
    $scope.pi.editNote = state;
  }

  $scope.saveNote = function(){
    $scope.savingNote = true;
    $scope.error = null;

    checklistFactory.savePi($scope.piCopy)
      .then(
        function(returnedPi){
          angular.extend(checklistFactory.inspection.PrincipalInvestigator, returnedPi);
          $scope.savingNote = false;
          $scope.close();
          $scope.pi.editNote = false;
          $scope.pi.Inspection_notes = returnedPi.Inspection_notes;
        },
        function(){
          $scope.savingNote = false;
          $scope.error = "The Inspection Comments could not be saved.  Please check your internet connection and try again."
        }
      )
  }

}
