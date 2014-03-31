var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodModule']);

//called on page load, gets initial user data to list users
function ChecklistController($scope,  $location, $anchorScroll, convenienceMethods) {
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){

    if($location.search().inspection){

      $scope.inspId = $location.search().inspection;

     // convenienceMethods.getData('../../ajaxaction.php?action=getUserById&id=1&callback=JSON_CALLBACK',onGetUser, onFailGetUser);
        convenienceMethods.getData('../../ajaxaction.php?action=resetChecklists&id='+$scope.inspId +'&callback=JSON_CALLBACK',onGetChecklists, onFailGetChecklists);
    //  convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+$scope.inspId +'&callback=JSON_CALLBACK',onGetInsp, onFailGetInsp);
    }
    
  }

  function onGetUser(data){
    $scope.User = data;
  }
  function onFailGetUser(){
    alert("There was a problem retrieving your user information");
  }
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {
    $scope.inspection = data;
    $scope.checklists = data.Checklists;
    angular.forEach($scope.checklists, function(checklist, key){
      checklist.isNew = true;
    });
  }

  function onFailGetChecklists(){
    alert('There was a problem getting the checklist for this inspection.');
  }
  function onFailGetInsp(){

  }

  function onGetInsp(data){
    $scope.inspection = data;
  }

  $scope.imgNumber = "1";
  $scope.change = function(imgNumber, checklist) {

      $scope.imgNumber = imgNumber;
      checklist.open = !checklist.open;
  }

  $scope.questionAnswered = function(checklist, response, question){
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
          answer:         response.Answer,
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
        answer:         response.Answer,
      }
      handleResponse(responseDTO, response, question, checklist);
    }
  }

  //click handler for questions that have already been answered if we wish to set answers to null
  $scope.setUnchecked = function(checklist, response, question,checklist){
    question.IsDirty = true;
     //this question has already been answered
    if(response.previous){
      //this question's answer has not changed
      if(response.previous == reponse.Answer){
        //include a key id in the reponse dto so that we update instead of saving a new one
        var responseDTO = {
          Class:          "Response",
          Inspection_id : $scope.inspId,
          Question_id:    question.Key_id,
          Question_text:  question.Text,
          answer:         response.Answer,
          Key_id:         response.Key_id
        }
        //todo: api call to get rid of relationship between response and question
        //handleResponse(responseDTO, response, question);
        response.Answer = false;
      }
    }
  }


  function handleResponse(responseDTO, response, question, checklist){
    var url = '../../ajaxaction.php?action=saveResponse';
    convenienceMethods.updateObject( responseDTO, question, onSaveResponse, onFailSaveResponse, url, 'test', checklist, response.previous);
  }

  function onSaveResponse(response, question, checklist, previous){
    response.previous = previous;
    question.Responses = response;
    question.showChecks = true;
    question.IsDirty = false;
    $scope.countAnswers(checklist);
  }

 $scope.countAnswers = function(checklist,idx){
    checklist.AnsweredQuestions = 0;
    for(i=0;checklist.Questions.length > i;i++){
        if(checklist.hasBeenCounted == 1){evaluateRecsAndObs(question);}
        question =  checklist.Questions[i];
        question.complete = false;
        if(question.Responses){
         answer = question.Responses.Answer;
         if(answer)question.Responses.previous = true;
        //if a user answers "yes" or "n/a", a question is valid
        if(answer.toLowerCase() == 'yes' || answer.toLowerCase() == 'n/a'){
          question.complete = true;
        }else{
          //if a user answers "no", a question is not valid until the user speficies one or more deficiencies
          evalDefSelections(question);
          checkedCount = 0;
          for(defCount=0;question.Deficiencies.length > defCount;defCount++){
            if(question.Deficiencies[defCount].checked == true){
              checkedCount++;
            }
          }
         if(checkedCount > 0){
            question.complete = true;
         }
        }        
        if(question.complete == true){
          checklist.AnsweredQuestions++;
        }
      }
    }
    checklist.hasBeenCounted++; 
  }

  function evaluateRecsAndObs(question){
    
   // console.log(question);

    if(question.Responses.Recommendations.length > 0){
      angular.forEach(question.Recommendations, function(rec, key){
       // console.log(convenienceMethods.arrayContainsObject(question.Responses.Recommendations, rec,false));
        if(convenienceMethods.arrayContainsObject(question.Responses.Recommendations, rec)) {
           rec.checked = true; 
          //console.log(rec);
        }    
      });
    }

    if(question.Responses.Observations.length > 0){
      angular.forEach(question.Observations, function(obs, key){
       // console.log(convenienceMethods.arrayContainsObject(question.Responses.Recommendations, rec,false));
        if(convenienceMethods.arrayContainsObject(question.Responses.Observations, obs)) {
           obs.checked = true; 
           //console.log(obs);
        }    
      });
    } 
  }


  function evalDefSelections(question){
    angular.forEach(question.Deficiencies, function(def, key){
     // console.log(def);
      if(convenienceMethods.arrayContainsObject(question.Responses.DeficiencySelections,def,["Deficiency_id","Key_id"])){
        def.checked=true;
        //console.log(def);
      }
    });
  }

  function onFailSaveResponse(){
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
      }

      if(obj.Class == "Recommendation"){
        var url = '../../ajaxaction.php?action=saveRecommendationRelation';
      }

      convenienceMethods.updateObject( relationshipDTO, obj, onSaveRelationship, onFailSaveRelationship, url,'', relationshipDTO);
  }

  $scope.createNewNoteOrRec = function(question, response, persist, type){    
    if(question.noteText != null && type == 'observation'){
      
      obsDto = {
        Text:  question.noteText
      }
      if(!persist){
        obsDto.Class = "SupplementalObservation";
        obsDto.Response_id = Question.ResponsesKey_id;
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
         obsDto.Response_id = Question.ResponsesKey_id;
         var url = '../../ajaxaction.php?action=saveSupplementalRecommendation';
      }else{
        obsDto.Class = "Recommendation";
        obsDto.Question_id = question.Key_id;
        var url = '../../ajaxaction.php?action=saveRecommendation';
      }
      convenienceMethods.updateObject( obsDto, question, onSaveRec, onFailSaveRec, url);
    }
  }

  function onSaveRelationship(serverResp, obj, relationshipDTO){
    obj.IsDirty = false;
    obj.checked = relationshipDTO.add;
  }

  function onFailSaveRelationship(relationshipDTO){
    alert('There was a problem saving the note or recommendation.');
  }

  function onSaveObs(obsDto,question){
    obsDto.checked = true;
    question.Observations.push(obsDto);
    //make second api call to create relationship between new observation or recommendation and response, but only if the obs or rec is not supplemental
    if(obsDto.Class.indexOf("Supplemental") > -1)$scope.handleNotesAndRecommendations(question,obsDto);
  }

  function onFailSaveObs(){
    alert("There was a problem saving the new note");
  }

  function onSaveRec(obsDto,question){
    obsDto.checked = true;
    question.Recommendations.push(obsDto);
    $scope.handleNotesAndRecommendations(question,obsDto);
  }

  function onFailSaveRec(){
    alert("There was a problem saving the new recommendation");
  }

  $scope.deficiencySelected = function(response, deficiency, rooms){
    console.log(response);
    if(!rooms){
      var rooms = $scope.inspection.Rooms;
    }

    var RoomIds = [];

    angular.forEach(rooms, function(room, key){
      RoomIds.push(room.Key_id);
    });




    defDto = {
      Class: "DeficiencySelection",
      RoomIds: RoomIds,
      Deficiency_id:  deficiency.Key_id,
      Response_id: response.Key_id,
      Key_id:      null
    }

    var url = '../../ajaxaction.php?action=saveDeficiencySelection';
    convenienceMethods.updateObject( defDto, response, onSaveDefSelect, onFailSaveDefSelect, url, null, deficiency);

  }

  function onSaveDefSelect(def){
    console.log(def);
  }

  function onFailSaveDefSelect(){}

  $scope.$watch('checklists', function(checklists){
    angular.forEach(checklists, function(checklist, idx){
      if(!checklist.hasBeenCounted)checklist.hasBeenCounted = 0;

      if(checklist.hasBeenCounted < 2)$scope.countAnswers(checklist,idx);
      checklist.currentlyOpen = false;
      if (checklist.open) {
        checklist.currentlyOpen = true;
        checklist.hasBeenSelected = true;
      }

      if(checklist.hasBeenSelected == true){
        if(checklist.AnsweredQuestions < checklist.Questions.length){
          checklist.countClass = 'red';
        }else{
          checklist.countClass = 'green';
        }
      }
    })   
  }, true);
};

//set controller
inspectionChecklist.controller( 'ChecklistController', ChecklistController);