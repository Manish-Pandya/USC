var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodWithRoleBasedModule','once'])
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
            selected:"@",
            selectedTitle:"=",
            unselectedTitle:"=",
            textAreaContent:"@",
            param:"=",
            checkedOnInit:"&",
            textareaPlaceholder:"@"
        },
        templateUrl:'otherDeficiencyComponent.html',  //path to template
        link:function(){
            //stuff we want to do to the view
            //jQuery style DOM manipulation
        },
        controller: function($scope, checklistFactory){
            $scope.freeText = '';
            $scope.selected = false;
            $scope.checkboxChanged = function() {
                $scope.freeText = $scope.selected ? $scope.textAreaContent : "";
                $scope.selectionChange();
            }
            $scope.$watch("selectedTitle", function(){
                console.log('changed to ' + $scope.selectedTitle);
            })
        }
    }
}])
.factory('checklistFactory', function(convenienceMethods,$q,$rootScope,$timeout,$location,$anchorScroll){

        var factory = {};
        factory.inspection = [];

        factory.getHasOtherDeficiency  = function(question){
            if(question.Responses && question.Responses.DeficiencySelections){
                var i = question.Responses.DeficiencySelections.length;
                while(i--){
                    if(question.Responses.DeficiencySelections[i].Other_text){
                        question.activeDeficiencies[question.activeDeficiencies.length-1].Other_text = question.Responses.DeficiencySelections[i].Other_text;
                        question.saved = true;
                        if(question.Responses.DeficiencySelections[i].Is_active)return true;
                    }
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
                convenienceMethods.getDataAsDeferredPromise(url).then(
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

        factory.conditionallySaveOtherDeficiency = function( localScope )
        {
            if (localScope.selected) {
                console.log("I am selected", localScope.freeText);
            } else {
                console.log("I am NOT selected", localScope.freeText);
            }
        }

        factory.setImage = function( category )
        {
                if( category == 'Biological Safety' ){
                        return 'biohazard-largeicon.png';
                }else if( category == 'Chemical Safety' ){
                        return 'chemical-safety-large-icon.png';
                }else if( category.toLowerCase().indexOf('general') > -1 ){
                        return 'gen-hazard-large-icon.png';
                }else{
                        return 'radiation-large-icon.png';
                }
        }

        factory.selectCategory = function( category )
        {
                //if(!category)category = "Biological Safety";
                $rootScope.loading = true;
                $rootScope.image = factory.setImage( category );
                $rootScope.inspection = factory.inspection
                $rootScope.inspection.selectedChecklists = [];
                $rootScope.category = category;
                var categoryLabel = category.split(" ")[0].toLowerCase();

                var len = factory.inspection.Checklists.length;
                var counter;
                var i = 0;
                var selectedChecklists = [];

                innerFilter();

                function innerFilter(){
                    for( counter = 0; i < len && counter < 3; counter++, i++){
                        var checklist = factory.inspection.Checklists[i];
                        if( checklist.Master_hazard.toLowerCase().indexOf(categoryLabel) > -1  )selectedChecklists.push( checklist );
                    }

                    if(i == len){
                        factory.inspection.selectedCategory = selectedChecklists;
                        $rootScope.loading = false;
                    }
                    else{
                        $timeout(innerFilter, 10);
                    }
                }
        }

        factory.evaluateCategories = function ()
        {
                var i = this.inspection.Checklists.length;
                this.selectCategory( this.inspection.Checklists[0].Master_hazard );
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
                if(!question.Responses){
                    question.Responses = {
                        Class: "Response",
                        Question_id: question.Key_id,
                    }
                }
                var response = question.Responses;

                question.IsDirty = true;

                var url = '../../ajaxaction.php?action=saveResponse';

                responseDto = convenienceMethods.copyObject(response);
                if(!response.Inspection_id)responseDto.Inspection_id = this.inspection.Key_id;
                if(!response.Question_id)responseDto.Question_id = question.Key_id;
                responseDto.Class = "Response";

                if(!responseDto.Answer)responseDto.Answer = '';

                console.log(responseDto)

                var deferred = $q.defer();
                return convenienceMethods.saveDataAndDefer(url, responseDto).then(
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

        factory.evaluateDeficiency = function( id ){
                var i = this.inspection.Deficiency_selections[0].length;

                while(i--){
                    if( id == this.inspection.Deficiency_selections[0][i] )return true;
                }
                return false;

        }

        factory.evaluateDeficienyShowRooms = function( id ){
                var i = this.inspection.Deficiency_selections[2].length;
                console.log(i);
                while(i--){
                    console.log(this.inspection.Deficiency_selections[2][i] + ' | ' + id)
                    if( id == this.inspection.Deficiency_selections[2][i] )return true;
                }
                return false;

        }

        factory.saveDeficiencySelection = function( deficiency, question, checklist, room )
        {
                console.log(deficiency);
                deficiency.IsDirty = true;
                question.error =  '';
                if(!checklist.InspectionRooms || !checklist.InspectionRooms.length)checklist.InspectionRooms = convenienceMethods.copyObject( factory.inspection.Rooms );
                console.log(checklist.InspectionRooms);
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
                    this.room = room;
                    while(i--){
                        if( deficiency.InspectionRooms[i].checked )roomIds.push( deficiency.InspectionRooms[i].Key_id );
                    }
                }
                console.log(roomIds);

                var defDto = {
                    Class: "DeficiencySelection",
                    RoomIds: roomIds,
                    Deficiency_id:  deficiency.Key_id,
                    Response_id: question.Responses.Key_id,
                    Inspection_id: this.inspection.Key_id,
                    Show_rooms:  deficiency.Show_rooms
                  }

                if( deficiency.selected ){

                        if(question.Responses && question.Responses.DeficiencySelections){
                            var j = question.Responses.DeficiencySelections.length;
                            while(j--){
                                var ds = question.Responses.DeficiencySelections[j];
                                if(deficiency.Key_id == ds.Deficiency_id)defDto.Key_id = ds.Key_id;
                            }
                        }

                          var url = '../../ajaxaction.php?action=saveDeficiencySelection';
                        convenienceMethods.saveDataAndDefer(url, defDto)
                            .then(
                                function(returnedDeficiency){
                                    deficiency.IsDirty = false;
                                    deficiency.selected = true;
                                    //console.log(returnedDeficiency);
                                    factory.inspection.Deficiency_selections[0].push( deficiency.Key_id );
                                    if(!question.Responses.DeficiencySelections)question.Responses.DeficiencySelections = [];
                                    question.Responses.DeficiencySelections.push( returnedDeficiency );

                                    if(factory.room){
                                        var l = deficiency.InspectionRooms.length;
                                        var m = 0;
                                        while(l--){
                                            var room = deficiency.InspectionRooms.length[l];
                                            if( roomIds.indexOf( factory.room.Key_id ) > -1 ){
                                                factory.room.checked = true;
                                                m++
                                            }else{
                                                factory.room.checked = false;
                                            }
                                            //if no rooms are left checked for this deficiency, we remove it's key id from the Inspection's array of deficiency_selection ids
                                            if(m == 0)factory.inspection.Deficiency_selections[0].splice( factory.inspection.Deficiency_selections.indexOf(deficiency.Key_id, 1 ) )

                                        }
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
                      convenienceMethods.saveDataAndDefer( url, defDto )
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
            //console.log(deficiency);
            var i = question.Responses.DeficiencySelections.length;
            while(i--){
                if( question.Responses.DeficiencySelections[i].Deficiency_id == deficiency.Key_id ){
                    var j = question.Responses.DeficiencySelections[i].Rooms.length;
                    while(j--){
                        if( question.Responses.DeficiencySelections[i].Rooms[j].Key_id == room.Key_id ){
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        factory.copyForEdit = function( question, objectToCopy )
        {
            console.log(objectToCopy);
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
            console.log(question)
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
                      convenienceMethods.saveDataAndDefer( url, $rootScope.ObservationCopy )
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
                  convenienceMethods.saveDataAndDefer( url, $rootScope.RecommendationCopy )
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
                  convenienceMethods.saveDataAndDefer( url, soDto )
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
                  convenienceMethods.saveDataAndDefer( url, srDto )
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
                    convenienceMethods.saveDataAndDefer( url, relationshipDTO )
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
                                  console.log(question.Responses);
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
            convenienceMethods.saveDataAndDefer( url, relationshipDTO )
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
          convenienceMethods.saveDataAndDefer(url, pi)
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

    $scope.cf = checklistFactory;

    if($location.search().inspection){
      $scope.inspId = $location.search().inspection;
      checklistFactory.getInspection( $scope.inspId )
          .then(
              function( inspection ){
                  console.log(inspection);
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
            //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
            deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
        }
       // checklistFactory.evaluateDeficiecnyRooms( question, checklist );

        event.stopPropagation();
        calculateClickPosition(event,deficiency,element);
        deficiency.showRoomsModal = !deficiency.showRoomsModal;
      }

    //get the position of a mouseclick, set a properity on the clicked hazard to position an absolutely positioned div
    function calculateClickPosition(event, deficiency, element){
        console.log(deficiency);
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
  console.log(checklistFactory);
  var pi = checklistFactory.inspection.PrincipalInvestigator;
  $scope.pi = pi;
  console.log($scope.pi);
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
    console.log($scope.editNote);
  }

  $scope.saveNote = function(){
    $scope.savingNote = true;
    $scope.error = null;

    checklistFactory.savePi($scope.piCopy)
      .then(
        function(returnedPi){
          console.log(returnedPi);
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
