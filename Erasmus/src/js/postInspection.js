var postInspection = angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule','ngQuickDate']);
postInspection.filter('joinBy', function () {
  return function (input,delimiter) {
    return (input || []).join(delimiter || ',');
  };
});

//configure datepicker util
postInspection.config(function(ngQuickDateDefaultsProvider) {
  return ngQuickDateDefaultsProvider.set({
    closeButtonHtml: "<i class='icon-cancel-2'></i>",
    buttonIconHtml: "<i class='icon-calendar-2'></i>",
    nextLinkHtml: "<i class='icon-arrow-right'></i>",
    prevLinkHtml: "<i class='icon-arrow-left'></i>",
    // Take advantage of Sugar.js date parsing
    parseDateFunction: function(str) {
      return new Date(Date.parse(str));
    }
  });
});


postInspection.config(function($routeProvider){

  $routeProvider
  .when('/confirmation', 
    {
      templateUrl: 'post-inspection-templates/inspectionConfirmation.html', 
      controller: inspectionConfirmationController
    }
  )
  .when('/report', 
    {
      templateUrl: 'post-inspection-templates/standardView.html', 
      controller: inspectionReviewController 
    }
  )
  .when('/details', 
    {
      templateUrl: 'post-inspection-templates/inspectionDetails.html', 
      controller: inspectionDetailsController 
    }
  )
  .otherwise(
    {redirectTo: '/report'}
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

mainController = function($scope, $location, convenienceMethods, $rootScope){
 // console.log($location);
  $scope.setRoute = function(route){
    $location.path(route);
  }

  init();
  function init(){
    $scope.doneLoading = false;
    insp = $location.search().inspection;
    convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);
    convenienceMethods.getData('../../ajaxaction.php?action=getUserById&id=1&callback=JSON_CALLBACK',onGetUser, onFailGetUser);
  };
  function onGetUser(data){
    $scope.User = data;
   // console.log($scope.User);
  }
  function onFailGetUser(){
    alert("There was a problem retrieving your user information");
  }
  function onGetInspection(data){
    console.log(data);
    if(data.Date_last_modified)var date = convenienceMethods.getDate(data.Date_last_modified);
    if(data.Date_last_modified)data.Date_last_modified = date.formattedString;
    $scope.Inspection = data;
    $rootScope.Inspection = data;
    $scope.doneLoading = data.doneLoading;
    console.log($rootScope);
  }
  function onFailGet(){
    alert('There was an error finding the inspection.');
  }

  //initialize date controls
  $scope.today = function() {
    $scope.dt = new Date();
  };

  $scope.today();

  $scope.showWeeks = true;
  $scope.toggleWeeks = function () {
    $scope.showWeeks = ! $scope.showWeeks;
  };

  $scope.clear = function () {
    $scope.dt = null;
  };

  // Disable weekend selection
  $scope.disabled = function(date, mode) {
    //return ( mode === 'day' && ( date.getDay() > $scope.Inspection.Date_last_modified) );
  };

  $scope.toggleMin = function() {
    $scope.minDate = ( $scope.minDate ) ? null : new Date();
  };
  $scope.toggleMin();

  $scope.open = function($event) {
    $event.preventDefault();
    $event.stopPropagation();
    $scope.opened = true;
  };

  $scope.dateOptions = {
    'year-format': "'yy'",
    'starting-day': 1
  };

  $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'shortDate'];
  $scope.format = $scope.formats[0];

}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods,$rootScope){
  
  init();
  function init(){
    console.log($rootScope);
    $scope.doneLoading = false;
    if(!$rootScope.Inspection){
       insp = $location.search().inspection;
       convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);
    }else{
      console.log('here');
      $scope.Inspection = $rootScope.Inspection;
      onGetInspection($scope.Inspection);
    }
    $scope.defaultNote = "These are your results from the recent inspection of your laboratory."
   
  };

  $scope.addOtherRecipient = function(){
    var other = "";
    $scope.others.push(other);
  }
  
  $scope.addOtherRecipient

  //grab set user list data into the $scrope object
  function onGetInspection(data) {  
    $scope.inspection = data;
    $scope.doneLoading = data.doneLoading;
    $scope.others = [{email:''}];
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

  $scope.handleContactList = function(contact){
    if(contact.include){
      $scope.contactList.push(contact.Key_id);
    }else{
      $scope.contactList.splice( $scope.contactList.indexOf(contact.Key_id),1);
    }
    console.log($scope.contactList);
  }

  $scope.sendEmail = function(){

    othersToSendTo = [];

    angular.forEach($scope.others, function(other, key){
      othersToSendTo.push(other.email);
    });

    var emailDto ={
      Class: "EmailDto",
      Entity_id: $scope.Inspection.Key_id,
      Recipient_ids: $scope.contactList,
      Other_emails: othersToSendTo,
      Text: $scope.defaultNote
    }

    console.log(emailDto);
    var url = '../../ajaxaction.php?action=sendInspectionEmail';
    convenienceMethods.sendEmail(emailDto, onSendEmail, onFailSendEmail, url);
    $scope.sending = true;
   // $scope.setRoute('/review');
  }

  function onSendEmail(data){
    $scope.sending = false;
    $scope.emailSent = 'success';
  }

  function onFailSendEmail(){
    $scope.sending = false;
    $scope.emailSent = 'error';
    alert('There was a problem when the system tried to send the email.');
  }


}

inspectionReviewController = function($scope, $location, $anchorScroll, convenienceMethods, $filter, $rootScope){
  
  init();

  function init(){
    $scope.doneLoading = false;

    if(!$rootScope.Inspection){
      insp = $location.search().inspection;
      convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);;
    }else{
      onGetInspection($rootScope.Inspection);
    }
    $scope.options = ['Incomplete','Pending','Complete'];

  };

  function calculateScore(){
    if(!$scope.score)$scope.score = {};
    $scope.score.itemsInspected = 0;
    $scope.score.deficiencyItems = 0;
    $scope.score.compliantItems = 0;
    angular.forEach($scope.Inspection.Checklists, function(checklist, key){
      angular.forEach(checklist.Questions, function(question, key){
        $scope.score.itemsInspected++;
        if(question.Responses && question.Responses.Answer && question.Responses.Answer == 'no'){
          $scope.score.deficiencyItems++;
        }else /*if(question.Responses && question.Responses.Answer)*/{
          $scope.score.compliantItems++;
          console.log('here');
        }
      });
    });

    console.log(parseInt($scope.score.compliantItems));

    $scope.score.score = Math.round(parseInt($scope.score.compliantItems)/parseInt($scope.score.itemsInspected) * 100);
    //$scope.score.score = 10/11;
  }

  function onGetInspection(data){
    $scope.recommendations = [];
    angular.forEach(data.Checklists, function(checklist, key){
      console.log(checklist);
      if(!checklist.Responses)checklist.Responses = [];
      angular.forEach(checklist.Questions, function(question, key){

        if(question.Responses && question.Responses.Recommendations && question.Responses.Recommendations.length){
          angular.forEach(question.Responses.Recommendations, function(recommendation, key){
            $scope.recommendations.push(recommendation);
          });
        }

        if(question.Responses && question.Responses.Answer.toLowerCase() == "no"){
          angular.forEach(question.Responses.DeficiencySelections, function(def, key){
             def.questionText = question.Text;
             if(!def.CorrectiveActions.length){
              def.CorrectiveActions[0]={
                Class: "CorrectiveAction",
                Deficiency_selection_id: def.Key_id,
                Status: "Incomplete"
              }
            }
            def.CorrectiveActionCopy =  angular.copy(def.CorrectiveActions[0]);
            if(def.CorrectiveActionCopy.Completion_date)def.CorrectiveActionCopy.Completion_date = convenienceMethods.getDate(def.CorrectiveActionCopy.Completion_date);
            console.log(def.CorrectiveActionCopy);
            checklist.Responses.push(def);
          });
        }
      });
    });

    var parentHazards = ['BIOLOGICAL SAFETY', 'CHEMICAL SAFETY', 'RADIATION SAFETY'];

    orderedChecklists = [];

    $scope.typeFlag = '';
  //  angular.forEach($scope.checklists, function(checklist, key){
    for(var i=0;data.Checklists.length>i;i++){
      var checklist = data.Checklists[i];
      console.log(checklist);
      if(checklist.Responses){
        console.log('true');
        if(checklist.Name.indexOf('iological') > -1){
          //set bio flag
          $scope.typeFlag = 'bioHazards';
        }
        if(checklist.Name.indexOf('hemical') > -1){
          //set checm flag
           $scope.typeFlag = 'chemHazards';
        }

        if(checklist.Name.indexOf('adiation') > -1){
          //set radiation flag
           $scope.typeFlag = 'radHazards';
        }

        if(checklist.Name.indexOf('eneral') > -1){
          //set general flag
           $scope.typeFlag = 'genHazards';
        }

        //interpret flag, push checklist into proper array
        if(!$scope[ $scope.typeFlag ])$scope[ $scope.typeFlag ] = [];
        $scope[ $scope.typeFlag ].push(checklist);

        //push all the responses foreach question in the checklist to a master array of reponses foreach hazard type
        if(checklist.Responses.length){
          $scope[ $scope.typeFlag ].Responses = [];
          angular.forEach(checklist.Responses, function(response, key){
            $scope[ $scope.typeFlag ].Responses.push(response);
          });
        }

      }
    }

  // if(orderedChecklists.bioHazards)$scope.bioHazards = orderedChecklists.bioHazards;
   console.log($scope.bioHazards);
   if(data.Date_last_modified)data.Date_last_modified = convenienceMethods.getDate(data.Date_last_modified );
   $scope.Inspection = data;
 
    $scope.doneLoading = data.doneLoading;
    calculateScore();
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

  $scope.saveCorrectiveAction = function(CorrectiveActionCopy,CorrectiveAction,accept){
    $scope.CorrectiveActionCopy = angular.copy(CorrectiveActionCopy);
    CorrectiveActionCopy.IsDirty = true;
    if($scope.CorrectiveActionCopy.Completion_date)$scope.CorrectiveActionCopy.Completion_date = convenienceMethods.setMysqlTime(CorrectiveActionCopy.Completion_date);
    if(accept)$scope.CorrectiveActionCopy.Status = "Accepted";
    console.log($scope.CorrectiveActionCopy);
    var url = "../../ajaxaction.php?action=saveCorrectiveAction";
    convenienceMethods.updateObject( $scope.CorrectiveActionCopy, CorrectiveActionCopy, onSaveCorrectiveAction, onSaveFailCorrectiveAction, url, $scope.CorrectiveActionCopy, CorrectiveAction);

  }

  function onSaveCorrectiveAction(returned, old, CorrectiveAction){
    console.log(CorrectiveAction);
    CorrectiveAction.Status = returned.Status;
    old.IsDirty = false;
  }

  function onSaveFailCorrectiveAction(data){
    alert("Something went wrong when the system tried to save the Corrective Action");
    data = angular.copy($scope.CorrectiveActionCopy);
    data.IsDirty = false;
  }

  $scope.cancelEdit = function(action){
    action.beingEdited = false;
  }

  $scope.afterInspection = function(d){
    //console.log( Date.parse(d));
    //console.log(Date.parse($scope.Inspection.DateCreated));
    console.log(Date.parse(d));
    console.log(Date.parse($scope.Inspection.Date_last_modified));
    if(Date.parse(d)>Date.parse($scope.Inspection.Date_last_modified)){
      return true;
    }
    return false;
  }

}
function inspectionDetailsController($scope, $location, $anchorScroll, convenienceMethods, $rootScope, $http) {
  init();
  
  //call the method of the factory to get users, pass controller function to set data inot $scope object
  //we do it this way so that we know we get data before we set the $scope object
  //
  function init(){
    if(!$rootScope.Inspection){
      insp = $location.search().inspection;
      convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id='+insp+'&callback=JSON_CALLBACK',onGetInspection, onFailGet);
    }else{
      console.log('here');
      $scope.Inspection = $rootScope.Inspection;
      onGetInspection($scope.Inspection);
    }
  };
  
  //grab set user list data into the $scrope object
  function onGetInspection(data) {
    console.log(data);
    $scope.Inspection = data;
   

    //onFinishLoop(data.Checklists);
  }

  function onFailGet(){
    alert('There was an error getting the Inspection');
  }

  function onFinishLoop(data){
   $scope.checklists = data;
    var answers = ['Yes','No','N/A'];
    for(i=0;i<$scope.checklists.length;i++){
      var checklist = $scope.checklists[i];
      for(x=0;x<checklist.Questions.length;x++){
        var question = checklist.Questions[x];
        question.Response = answers[Math.floor(Math.random()*answers.length)];
        if(question.Response == 'No'){
          question.Deficiency = question.Deficiencies[Math.floor(Math.random()*question.Deficiencies.length)].text;
          question.RootCause = question.DeficiencyRootCauses[Math.floor(Math.random()*question.DeficiencyRootCauses.length)];
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

  $scope.resetEdits = function(newCorrAct,oldCorrAct){
    newCorrAct.Text = oldCorrAct.Text;
    newCorrAct.Completion_date = oldCorrAct.Completion_date;
  }
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