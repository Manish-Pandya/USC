var postInspection = angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule']);
postInspection.filter('joinBy', function () {
  return function (input,delimiter) {
    return (input || []).join(delimiter || ',');
  };
});
postInspection.config(function($routeProvider){

  $routeProvider
  .when('/confirmation', 
    {
      templateUrl: '../post-inspection-templates/inspectionConfirmation.html', 
      controller: inspectionConfirmationController
    }
  )
  .when('/review', 
    {
      templateUrl: '../post-inspection-templates/standardView.html', 
      controller: inspectionReviewController 
    }
  )
  .when('/details', 
    {
      templateUrl: '../post-inspection-templates/inspectionDetails.html', 
      controller: inspectionDetailsController 
    }
  )
  .otherwise(
    {redirectTo: '/review'}
  );

});

postInspection.factory('testFactory', function($http){
  
  //initialize a factory object
  var tempFactory = {};
  
  //simple 'getter' to grab data from service layer
  tempFactory.getChecklists = function(onSuccess, url){
  
  //user jsonp method of the angularjs $http object to request data from service layer
  $http.jsonp(url)
    .success( function(data) {
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


controllers = {};

mainController = function($scope, $location, convenienceMethods){
  console.log($location);
  $scope.setRoute = function(route){
    $location.path(route);
  }

  init();
  function init(){
    $scope.doneLoading = false;
    convenienceMethods.getData('../../../ajaxaction.php?action=getInspectionById&id=1&callback=JSON_CALLBACK',onGetInspection, onFailGet);
  };
  function onGetInspection(data){
    data.DateCreated = convenienceMethods.getDate(data.DateCreated);
    $scope.Inspection = data;
    $scope.doneLoading = data.doneLoading;
  }
  function onFailGet(){
    alert('There was an error finding the inspection.');
  }
}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods){
  
  init();
  function init(){
    $scope.doneLoading = false;
    convenienceMethods.getData('../../../ajaxaction.php?action=getInspectionById&id=1&callback=JSON_CALLBACK',onGetChecklists, onFailGet);
  };
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {  
    //console.log(data);
  	$scope.PrincipalInvestigator = data.PrincipalInvestigator.User;
    $scope.Contacts = data.PrincipalInvestigator.LabPersonnel
    $scope.doneLoading = data.doneLoading;

  }
  function onFailGet(data){
  	alert('There was a problem trying to get your getData');
  }

  $scope.contactList = [];

  $scope.handleContactList = function(obj, $index){

  	if(!convenienceMethods.arrayContainsObject($scope.contactList,obj)){
  		$scope.contactList.push(obj);
  	}else{
  		angular.forEach($scope.contactList, function(value, key){
  			if(value.KeyId === obj.KeyId){
  				console.log(key);
  				$scope.contactList.splice(key,1);
  			}
  		});
  	}

  }

  $scope.sendEmail = function(){
  	//todo:  send email
  	//convenienceMethods.updateObject();
    $scope.setRoute('/review');
  }

}

inspectionReviewController = function($scope,  $location, $anchorScroll, convenienceMethods,$filter){
  
  init();

  function init(){
    $scope.doneLoading = false;
    convenienceMethods.getData('../../../ajaxaction.php?action=getInspectionById&id=1&callback=JSON_CALLBACK',onGetResponses, onFailGet);
  };

  function onGetResponses(data){
    $scope.responses = data.Responses;
    console.log($scope.responses);

    var parentHazards = ['BIOLOGICAL SAFETY', 'CHEMICAL SAFETY', 'RADIATION SAFETY'];
    var complianceDescriptions = ['All biological safety cabinets must be certified annually. This certification involves a process of inspection and testing by trained personnel, following strict protocols, to verify that it is working properly. This certification should be scheduled by contacting Tom Gardner with Biological Control Services at (919) 906-3046.',
    'Not established The OSHA Laboratory Standard requires that each laboratory establish and maintain a Chemical Hygiene Plan. This plan should include procedures, equipment, PPE and work practices that are designed to protect employees from the health hazards presented by hazardous chemicals used in the lab. The plan must be accessible to lab personnel at all times. Chemical Hygiene Plan: http://ehs.sc.edu/LabSafety.htm. This plan must be reviewed and updated annually and signed by all lab staff.',
    'Weekly wipe surveys from radioisotope work areas must be properly documented, maintained, and available upon request by EH&S.'
    ];

    angular.forEach($scope.responses, function(value, key){

      value.complianceReference = Math.random().toString(36).substring(7);
      value.CorrectiveAction = {};
      var randomParentHazard = parentHazards[Math.floor(Math.random()*parentHazards.length)];
      value.ParentHazard = randomParentHazard;
      value.CorrectiveAction.beingEdited = false;
      console.log(value);
      if(randomParentHazard == 'BIOLOGICAL SAFETY'){
        value.complianceDescription = complianceDescriptions[0];
        value.CorrectiveAction.Status = 'incomplete';
        value.CorrectiveAction.Description = 'Re-certification scheduled for 07/01/12';
        value.CorrectiveAction.Date = convenienceMethods.getDate(data.DateCreated);

      }else if(randomParentHazard == 'CHEMICAL SAFETY'){
        value.complianceDescription = complianceDescriptions[1];

        value.CorrectiveAction.Status = 'notStarted';
        value.CorrectiveAction.Description = '';
        value.CorrectiveAction.Date = convenienceMethods.getDate(data.DateCreated);

      }else if(randomParentHazard == 'RADIATION SAFETY'){
        value.complianceDescription = complianceDescriptions[2];

        value.CorrectiveAction.Status = 'complete';
        value.CorrectiveAction.Description = 'Chemical Hygiene Plan was established, and is now accessible to all lab staff';
        value.CorrectiveAction.Date = convenienceMethods.getDate(data.DateCreated);

      }
    });
  
    $scope.doneLoading = data.doneLoading;

    $scope.criteria = 'Replace mercury thermometers';
    $scope.recommendation = 'Mercury from broken thermometers presents a hazard to laboratory personnel, and hazardous waste that is costly for clean-up and disposal. Non-mercury thermometers are available that are accurate, safe, and less toxic. EH&S recommends that mercury thermometers be replaced with non-mercury alternatives.';
  }

  function onFailGet(data){
    alert('There was a problem when trying to get your data');
  }

  $scope.editCorrectiveAction = function(CorrectiveAction){
    console.log(CorrectiveAction);
    CorrectiveAction.beingEdited = true;
    $scope.CorrectiveActionCopy = angular.copy(CorrectiveAction);
    console.log($scope.CorrectiveActionCopy);
  }

  $scope.saveCorrectiveAction = function(CorrectiveAction){
    CorrectiveAction.beingEdited = false;
    console.log(CorrectiveAction);
    CorrectiveAction.Description = $scope.CorrectiveActionCopy.Description;
    CorrectiveAction.Date = $scope.CorrectiveActionCopy.Date;
    CorrectiveAction.Status = $scope.CorrectiveActionCopy.Status;
    console.log(CorrectiveAction);
  }

  $scope.cancelEdit = function(action){
    action.beingEdited = false;
  }

}
function inspectionDetailsController($scope,  $location, $anchorScroll, testFactory) {
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    console.log('init called');
    testFactory.getChecklists(onGetChecklists,'/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&checklists=true');
  };
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {
    console.log(data);
    onFinishLoop(data.Checklists);
  }

  function onFinishLoop(data){
   $scope.checklists = data;
    var answers = ['Yes','No','N/A'];
    for(i=0;i<$scope.checklists.length;i++){
      var checklist = $scope.checklists[i];
      for(x=0;x<checklist.questions.length;x++){
        var question = checklist.questions[x];
        question.response = answers[Math.floor(Math.random()*answers.length)];
        if(question.response == 'No'){
          question.deficiency = question.deficiencies[Math.floor(Math.random()*question.deficiencies.length)].text;
          question.rootCause = question.deficiencyRootCauses[Math.floor(Math.random()*question.deficiencyRootCauses.length)];
          console.log(question);
        }
      }
    }
    $scope.doneLoading = true;
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


};
/*
inspectionStandardViewController = function($scope,  $location, $anchorScroll, convenienceMethods){
  
  $scope.doneLoading = false;
  init();
  function init(){
    convenienceMethods.getData('/Erasmus/src/views/api/hazardAssApi.php?callback=JSON_CALLBACK&checklists=true',onGetChecklists, onFailGet);
  };
  
  //grab set user list data into the $scrope object
  function onGetChecklists(data) {
    console.log(data);
    console.log('controlled');
    $scope.Checklists = data.Checklists;
    $scope.doneLoading = data.doneLoading;
  }
  function onFailGet(data){
    alert('There was a problem trying to get your getData');
  }
}*/

//postInspection.controller(controllers);