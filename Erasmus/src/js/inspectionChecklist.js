var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodModule']);

inspectionChecklist.factory('testFactory', function($http){
  
  //initialize a factory object
  var tempFactory = {};
  
  //simple 'getter' to grab data from service layer
  tempFactory.getChecklists = function(onSuccess, url){
  
  //user jsonp method of the angularjs $http object to request data from service layer
  $http.jsonp(url)
    .success( function(data) {
      angular.forEach(data, function(datum) {
        datum.answeredQuestions = 0;
        datum.hasBeenSelected = false;
        angular.forEach(datum.Questions, function(question){
          //initialize properties to hold user responses for each question in the view model.  
          //these properties will be used to build the posted body for our asynchronous call when a user answers a question
          question.Response = {};
          //The Dirty property is a boolean to indicate whether the response is in sync with the server.  True indicates out of sync
          question.Response.dirty = true;
          question.Response.answer = 'unanswered';
          question.Response.deficiencies = 'test value';
          question.Response.notes = 'test value';
          question.Response.recommendations = 'test value';
        });
      });
      //onSuccess is the method we have passed from the controller.  Binds data from service layer to angularjs $scope object
      onSuccess(data);
    })
    .error(function(data, status, headers, config){
      alert('error');
      console.log(headers());
      console.log(status);
      console.log(config);
    });
        
  };
  
  tempFactory.saveUser = function(onSuccess, url){
    alert('saving user');
  }
  
  return tempFactory;
});

//called on page load, gets initial user data to list users
function ChecklistController($scope,  $location, $anchorScroll, testFactory, convenienceMethods) {
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){

    if($location.search().inspection){

      $scope.inspId = $location.search().inspection;

      convenienceMethods.getData('../../ajaxaction.php?action=getUserById&id=1&callback=JSON_CALLBACK',onGetUser, onFailGetUser);

      console.log('init called');
      //data-ng-controller="hazardAssessmentController"
      convenienceMethods.getData('../../ajaxaction.php?action=getChecklistsForInspection&id='+$scope.inspId +'&callback=JSON_CALLBACK',onGetChecklists, onFailGetChecklists);
      //testFactory.getChecklists(onGetChecklists,'../../views/api/hazardAssApi.php?callback=JSON_CALLBACK&checklists=true');
    }
    
  };

  function onGetUser(data){
    $scope.User = data;
  }
  function onFailGetUser(){
    alert("There was a problem retrieving your user information");
  }
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {

    angular.forEach(data, function(datum) {
      datum.AnsweredQuestions = 0;
      datum.hasBeenSelected = false;
    });

    $scope.checklists = data;
    console.log($scope.checklists);
  }

  function onFailGetChecklists(){
    alert('There was a problem getting the checklist for this inspection.');
  }

  $scope.imgNumber = "1";
  $scope.change = function(imgNumber, checklist) {
      console.log(imgNumber);
      $scope.imgNumber = imgNumber;
      checklist.open = !checklist.open;
      console.log(checklist);
  }

  $scope.questionAnswered = function(checklist, response, question){

    //this question has already been answered
    if(response.previous){
      //this question's answer has changed
      if(response.previous != reponse.Answer){
        //include a key id in the reponse dto so that we update instead of saving a new one
        var responseDTO = {
          Class:          "Response",
          Inspection_id : $scope.inspId,
          Question_id:    question.Key_id,
          Question_text:  question.Text,
          answer:         response.Answer,
          Key_id:         response.Key_id
        }
        handleResponse(responseDTO, response, question);
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
    question.Response = response;
    question.showChecks = true;

    countAnswers(checklist);
  }

  function countAnswers(checklist){
    checklist.AnsweredQuestions = 0;
    for(i=0;checklist.Questions.length > i;i++){
        checklist.Questions[i].complete = false;
         console.log(checklist.Questions[i]);
         if(checklist.Questions[i].Response){
         answer = checklist.Questions[i].Response.Answer;
         console.log(answer);
        //if a user answers "yes" or "n/a", a question is valid
        if(answer.toLowerCase() == 'yes' || answer.toLowerCase() == 'n/a'){
          console.log(answer);
          checklist.Questions[i].complete = true;
        }else{
          //if a user answers "no", a question is not valid until the user speficies one or more deficiencies
          checkedCount = 0;
          for(defCount=0;checklist.Questions[i].Deficiencies.length > defCount;defCount++){
            if(checklist.Questions[i].Deficiencies[defCount].checked == true){
              checkedCount++;
            }
          }
         if(checkedCount > 0){
            console.log(i);
            checklist.Questions[i].complete = true;
         }
        }        
        if(checklist.Questions[i].complete == true){
         // console.log(checklist.Questions[i].Text);
          checklist.AnsweredQuestions++;
          console.log(checklist);
        }
      }
    }
  }

  function onFailSaveResponse(){
    alert("There was an issue when the system tried to save the response.");
  }

  $scope.handleNotesAndRecommendations = function(question, obj){
    console.log(obj);
    console.log(question);

      var relationshipDTO = {
        Class:          "RelationshipDto",
        Master_id :     question.Response.Key_id,
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
        obsDto.Response_id = question.Response.Key_id;
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
         obsDto.Response_id = question.Response.Key_id;
         console.log(obsDto);
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
    console.log(obj,relationshipDTO);
    obj.checked = relationshipDTO.add;
  }

  function onFailSaveRelationship(relationshipDTO){
    alert('There was a problem saving the note or recommendation.');
  }

  function onSaveObs(obsDto,question){
    console.log(obsDto);
    console.log(question);
    obsDto.checked = true;
    question.Observations.push(obsDto);
    //make second api call to create relationship between new observation or recommendation and response, but only if the obs or rec is not supplemental
    if(obsDto.Class.indexOf("Supplemental") > -1)$scope.handleNotesAndRecommendations(question,obsDto);
  }

  function onFailSaveObs(){
    alert("There was a problem saving the new note");
  }

  function onSaveRec(obsDto,question){
    console.log(obsDto);
    console.log(question);
    obsDto.checked = true;
    question.Recommendations.push(obsDto);
    $scope.handleNotesAndRecommendations(question,obsDto);
  }

  function onFailSaveRec(){
    alert("There was a problem saving the new recommendation");
  }

  $scope.$watch('checklists', function(checklists){
    angular.forEach(checklists, function(checklist, idx){
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