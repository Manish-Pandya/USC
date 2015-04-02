var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodModule','once']);

inspectionChecklist.run(function($rootScope, $templateCache) {
   $rootScope.$on('$viewContentLoaded', function() {
      $templateCache.removeAll();
   });
})

.factory('checklistFactory', function(convenienceMethods,$q){

    var factory = {};

    factory.organizeChecklists = function(checklists){
        //object with array properties to contain the checklists
        checklistHolder = {};
        checklistHolder.biologicalHazards = {uid:'biologicalHazards', Name: "BIOLOGICAL SAFETY",img:'biohazard-largeicon.png', checklists:[], altImg:"biohazard-white-con.png"};
        checklistHolder.chemicalHazards = {uid:'chemicalHazards', Name: "CHEMICAL SAFETY", img:'chemical-safety-large-icon.png', checklists:[]};
        checklistHolder.radiationHazards = {uid:'radiationHazards', Name: "RADIATION SAFETY", img:'radiation-large-icon.png', checklists:[]};
        checklistHolder.generalHazards = {uid:'generalHazards', Name: "GENERAL SAFETY", img:'gen-hazard-large-icon.png' ,checklists:[]};

        //group the checklists by parent hazard
        //get the questions for each checklist and store them in a property that the view can access easily
        for(i=0;i<checklists.length;i++){
          var checklist = checklists[i];

          if(checklist.Master_hazard.toLowerCase().indexOf('biological') > -1){
            if(!checklistHolder.biologicalHazards.Questions)checklistHolder.biologicalHazards.Questions = [];
            checklistHolder.biologicalHazards.checklists.push(checklist);
          }
          else if(checklist.Master_hazard.toLowerCase().indexOf('chemical') > -1){
            if(!checklistHolder.chemicalHazards.Questions)checklistHolder.chemicalHazards.Questions = [];
            checklistHolder.chemicalHazards.checklists.push(checklist);
          }
          else if(checklist.Master_hazard.toLowerCase().indexOf('radiation') > -1){
            if(!checklistHolder.radiationHazards.Questions)checklistHolder.radiationHazards.Questions = [];
            checklistHolder.radiationHazards.checklists.push(checklist);
          }
          else if(checklist.Master_hazard.toLowerCase().indexOf('general') > -1){
            if(!checklistHolder.generalHazards.Questions)checklistHolder.generalHazards.Questions = [];
            checklistHolder.generalHazards.checklists.push(checklist);
          }
        }
        return checklistHolder;
      };

      factory.getQuestionsByChecklist = function( checklist ){
        return checklist.Questions;
      }

      factory.evaluateDeficiecnyRooms = function( question, checklist )
      {
 
          var i = question.Deficiencies.length;

          while(i--){
              var def = question.Deficiencies[i];
              if(!def.InspectionRooms)def.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms )
              //does this deficiency match a selection?
              if(question.Responses.DeficiencySelections){
                var j = question.Responses.DeficiencySelections.length;
                while(j--){
                  var defSelect = question.Responses.DeficiencySelections[j];
                  if(defSelect.Deficiency_id == def.Key_id){
                      var k = def.InspectionRooms.length;
                      while(k--){
                         var room = def.InspectionRooms[k];
                         var l = defSelect.Rooms.length;
                         while(l--){
                           if(room.Key_id == defSelect.Rooms[l].Key_id){
                              room.checked = true;
                           }
                         }
                      }
                  }
                }
              }
          }
      }

    return factory;

});

//called on page load, gets initial user data to list users
function ChecklistController($scope,  $location, $anchorScroll, convenienceMethods, $window, checklistFactory) {

  //grab a copy of convenienceMethods copyObject method so we can call it from the view
  $scope.copyObject = convenienceMethods.copyObject;
  $scope.evaluateDeficiecnyRooms = convenienceMethods.evaluateDeficiecnyRooms

  init();
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  function init(){
    if($location.search().inspection){
      $scope.inspId = $location.search().inspection;
      convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=resetChecklists&id='+$scope.inspId+'&callback=JSON_CALLBACK', onFailGetInsp)
      .then(function(data){
        console.log(data.data);
         $scope.inspection = data.data;
         $scope.checklists = checklistFactory.organizeChecklists(data.data.Checklists);
         $scope.selectChecklistCategory("biologicalHazards");
         angular.forEach($scope.checklists, function(checklist, key) {
             onGetChecklists(checklist);
         });
      })
    }else{
      $scope.error="No inspection has been specifed.";
      $scope.checklists = true;
    }

  }

  function onGetUser(data){
    $scope.User = data;
  }
  function onFailGetUser(){
    alert("There was a problem retrieving your user information");
  }
  
  //grab set user list data into the $scope object
  var onGetChecklists = function(checklists){
    var len = checklists.checklists.length;
    //We loop through each checklist, and each checklist's questions, to see if questions have already been answered
    for(i=0;i<len;i++){
      var checklist = checklists.checklists[i];
      checklist.isNew = true;
      var questions = checklist.Questions;
      var qLen = questions.length;
      for(x=0;x<qLen;x++){
        var question = questions[x];
        if(question.Responses && question.Responses.Answer){
          //set a previous response object for each question that has been answered so that the question can be "unanswered" by a user
          question.Responses.previous = angular.copy(question.Responses.Answer); 
          question = evaluateQuestionComplete(question);
          evaluateRecommendationsAndObservations(question);
        }
      }
      countAnswers(checklist);
    }
    return checklists;
  }

  //Evaluate whether a question has been completed
  function evaluateQuestionComplete(question){
    question.isComplete = false;
    //Check whether the question has been answered
    if(question.Responses.Answer){
      //if a question has an answer of 'Yes' or 'N/A', we can consider it completed on the checklist
      if(question.Responses.Answer.toLowerCase() == 'yes' || question.Responses.Answer.toLowerCase() ==  'n/a'){
        question.isComplete = true;
      //If a question has been answered 'No', the user must select one or more deficiences before the question is complete
      }else if(question.Deficiencies && question.Responses.Answer.toLowerCase() == 'no' && question.Responses.DeficiencySelections && question.Responses.DeficiencySelections.length ){
        question.isComplete = false;
        checkQuestionsDeficiencies(question);
      }else{
        question.isComplete = false;
      }
    } 
    return question;
  }

  function checkQuestionsDeficiencies(question){
    //see if any of the deficiencies for this question are in the Inspection's list of Deficiency Selections.
    //Inspection.Deficiency_selections contains a list of the key_ids of all deficiencies that have been selected
    var dLen = question.Deficiencies.length
    for(z=0;z<dLen;z++){
      var defID = question.Deficiencies[z].Key_id;
      //Does this deficiency's key_id occur in the list of selected deficiencies?
      if($scope.inspection.Deficiency_selections[0].indexOf(defID)>-1){
        //at least one Deficiency has been selected for this question, so the question is complete
        question.isComplete = true;
        question.Deficiencies[z].selected = true;
        //was this deficiency Corrected durring the inspection?
        if(($scope.inspection.Deficiency_selections[1].indexOf(defID)>-1))question.Deficiencies[z].correctedDuringInspection = true;
      }
    }
    return question;
  }

  function onFailGetChecklists(){
    alert('There was a problem getting the checklist for this inspection.');
  }
  function onFailGetInsp(){
    $scope.Inspection = '';
    $scope.checklists = true;
    $scope.error = 'There was an error getting the checklists for this inspection.  Check your internet connection and try again.'; 
  }

  $scope.imgNumber = "1";
  $scope.change = function(imgNumber, checklist) {
      $scope.imgNumber = imgNumber;
      checklist.currentlyOpen = !checklist.currentlyOpen;
  }

  $scope.questionAnswered = function(checklist, response, question){
    console.log(response);
    question.IsDirty = true;
    //this question has already been answered
    if(response.previous){
      //this question's answer has changed
      if(response.previous != response.Answer){
        //include a key id in the reponse dto so that we update instead of saving a new one
        var responseDTO = {
          Class:          "Response",
          Inspection_id : $scope.inspId,
          Question_id:    question.Key_id,
          Question_text:  question.Text,
          Answer:         response.Answer,
          Key_id:         response.Key_id
        }
        handleResponse(responseDTO, response, question, checklist);
      }
      //if the question has an answer that is the same as it's previos answer, we let the click handler setUnchecked handle it instead
    }else{
      //this question has not been answered
     //Don't include a key id; we are saving a new response for this question
      var responseDTO = {
        Class:          "Response",
        Inspection_id : $scope.inspId,
        Question_id:    question.Key_id,
        Question_text:  question.Text,
        Answer:         response.Answer,
      }
      //if the question has an answer that is the same as it's previos answer, we let the click handler setUnchecked handle it instead
      if(response.Key_id)responseDTO.Key_id = response.Key_id;
      handleResponse(responseDTO, response, question, checklist);
    }
  }

  //click handler for questions that have already been answered if we wish to set answers to null
  $scope.setUnchecked = function(checklist, response, question,checklist){
    question.IsDirty = true;
     //this question has already been answered
    if(question.Responses && question.Responses.previous){
      //this question's answer has not changed
      if(question.Responses.previous == response){
        //include a key id in the reponse dto so that we update instead of saving a new one
        response.Answer = false;
        var responseDTO = {
          Class:          "Response",
          Inspection_id : $scope.inspId,
          Question_id:    question.Key_id,
          Question_text:  question.Text,
          Answer:         false,
          Key_id:         response.Key_id
        }
        handleResponse(responseDTO, response, question, checklist);
      }
    }
  }


  function handleResponse(responseDTO, response, question, checklist){
    console.log(responseDTO);
    if(responseDTO.Answer){
      var url = '../../ajaxaction.php?action=saveResponse';
      convenienceMethods.updateObject( responseDTO, question, onSaveResponse, onFailSaveResponse, url, 'test', checklist, response.previous);
    }else{
      var url = '../../ajaxaction.php?action=removeResponse&id='+responseDTO.Key_id+'&callback=JSON_CALLBACK';
      convenienceMethods.deleteObject( onSetUncehcked, onFailSaveResponse, url, question );
    }

  }

  function onSetUncehcked(data,question){
    question.Responses.Answer = false;
    question.IsDirty = false;
    question.Responses.previous = null;
  }

  function onSaveResponse(response, question, checklist, previous){
    if(!question.Responses.previous)question.Responses.previous;
    question.Responses.previous = previous;
    question.Responses = response;
    question.Responses.DeficiencySelections = response.DeficiencySelections;

    if(question.Responses.Answer != 'no'){
      var i = question.Deficiencies.length;
      var j = question.Responses.DeficiencySelections.length;
      while(i--){
          var def = question.Deficiencies[i];
          def.selected = false;
          while(j--){
              if(def.Key_id == question.Responses.Deficiencies[j].Key_id)def.selected = true; 
          }

      }
    }

    question.showChecks = true;
    question.IsDirty = false;
    evaluateQuestionComplete(question);
    countAnswers(checklist);
  }

 //Counts the number of questions that have been completely answered in a checklist
 countAnswers = function(checklist){
  checklist.AnsweredQuestions = 0;
  var cLen = checklist.Questions.length;
  for(j=0;j<cLen;j++){
    question = checklist.Questions[j];
    if(question.isComplete){
      checklist.AnsweredQuestions++;
    }
  }
  if(checklist.AnsweredQuestions === cLen)checklist.complete = true;
 }

  function evaluateRecsAndObs(question){
    
    if(question.Responses && question.Responses.Recommendations.length > 0){
      angular.forEach(question.Recommendations, function(rec, key){
       // //console.log(convenienceMethods.arrayContainsObject(question.Responses.Recommendations, rec,false));
        if(convenienceMethods.arrayContainsObject(question.Responses.Recommendations, rec)) {
           rec.checked = true; 
        }    
      });
    }

    if(question.Responses && question.Responses.Observations.length > 0){
      angular.forEach(question.Observations, function(obs, key){
       // //console.log(convenienceMethods.arrayContainsObject(question.Responses.Recommendations, rec,false));
        if(convenienceMethods.arrayContainsObject(question.Responses.Observations, obs)) {
           obs.checked = true; 
        }    
      });
    } 
  }

  function onFailSaveResponse(){
    alert('The system couldn\'t save the response.');
  }

  $scope.handleNotesAndRecommendations = function(question, obj){
      obj.IsDirty = true;
      var relationshipDTO = {
        Class:          "RelationshipDto",
        Master_id :     question.Responses.Key_id,
        Relation_id:    obj.Key_id,
        add:            obj.checked,
      }

      obj.checked = !obj.checked;

      if(obj.Class == "Observation"){
         var url = '../../ajaxaction.php?action=saveObservationRelation';
      }else if(obj.Class == "Recommendation"){
        var url = '../../ajaxaction.php?action=saveRecommendationRelation';
      }

      convenienceMethods.updateObject( relationshipDTO, obj, onSaveRelationship, onFailSaveRelationship, url,'', relationshipDTO);
  }



  $scope.createNewNoteOrRec = function(question, response, persist, type){  
    question.savingNew = true;
    if(question.noteText != null && type == 'observation'){

      obsDto = {
        Text:  question.noteText,
        Is_active: true
      }
      if(!persist){
        obsDto.Class = "SupplementalObservation";
        obsDto.Response_id = question.Responses.Key_id;
        var url = '../../ajaxaction.php?action=saveSupplementalObservation';
      }else{
        obsDto.Class = "Observation";
        obsDto.Question_id = question.Key_id;
        var url = '../../ajaxaction.php?action=saveObservation';
      }
     
      convenienceMethods.updateObject( obsDto, question, onSaveObs, onFailSaveObs, url);
    }

     if(question.recommendationText != null && type == 'recommendation'){
      
      obsDto = {
        Text:  question.recommendationText
      }
      if(!persist){
         obsDto.Class = "SupplementalRecommendation";
         obsDto.Response_id = question.Responses.Key_id;
         var url = '../../ajaxaction.php?action=saveSupplementalRecommendation';
      }else{
        obsDto.Class = "Recommendation";
        obsDto.Question_id = question.Key_id;
        var url = '../../ajaxaction.php?action=saveRecommendation';
      }
      console.log(url);
      convenienceMethods.updateObject( obsDto, question, onSaveRec, onFailSaveRec, url);
    }
  }

  function onSaveRelationship(serverResp, obj, relationshipDTO){
    console.log(serverResp);
    console.log(relationshipDTO);
    obj.IsDirty = false;
    obj.checked = relationshipDTO.add;
  }

  function onFailSaveRelationship(relationshipDTO){
    alert('There was a problem saving the note or recommendation.');
  }

  function onSaveObs(obsDto,question){
    question.savingNew = false;
    obsDto.checked = true;
    obsDto.isNew = true;
    if(obsDto.Class.indexOf("Supplemental") == -1){
      question.Observations.push(obsDto);
      $scope.handleNotesAndRecommendations(question,obsDto);
    }else{
      question.Responses.SupplementalObservations.push(obsDto);
    }
  }

  function onFailSaveObs(){
    alert("There was a problem saving the new note");
  }

  function onSaveRec(obsDto,question){
    question.savingNew = true;
    obsDto.checked = true;
    obsDto.isNew = true;
    if(obsDto.Class.indexOf("Supplemental") == -1){
      question.Recommendations.push(obsDto);
      $scope.handleNotesAndRecommendations(question,obsDto);
    }else{
      question.Responses.SupplementalRecommendations.push(obsDto);
    }

  }

  function onFailSaveRec(){
    alert("There was a problem saving the new recommendation");
  }

  $scope.selectRoom = function(response, deficiency, room, checklist){
    room.IsDirty = true;
    $scope.deficiencySelected(response, deficiency, room, checklist, true);
  }


  $scope.deficiencySelected = function(question, deficiency, rooms, checklist, roomSelected){
    console.log(checklist);

    response = question.Responses;
    response.IsDirty = true;

    if(!roomSelected){
        var adding = deficiency.selected
    }else{
        var adding = rooms.checked;
    }

    //if this deficiency doesn't have a rooms collection, make one
    if(!deficiency.InspectionRooms){
        var rooms = convenienceMethods.copyObject(checklist.InspectionRooms);
        deficiency.InspectionRooms = convenienceMethods.copyObject(checklist.InspectionRooms);
    
    }
    var RoomIds = [];
    var atLeastOneChecked = true;

    //build out an array of Room key_ids for the server request
    if( !roomSelected || typeof roomSelected == 'undefined' ){
      if(!rooms){
        if(!deficiency.InspectionRooms)deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
        var rooms = deficiency.InspectionRooms;
      }
      for(i=0;i<rooms.length;i++){
        console.log('heere');
        rooms[i].checked = true;
        RoomIds.push(rooms[i].Key_id);
      }
    }else{
        RoomIds.push(rooms.Key_id);
    }

    console.log( RoomIds );
    //the deficieny or room has been switched from an uncheked to a checked state
    if( adding ){


      defDto = {
        Class: "DeficiencySelection",
        RoomIds: RoomIds,
        Deficiency_id:  deficiency.Key_id,
        Response_id: response.Key_id,
        Inspection_id: $scope.inspection.Key_id
      }

      //set checked property to false.  we set it to true only on success, in the callback
      deficiency.checked = false;
      var url = '../../ajaxaction.php?action=saveDeficiencySelection';
      //convenienceMethods.updateObject( defDto, question, onSaveDefSelect, onFailSaveDefSelect, url, null, checklist, deficiency);

      convenienceMethods.saveDataAndDefer( url, defDto ).then(
          function(returnedDeficiencySelection){
             console.log(returnedDeficiencySelection);
              //push the def selections deficiency_id into the inspections array of deficiency Key_ids
              //$scope.inspection.Deficiency_selections[0].push(deficiency.Key_id)
              var atLeastOneChecked = false;
              if(!deficiency.InspectionRooms)deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
             
              var i = deficiency.InspectionRooms.length

              while(i--){
                var room = deficiency.InspectionRooms[i];
                room.IsDirty = false;
                if(room.checked)atLeastOneChecked = true;
              }

              deficiency.IsDirty = false;
              deficiency.selected = true;
              question.Responses.DeficiencySelections.push(returnedDeficiencySelection);
              evaluateQuestionComplete(question);
              countAnswers(checklist);
          },
          function(promise){
            deferred.reject(promise);
          }
      );



    }else{
    console.log( RoomIds );

    defDto = {
      Class: "DeficiencySelection",
      RoomIds: RoomIds,
      Deficiency_id:  deficiency.Key_id,
      Response_id: response.Key_id,
      Inspection_id: $scope.inspection.Key_id
    }

      var i = question.Responses.DeficiencySelections.length;
      console.log(defDto.Deficiency_id);
      console.log(question.Responses.DeficiencySelections);
      //get the key_id for our DeficiencySelection
      while(i--){
        if( question.Responses.DeficiencySelections[i].Deficiency_id == defDto.Deficiency_id ){
          defDto.Key_id = question.Responses.DeficiencySelections[i].Key_id;
          var defSelectIdx = i;
        } 
      }

      var url = '../../ajaxaction.php?action=removeDeficiencySelection';
      convenienceMethods.saveDataAndDefer( url, defDto ).then(
          function(promise){
             console.log(checklist);
              //push the def selections deficiency_id into the inspections array of deficiency Key_ids
              //$scope.inspection.Deficiency_selections[0].push(deficiency.Key_id)
              var atLeastOneChecked = false;             
              var i = deficiency.InspectionRooms.length
              while(i--){
                var room = deficiency.InspectionRooms[i];
                console.log(room);
                room.IsDirty = false;
                //are any rooms checked?
                if(room.checked){
                  console.log('SELECTED?  '+roomSelected);
                  //did we get here by checking a room or a deficiency?
                  if(!roomSelected || typeof roomSelected == 'undefined'){
                        console.log('here');
                        //we got here by checking a deficiency, so all rooms should be unchecked
                        room.checked = false;
                  }else{
                      if(room.checked)atLeastOneChecked = true;
                  }
                }
              }

              question.Responses.DeficiencySelections.splice( defSelectIdx, 1 );
              deficiency.IsDirty = false;
              if(atLeastOneChecked){
                deficiency.selected = true;
              }else{
                console.log('should be falsey')
                deficiency.selected = false;
              }
              evaluateQuestionComplete(question);
              countAnswers(checklist);
          },
          function(promise){
            deferred.reject(promise);
          }
      );
    }
  }

  function onRemoveDefSelect(bool, deficiency, question, checklist){
    //get the index of the deficiency selection for the question
    var idx = convenienceMethods.arrayContainsObject(question.Responses.DeficiencySelections, deficiency, null, true);
    //if we find the deficiency selection, remove it
    if(idx)question.Responses.DeficiencySelections.splice(idx, 1);
  
    //also remove the key id of the deficiency selection from the Inspection's array of deficiency selections
    $scope.inspection.Deficiency_selections[0].splice($scope.inspection.Deficiency_selections[0].indexOf(deficiency.Deficiency_id,1));

    //determine if the question is completely answered
    evaluateQuestionComplete(question);
  
    //count the checklists answers
    countAnswers(checklist);

    //update the view
    deficiency.selected = false;
    response.IsDirty = false;
  }

  function onFailRemoveDefSelect(deficiency, response){
    deficiency.checked = true;
    response.IsDirty = false;
  }

  $scope.handleCorrectedDurringInspection = function(def){
    def.IsDirty = true;
    var def_id = def.Key_id;
    if(def.correctedDuringInspection){
      //we set corrected during inspection
      var url = '../../ajaxaction.php?action=addCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+$scope.inspection.Key_id+'&callback=JSON_CALLBACK';
      convenienceMethods.deleteObject( onAddCorrectedDurringInspection, onFailHandleCorrectedDurringInspection, url, def );
    }else{
      //we unst corrected during inspection
      var url = '../../ajaxaction.php?action=removeCorrectedInInspection&deficiencyId='+def_id+'&inspectionId='+$scope.inspection.Key_id+'&callback=JSON_CALLBACK';
      convenienceMethods.deleteObject( onRemoveCorrectedDurringInspection, onFailHandleCorrectedDurringInspection, url, def );
    }
    //reverse the the boolean so that we can wait until the callback to set it.  this keeps the view model in sync with the server model
    def.correctedDuringInspection = !def.correctedDuringInspection;
  }

  function onAddCorrectedDurringInspection(bool,def){
    def.correctedDuringInspection = true;
    def.IsDirty = false;
  }

  function onRemoveCorrectedDurringInspection(bool,def){
    def.correctedDuringInspection = false;
    def.IsDirty = false;
  }

  function onFailHandleCorrectedDurringInspection(){
    alert("There was an error when the system tried to update the Deficiency Selection.");
  }

  $scope.showRooms = function( event, deficiency, element, checklist, question ){
    if(!deficiency.InspectionRooms){
        //we haven't brought up this deficiency's rooms yet, so we should create a collection of inspection rooms
        deficiency.InspectionRooms = convenienceMethods.copyObject( checklist.InspectionRooms );
    }
    checklistFactory.evaluateDeficiecnyRooms( question, checklist );

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

  function onSaveDefSelect(returnedDeficiencySelection, question, checklist, deficiency){
   
  }

  function onFailSaveDefSelect(){

  }

  //
  var unwatch = $scope.$watch('checklists', function(loadedChecklists, promisedChecklists, scope) {
    if(loadedChecklists && loadedChecklists.length){
      onGetChecklists($scope.selectedChecklists);
      unwatch();
    }
  });

  //set the recommendations and observations to their correct view state based on the server state for a question
  function evaluateRecommendationsAndObservations(question){
    //do we have responses for this question
    if(question.Responses){
      //we have reponses.  do we have observations?
      if(question.Responses.Observations && question.Responses.Observations.length){
        question.Responses.Observations = setViewStateForObservationsOrRecommendations(question, question.Responses.Observations, "Observations");
      }

      //we have reponses.  do we have recommednations?
      if(question.Responses.Recommendations && question.Responses.Recommendations.length){
        question.Responses.Recommendations = setViewStateForObservationsOrRecommendations(question, question.Responses.Recommendations, "Recommendations");
      }

    }
  }

  //set the view state properly on page load for observations or recommendations.  Used by evaluateRecommendationsAndObservations()
  function setViewStateForObservationsOrRecommendations(question, array, type){
    arrayLen = array.length;
    for(k=0;k<arrayLen;k++){
      //does the array of either Observations or recommendations, specified by the type passed, contain this observation or recommendation
      //if so, get its index
      console.log( array[k] );
      var idx = convenienceMethods.arrayContainsObject(question[type], array[k], null, true);
      console.log(idx);
      //if we found an index, set the checked state to true for the checkbox in the view.
      if(idx || idx > -1){
        console.log('index matched at:' + idx);
        if(question[type][idx])question[type][idx].checked = true;
      }
    }
    return array;
  }

  $scope.editItem = function(question, item){
    //is this an observation or recommendation?
    if(item.Class.indexOf("Rec") > -1){
      //disable all other edits
      //A supplemental one, specific to this inspection, or a new option added durring this inspection but available for all future inspection?
      if(item.Class.indexOf("Sup") > -1){
        angular.forEach(question.Responses.SupplementalRecommendations, function(rec, key){
          rec.edit = false;
        });
      }else{
        angular.forEach(question.Recommendations, function(rec, key){
          rec.edit = false;
        });
      }
      //this is a recommendation, so we make a recommendation copy for editing
      $scope.recommendationCopy = angular.copy(item);
    }else{
      //A supplemental one, specific to this inspection, or a new option added durring this inspection but available for all future inspection?
      //disable all other edits
      if(item.Class.indexOf("Sup") > -1){

        angular.forEach(question.Responses.SupplementalObservations, function(rec, key){
          rec.edit = false;
        });
      }else{
        angular.forEach(question.Observations, function(rec, key){
          rec.edit = false;
        });
      }

      //this is an observation so we make an observation copy for editing
      $scope.noteCopy = angular.copy(item);
    }

    //set the view states so that we display form elements for editing
    item.edit = true;
    question.edit = true;
    question.editedItem = item;
  }

  $scope.saveEdit = function(question, copy, item){
    console.log(question);
    item.IsDirty = true;
    var url = '../../ajaxaction.php?action=save'+item.Class;
    convenienceMethods.updateObject( copy, item, onSaveEdit, onFailSaveEdit, url, item, question);
  }

  function onSaveEdit(returned, old, question){
    question.edit=false;
    old.Text = returned.Text;
    old.Is_active = returned.Is_active;
    old.edit = false;
    old.IsDirty = false;

  }

  function onFailSaveEdit(item){
    item.IsDirty = false;
    if(item.Class.toLowerCase().indexOf('sup')>-1)item.Is_active = !item.Is_active
    alert('The edit could not be saved.  Please check your internet connection and try again.')
  }

  $scope.cancelEdit = function(item){
    item.edit = false;
    $scope.recommendationCopy = false;
    $scope.noteCopy = false;
  }

  $scope.setNoteOrObsActiveOrInactive = function(question, item){
    item.IsDirty = true;
    copy = angular.copy(item);
    $scope.saveEdit(question, copy, item)
  }

  $scope.selectChecklistCategory = function(category){
      $scope.selectedChecklists = $scope.checklists[category];
  }

};

//set controller
inspectionChecklist.controller( 'ChecklistController', ChecklistController);
