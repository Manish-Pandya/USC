angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule','ngQuickDate','ngRoute','once'])
/*
.directive('htmlText', function(){
  return {
    'restrict': 'A',
    'require': 'ngModel',
    'link': function(scope,element,attrs,model) {
      console.log(model);
      model.$formatters.push(function(val){
        return val.Text;
      });

      model.$parsers.push(function(val){
        model.$modelValue.Text = val;
      });
    } 
  };
})
*/
.run(function($rootScope, $templateCache) {
   $rootScope.$on('$viewContentLoaded', function() {
      $templateCache.removeAll();
   });
})

.filter('joinBy', function () {
  return function (input,delimiter) {
    return (input || []).join(delimiter || ',');
  };
})

//configure datepicker util
.config(function(ngQuickDateDefaultsProvider) {
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
})

.config(function($routeProvider){

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
})

.factory('postInspectionFactory', function(convenienceMethods,$q){

  var factory = {};
  var inspection = {};
  factory.recommendations = [];
  factory.observations = [];

  factory.getInspectionData = function(url){
    //return convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById3&id=132&callback=JSON_CALLBACK', this.onFailGet);
  };

  factory.getInspection = function(){
    return this.inspection;
  };

  factory.updateInspection = function(){
    //this should call convenienceMethods call to update an object on the server
  };

  factory.setInspection = function(inspection){
    return this.inspection = inspection;
  };

  factory.saveCorrectiveAction = function(action){
    var url = "../../ajaxaction.php?action=saveCorrectiveAction";
    var deferred = $q.defer();

    convenienceMethods.saveDataAndDefer(url, action).then(
      function(promise){
        deferred.resolve(promise);
      },
      function(promise){
        deferred.reject(promise);
      }
    );
    return deferred.promise
  }

  factory.onFailGet = function(){
    return {'data':error}
  };

  factory.organizeChecklists = function(checklists){

    //set a checklists object that we can use elsewhere
    this.checklists = checklists;

    //object with array properties to contain the checklists
    checklistHolder = {};
    checklistHolder.biologicalHazards = {name: "Biological Saftey", checklists:[]};
    checklistHolder.chemicalHazards = {name: "Chemical Saftey", checklists:[]};
    checklistHolder.radiationHazards = {name: "Radiation Safety", checklists:[]};
    checklistHolder.generalHazards = {name: "General Safety", checklists:[] };

    //group the checklists by parent hazard
    //get the questions for each checklist and store them in a property that the view can access easily
    for(i=0;i<checklists.length;i++){
      var checklist = checklists[i];

      if(checklist.Master_hazard.toLowerCase().indexOf('biological') > -1){
        if(!checklistHolder.biologicalHazards.Questions)checklistHolder.biologicalHazards.Questions = [];
        checklistHolder.biologicalHazards.checklists.push(checklist);
        checklistHolder.biologicalHazards.Questions = checklistHolder.biologicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('chemical') > -1){
        if(!checklistHolder.chemicalHazards.Questions)checklistHolder.chemicalHazards.Questions = [];
        checklistHolder.chemicalHazards.checklists.push(checklist);
        checklistHolder.chemicalHazards.Questions = checklistHolder.chemicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('radiation') > -1){
        if(!checklistHolder.radiationHazards.Questions)checklistHolder.radiationHazards.Questions = [];
        checklistHolder.radiationHazards.checklists.push(checklist);
        checklistHolder.radiationHazards.Questions = checklistHolder.radiationHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('general') > -1){
        if(!checklistHolder.generalHazards.Questions)checklistHolder.generalHazards.Questions = [];
        checklistHolder.generalHazards.checklists.push(checklist);
        checklistHolder.generalHazards.Questions = checklistHolder.generalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
    }
    this.evaluateChecklistCategory( checklistHolder.biologicalHazards );
    this.evaluateChecklistCategory( checklistHolder.chemicalHazards );
    this.evaluateChecklistCategory( checklistHolder.radiationHazards );
    this.evaluateChecklistCategory( checklistHolder.generalHazards );

    return checklistHolder;
  };

  factory.getQuestionsByChecklist = function(checklist){
    return checklist.Questions;
  }

  factory.evaluateChecklistCategory = function( category )
  {
      if(!category.Questions){
        //there weren't any hazards in this category
        //hide the whole category
        console.log(category.name+' had no hazards in these labs');
        category.message = false;
        category.show = false
      }else if( category.Questions.some(this.isAnsweredNo) ){
        console.log(category.name+' some questions were no');
        //some questions are answered no
        //display as normal
        category.show = true;
        category.message = false;
      }else if( category.Questions.every(this.notAnswered) ){
        console.log(category.name+' no questions were answered');
        //there were checklists but no questions were answered
        category.show = true;
        category.message = category.name+' hazards were not evaluated during this laboratory safety inspection.';
        console.log(category);

      }else{
        console.log(category.name+' there were no deficiencies');
        //there were no deficiencies
        category.show = true;
        category.message = 'No '+category.name+' deficiencies were identified during this laboratory safety inspection.';
      }

  }

  factory.isAnsweredNo = function(question)
  {
      if(question.Responses && question.Responses.Answer == 'no')return true;
      return false;
  }

  factory.notAnswered = function(question)
  {
      if(question.Responses && !question.Responses.Answer)return true
      return false;
  }

  //set a matching view property for a mysql datetime property of an object
  factory.setDateForView = function(obj, dateProperty){
    var dateHolder = convenienceMethods.getDate(obj[dateProperty]);
    obj['view'+dateProperty] = dateHolder.formattedString;
    return obj;
  }

  factory.setDatesForServer = function(obj, dateProperty){
    //by removing the string 'view' from the date property, we access the orginal MySQL datetime from which the property was set
    //i.e. corrective_action.viewPromised_date is the matching property to corrective_action.Promised_date
    obj[dateProperty.replace('view','')] = convenienceMethods.setMysqlTime(obj[dateProperty]);
    return obj;
  }

  //calculate the inspection's scores
  factory.calculateScore = function(inspection){
    if(!inspection.score)inspection.score = {};
    inspection.score.itemsInspected = 0;
    inspection.score.deficiencyItems = 0;
    inspection.score.compliantItems = 0;
    angular.forEach(inspection.Checklists, function(checklist, key){
      angular.forEach(checklist.Questions, function(question, key){
        inspection.score.itemsInspected++;
        if(question.Responses && question.Responses.Answer && question.Responses.Answer == 'no'){
          inspection.score.deficiencyItems++;
        }else /*if(question.Responses && question.Responses.Answer)*/{
          inspection.score.compliantItems++;
        }
      });
    });

    //javascript does not believe that 0 is a number in spite of my long philosophical debates with it
    //if either compliantItems or itemsInspected is 0, we cannot calculate because they are undefined according to JS
    if(inspection.score.compliantItems && inspection.score.itemsInspected){
      //we have both numbers, so we can calculate a score
      inspection.score.score = Math.round(parseInt(inspection.score.compliantItems)/parseInt(inspection.score.itemsInspected) * 100);
    }else{
      //since 0 is undefined, we se this property to the String "0"
      inspection.score.score = '0';
    }
    return this.inspection = inspection;
  }

  factory.setRecommendationsAndObservations = function()
  {   

        var defer = $q.defer();

        var checklistLength = this.inspection.Checklists.length;

        for(var i = 0; i < checklistLength; i++){

            var checklist = this.inspection.Checklists[i];

            var questions = checklist.Questions;
            var qLength   = questions.length

            for(var j = 0; j < qLength; j++){

                var question = questions[j];
                if(question.Responses && question.Responses.Recommendations) {
                  //now the time-wasting step of getting the question text for every recommendation.  this could be done by reference in the new orm framekwork

                  var recLen = question.Responses.Recommendations.length;

                  for(var k = 0; k < recLen; k++){
                        question.Responses.Recommendations[k].Question = question.Text;
                  }

                  this.recommendations = this.recommendations.concat(question.Responses.Recommendations);
                }
                if(question.Responses && question.Responses.SupplementalRecommendations) {
                  //now the time-wasting step of getting the question text for every recommendation.  this could be done by reference in the new orm framekwork
                  var recLen = question.Responses.SupplementalRecommendations.length;

                  for(var k = 0; k < recLen; k++){
                        question.Responses.SupplementalRecommendations[k].Question = question.Text;
                  }

                  this.recommendations = this.recommendations.concat(question.Responses.SupplementalRecommendations);
                }

                if(question.Responses && question.Responses.Observations) {
                  this.observations = this.observations.concat(question.Responses.Observations);
                }
                if(question.Responses && question.Responses.SupplementalObservations) {
                  this.observations = this.observations.concat(question.Responses.SupplementalObservations);
                }

            }
        }

        defer.resolve();
        return defer.promise;
  }

  factory.getRecommendations = function()
  {
          return this.recommendations;
  }

  factory.getObservations = function()
  {
          return this.observations;
  }

  factory.getNumberOfRoomsForQuestionByChecklist = function( question )
  {
          var i = this.inspection.Checklists.length;
          while(i--){
            if(question.Checklist_id == this.inspection.Checklists[i].Key_id)return this.inspection.Checklists[i].InspectionRooms.length;
          }
          return false;
  }

  return factory;
});

mainController = function($scope, $location, postInspectionFactory,convenienceMethods){
  $scope.route = $location.path();
  $scope.setRoute = function(route){
    $location.path(route);
    $scope.route = route;
  }
  /*
  if(!postInspectionFactory.getInspection()){
    convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=132&callback=JSON_CALLBACK')
      .then(function(promise){
        inspection = promise.data;
        console.log(inspection);
        postInspectionFactory.setInspection(promise.data);
        $scope.inspection = postInspectionFactory.getInspection();
      });
  }else{
    $scope.inspection = postInspectionFactory.getInspection();
  }
  */
}
inspectionDetailsController = function($scope, $location, $anchorScroll, convenienceMethods,postInspectionFactory, $rootScope){
  $scope.getNumberOfRoomsForQuestionByChecklist = postInspectionFactory.getNumberOfRoomsForQuestionByChecklist;
    function init(){
     if($location.search().inspection){
        var id = $location.search().inspection;
        if(!postInspectionFactory.getInspection()){
          $scope.doneLoading = false;
          convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=resetChecklists&id='+id+'&callback=JSON_CALLBACK', onFailGetInspeciton)
            .then(function(promise){
              console.log(promise.data);

              //set the inspection date as a javascript date object
              if(promise.data.Date_started)promise.data = postInspectionFactory.setDateForView(promise.data,"Date_started");
              $scope.inspection = promise.data;
              $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
              $scope.doneLoading = true;
              // call the manager's setter to store the inspection in the local model
              postInspectionFactory.setInspection($scope.inspection);
              postInspectionFactory.setRecommendationsAndObservations()
                  .then(
                    function(){
                      $scope.recommendations = postInspectionFactory.getRecommendations();
                    });


              $scope.doneLoading = true;
              //postInspection factory's organizeChecklists method will return a list of the checklists for this inspection
              //organized by parent hazard
              //each group of checklists will have a Questions property containing all questions for each checklist in a given category
              $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);

              console.log($scope.questionsByChecklist);
            });
        }else{
          $scope.inspection = postInspectionFactory.getInspection();
          $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
          $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);
          $scope.doneLoading = true;
        }
        $scope.options = ['Incomplete','Pending','Complete'];
      }else{
        $scope.error = 'No inspection has been specified';
      }
  }
  init();

  
  function onFailGetInspeciton(){
    $scope.doneLoading = true;
    $scope.error="The system couldn't find the inspection.  Check your internet connection."
  }

  $scope.someAnswers = function(checklist){
    if(checklist.Questions.some(isAnswered)) return true;
    return false;
  }

  function isAnswered(question){
    if(question.Responses && question.Responses.Answer)return true;
    return false;
  }


}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods,postInspectionFactory, $rootScope){
  if($location.search().inspection){
    var id = $location.search().inspection;

    if(!postInspectionFactory.getInspection()){

      $scope.doneLoading = false;
      convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id='+id+'&callback=JSON_CALLBACK', onFailGetInspeciton)
        .then(function(promise){
          $scope.inspection = promise.data;
          if(promise.data.Date_started)promise.data = postInspectionFactory.setDateForView(promise.data,"Date_started");
          console.log(promise.data);
          //set view init values for email
          $scope.others = [{email:''}];
          $scope.defaultNote = {};
          $scope.defaultNote.Text = "We appreciate you for taking the time to meet with EHS for your annual laboratory safety inspection on "+$scope.inspection.viewDate_started+". You can access the lab safety inspection report using your University username and password at the following link: radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection="+$scope.inspection.Key_id+" .\nPlease submit your lab's corrective action plan for each deficiency included in the report within the next two weeks.\nThank you for supporting our efforts to maintain compliance and ensure a safe research environment for all USC's faculty, staff, and students.\nBest regards,\nEHS Research Safety ";

          $scope.doneLoading = true;
          // call the manager's setter to store the inspection in the local model
          postInspectionFactory.setInspection($scope.inspection);
          $scope.doneLoading = true;
        });
    }else{
      //set view init values for email
      $scope.others = [{email:''}];
      $scope.defaultNote = {};
      $scope.inspection = postInspectionFactory.getInspection();
      $scope.defaultNote.Text = "We appreciate you for taking the time to meet with EHS for your annual laboratory safety inspection on "+$scope.inspection.viewDate_started+". You can access the lab safety inspection report using your University username and password at the following link: radon.qa.sc.edu/rsms/views/inspection/InspectionConfirmation.php#/report?inspection="+$scope.inspection.Key_id+" .\nPlease submit your lab's corrective action plan for each deficiency included in the report within the next two weeks.\nThank you for supporting our efforts to maintain compliance and ensure a safe research environment for all USC's faculty, staff, and students.\nBest regards,\nEHS Research Safety ";
    }
  }else{
    $scope.error = 'No inspection has been specified';
  }

  function onFailGetInspeciton(){
    $scope.doneLoading = true;
    $scope.error="The system couldn't find the inspection.  Check your internet connection."
  }

  $scope.contactList = [];

  $scope.handleContactList = function(obj, $index){

    if(!convenienceMethods.arrayContainsObject($scope.contactList,obj)){
      $scope.contactList.push(obj);
    }else{
      angular.forEach($scope.contactList, function(value, key){
        if(value.KeyId === obj.KeyId){
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
  }

  $scope.sendEmail = function(){

    othersToSendTo = [];

    angular.forEach($scope.others, function(other, key){
      othersToSendTo.push(other.email);
    });

    var emailDto ={
      Class: "EmailDto",
      Entity_id: $scope.inspection.Key_id,
      Recipient_ids: $scope.contactList,
      Other_emails: othersToSendTo,
      Text: $scope.defaultNote.Text
    }

    var url = '../../ajaxaction.php?action=sendInspectionEmail';
    convenienceMethods.sendEmail(emailDto, onSendEmail, onFailSendEmail, url);
    $scope.sending = true;
  }

  function onSendEmail(data){
    $scope.sending = false;
    $scope.emailSent = 'success';
    
    console.log($rootScope.inspection);
    evaluateCloseInspection();

  }

  function onFailSendEmail(){
    $scope.sending = false;
    $scope.emailSent = 'error';
    alert('There was a problem when the system tried to send the email.');
  }


  function evaluateCloseInspection(){
    var setCompletedDate  = true;
    $rootScope.inspection = $scope.inspection;
    //$rootScope.Checklists = angular.copy($rootScope.inspection.Checklists);
    angular.forEach($rootScope.Checklists, function(checklist, key){
        angular.forEach(checklist.Questions, function(question, key){
          if(question.Responses && question.Responses.DeficiencySelections){
            angular.forEach(question.Responses.DeficiencySelections, function(defSel, key){
              if(!defSel.Corrected_in_inspection)setCompletedDate = false;
            });
          }
        });
    });
    if(setCompletedDate)setInspectionClosed();
  }

  function setInspectionClosed(){
    var inspectionDto = angular.copy($rootScope.inspection);
    inspectionDto.date_closed = new Date();
    console.log(inspectionDto);
    var url = "../../ajaxaction.php?action=saveInspection";
    convenienceMethods.updateObject( inspectionDto, null, onSetInspectionClosed, onFailSetInspecitonClosed, url);
  }

  function onSetInspectionClosed(data){
    console.log('saved');
    data.Checklists = angular.copy($rootScope.Checklists);
    $rootScope.inspection = data;
    $rootScope.inspection.closed = true;
    $scope.inspection = $rootScope.inspection;
    console.log($rootScope.inspection);
  }

  function onFailSetInspecitonClosed(){
    alert("There was an issue when the system tried to set the Inpsection's closeout date");
  }

}

inspectionReviewController = function($scope, $location, convenienceMethods, postInspectionFactory,$rootScope){
  $scope.getNumberOfRoomsForQuestionByChecklist = postInspectionFactory.getNumberOfRoomsForQuestionByChecklist;
  function init(){
    if($location.search().inspection){
      var id = $location.search().inspection;
      if(!postInspectionFactory.getInspection()){
        $scope.doneLoading = false;
        convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=resetChecklists&id='+id+'&callback=JSON_CALLBACK', onFailGetInspeciton)
          .then(function(promise){
            console.log(promise.data);

            //set the inspection date as a javascript date object
            if(promise.data.Date_started)promise.data = postInspectionFactory.setDateForView(promise.data,"Date_started");
            $scope.inspection = promise.data;
            $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
            $scope.doneLoading = true;
            // call the manager's setter to store the inspection in the local model
            postInspectionFactory.setInspection($scope.inspection);

            postInspectionFactory.setRecommendationsAndObservations()
                .then(
                  function(){
                    $scope.recommendations = postInspectionFactory.getRecommendations();
                  });


            $scope.doneLoading = true;
            //postInspection factory's organizeChecklists method will return a list of the checklists for this inspection
            //organized by parent hazard
            //each group of checklists will have a Questions property containing all questions for each checklist in a given category
            $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);
          });
      }else{
        $scope.inspection = postInspectionFactory.getInspection();
        $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
        $scope.questionsByChecklist = postInspectionFactory.organizeChecklists($scope.inspection.Checklists);
        $scope.doneLoading = true;
      }
      $scope.options = ['Incomplete','Pending','Complete'];
    }else{
      $scope.error = 'No inspection has been specified';
    }
  }
  init();


  function onFailGetInspeciton(){
    $scope.doneLoading = true;
    $scope.error="The system couldn't find the inspection.  Check your internet connection."
  }

  //parse function to ensure that users cannot set the date for a corrective action before the date of the inspection
  $scope.afterInspection = function(d){
    var calDate = Date.parse(d);
    //inspection date pased into seconds minus the number of seconds in a day.  We subtract a day so that the inspection date will return true
    var inspectionDate = Date.parse($scope.inspection.viewDate_started)-864000;
    if(calDate>=inspectionDate){
      return true;
    }
    return false;
  }

  $scope.todayOrAfter = function(d){
    var calDate = Date.parse(d);
    //today's date parsed into seconds minus the number of seconds in a day.  We subtract a day so that today's date will return true
    var now = new Date(),
    then = new Date(
        now.getFullYear(),
        now.getMonth(),
        now.getDate(),
        0,0,0),
    diff = now.getTime() - then.getTime()

    var today = Date.parse(now)-diff;
 
    console.log(today);
    if(calDate>=today){
      return true;
    }
    return false;
  }

  $scope.saveCorrectiveAction = function(def){
    def.CorrectiveActionCopy.isDirty = true;

    //if this is a new corrective action (we are not editing one), we set it's class and Deficiency_selection_id properties
    if(!def.CorrectiveActionCopy.Deficiency_selection_id)def.CorrectiveActionCopy.Deficiency_selection_id = def.Key_id;
    if(!def.CorrectiveActionCopy.Class)def.CorrectiveActionCopy.Class = "CorrectiveAction";

    //parse the dates for MYSQL
    if(def.CorrectiveActionCopy.viewCompletion_date)def.CorrectiveActionCopy = postInspectionFactory.setDatesForServer(def.CorrectiveActionCopy,"viewCompletion_date");
    if(def.CorrectiveActionCopy.viewPromised_date)def.CorrectiveActionCopy = postInspectionFactory.setDatesForServer(def.CorrectiveActionCopy,"viewPromised_date");
    console.log(def.CorrectiveActionCopy);

    var test = postInspectionFactory.saveCorrectiveAction(def.CorrectiveActionCopy).then(
      function(promise){

        if(promise.Completion_date){
           promise = postInspectionFactory.setDateForView(promise,"Completion_date");
        }

        if(promise.Promised_date){
          promise = postInspectionFactory.setDateForView(promise,"Promised_date");
        }

        def.CorrectiveActionCopy.isDirty = false;
        def.CorrectiveActionCopy = angular.copy(promise);
        def.CorrectiveActions[0] = angular.copy(promise);
        postInspectionFactory.setInspection($scope.inspection);
      },
      function(promise){
        def.error = 'There was a promblem saving the Corrective Action';
        def.CorrectiveActionCopy.isDirty = false;
      }
    );
  }

  $scope.setCorrectiveActionCopy = function(def){
    def.CorrectiveActionCopy = angular.copy(def.CorrectiveActions[0]);
  }

  $scope.setViewDate = function( date ){
    if(!date)return convenienceMethods.getDate(convenienceMethods.setMysqlTime(Date())).formattedString;
    console.log(date);
    return convenienceMethods.getDate(date).formattedString;
  }

  function answerIsNotNo(answer){
      if(answer!=no)return true;
      return false;
  }


}