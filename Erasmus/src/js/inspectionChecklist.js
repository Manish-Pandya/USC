var inspectionChecklist = angular.module('inspectionChecklist', ['ui.bootstrap', 'shoppinpal.mobile-menu']);

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
        angular.forEach(datum.questions, function(question){
          //initialize properties to hold user responses for each question in the view model.  
          //these properties will be used to build the posted body for our asynchronous call when a user answers a question
          question.userResponse = {};
          //The Dirty property is a boolean to indicate whether the response is in sync with the server.  True indicates out of sync
          question.userResponse.dirty = true;
          question.userResponse.answer = 'unanswered';
          question.userResponse.deficiencies = 'test value';
          question.userResponse.notes = 'test value';
          question.userResponse.recommendations = 'test value';
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
function ChecklistController($scope,  $location, $anchorScroll, testFactory) {
  $scope.users = [];
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    console.log('init called');
    testFactory.getChecklists(onGetChecklists,'http://erasmus.graysail.com/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&checklists=true');
  };
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {
    $scope.checklists = data;
  }

   $scope.imgNumber = "1";
    $scope.change = function(imgNumber, checklist) {
      console.log(imgNumber);
      $scope.imgNumber = imgNumber;
      checklist.open = !checklist.open;
      console.log(checklist);
  }

  $scope.questionAnswered = function(checklist, response, question){
    
    checklist.answeredQuestions = 0;

    for(i=0;checklist.questions.length > i;i++){
      checklist.questions[i].complete = false;

        answer = checklist.questions[i].userResponse.answer;

        //if a user answers "yes" or "n/a", a question is valid
        if(answer == 'true' || answer.toLowerCase() == 'n/a'){
          checklist.questions[i].complete = true;
          //send to server
        }else{
          //if a user answers "no", a question is not valid until the user speficies one or more deficiencies
          
          //loop through deficiencies.  if any are checked, the question is valid
          checkedCount = 0;
          for(defCount=0;checklist.questions[i].deficiencies.length > defCount;defCount++){
            if(checklist.questions[i].deficiencies[defCount].checked == true){
              checkedCount++;
            }
          }
           if(checkedCount > 0){
            console.log(i);
              checklist.questions[i].complete = true;
           }
        }
        
        if(checklist.questions[i].complete == true){
          checklist.answeredQuestions++;
        }
    }
  }

  $scope.handleNotesAndRecommendations = function(question){

    if(question.noteText != null && question.noteText != ''){
      question.notes.push({text:question.noteText, checked:true});
      question.noteText = '';
      //send question.userRepsonse to server
    }

    if(question.recommendationText != null  && question.recommendationText != ''){
      question.recommendations.push({text:question.recommendationText, checked:true});
      question.recommendationText = '';
      //send question.userRepsonse to server
    }
  }


  $scope.$watch('checklists', function(checklists){
    angular.forEach(checklists, function(checklist, idx){
      checklist.currentlyOpen = false;
      if (checklist.open) {
        checklist.currentlyOpen = true;
        checklist.hasBeenSelected = true;
      }

      if(checklist.hasBeenSelected == true){
          if(checklist.answeredQuestions < checklist.questions.length){
            checklist.countClass = 'red';
          }else{
            checklist.countClass = 'green';
          }
      }
    })   
  }, true);
  /*
   * USER SAVE METHODS
   * used for creating and updating users
   * 
   */

};

//set controller
inspectionChecklist.controller( 'ChecklistController', ChecklistController);