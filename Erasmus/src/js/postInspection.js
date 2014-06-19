angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule','ngQuickDate','ngRoute','once'])

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
  /*
  .when('/details', 
    {
      templateUrl: 'post-inspection-templates/inspectionDetails.html', 
      controller: inspectionDetailsController 
    }
  )
*/
  .otherwise(
    {redirectTo: '/report'}
  );
})

.factory('postInspectionFactory', function(convenienceMethods,$q){
  var factory = {};
  var inspection = {};
  factory.getInspectionData = function(url){
    //return convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById3&id=132&callback=JSON_CALLBACK', this.onFailGet);
  };

  factory.getInspection = function(){
    return this.inspection;
  };

  factory.updateInspection = function(){
    //this should call convenienceMethods call to update an object on the server
  };

  factory.setInspection = function(data){
    return this.inspection = data;
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
    //object with array properties to contain the checklists
    checklistHolder = {};
    checklistHolder.biologicalHazards = [];
    checklistHolder.chemicalHazards = [];
    checklistHolder.radiationHazards = [];
    checklistHolder.generalHazards = [];

    //group the checklists by parent hazard
    //get the questions for each checklist and store them in a property that the view can access easily
    for(i=0;i<checklists.length;i++){
      var checklist = checklists[i];

      if(checklist.Master_hazard.toLowerCase().indexOf('biological') > -1){
        if(!checklistHolder.biologicalHazards.Questions)checklistHolder.biologicalHazards.Questions = [];
        checklistHolder.biologicalHazards.push(checklist);
        checklistHolder.biologicalHazards.Questions = checklistHolder.biologicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('chemical') > -1){
        if(!checklistHolder.chemicalHazards.Questions)checklistHolder.chemicalHazards.Questions = [];
        checklistHolder.chemicalHazards.push(checklist);
        checklistHolder.chemicalHazards.Questions = checklistHolder.chemicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('radiation') > -1){
        if(!checklistHolder.radiationHazards.Questions)checklistHolder.radiationHazards.Questions = [];
        checklistHolder.radiationHazards.push(checklist);
        checklistHolder.radiationHazards.Questions = checklistHolder.radiationHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('general') > -1){
        if(!checklistHolder.generalHazards.Questions)checklistHolder.generalHazards.Questions = [];
        checklistHolder.generalHazards.push(checklist);
        checklistHolder.generalHazards.Questions = checklistHolder.generalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
    }
    return checklistHolder;
  };

  factory.getQuestionsByChecklist = function(checklist){
    return checklist.Questions;
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
    if(inspection.compliantItems && inspection.itemsInspected){
      //we have both numbers, so we can calculate a score
      inspection.score.score = Math.round(parseInt(inspection.compliantItems)/parseInt(inspection.itemsInspected) * 100);
    }else{
      //since 0 is undefined, we se this property to the String "0"
      inspection.score.score = '0';
    }
    return this.inspection = inspection;
  }

  return factory;
});

mainController = function($scope, $location, postInspectionFactory,convenienceMethods){

  $scope.setRoute = function(route){
    $location.path(route);
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

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods,postInspectionFactory){
  if($location.search().inspection){
    var id = $location.search().inspection;

    if(!postInspectionFactory.getInspection()){

      $scope.doneLoading = false;
      convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id='+id+'&callback=JSON_CALLBACK', onFailGetInspeciton)
        .then(function(promise){
          $scope.inspection = promise.data;

          //set view init values for email
          $scope.others = [{email:''}];
          $scope.defaultNote = "These are your results from the recent inspection of your laboratory.";

          $scope.doneLoading = true;
          // call the manager's setter to store the inspection in the local model
          postInspectionFactory.setInspection($scope.inspection);
          $scope.doneLoading = true;
        });
    }else{
      //set view init values for email
      $scope.others = [{email:''}];
      $scope.defaultNote = "These are your results from the recent inspection of your laboratory.";
      $scope.inspection = postInspectionFactory.getInspection();
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
      Text: $scope.defaultNote
    }

    var url = '../../ajaxaction.php?action=sendInspectionEmail';
    convenienceMethods.sendEmail(emailDto, onSendEmail, onFailSendEmail, url);
    $scope.sending = true;
  }

  function onSendEmail(data){
    $scope.sending = false;
    $scope.emailSent = 'success';
    
    console.log($rootScope.Inspection);
    evaluateCloseInspection();

  }

  function onFailSendEmail(){
    $scope.sending = false;
    $scope.emailSent = 'error';
    alert('There was a problem when the system tried to send the email.');
  }


  function evaluateCloseInspection(){
    var setCompletedDate  = true;
    $rootScope.Checklists = angular.copy($rootScope.Inspection.Checklists);
    angular.forEach($rootScope.Checklists, function(checklist, key){
        angular.forEach(checklist.Questions, function(question, key){
          if(question.Responses && question.Responses.DeficiencySelections){
            angular.forEach(question.Responses.DeficiencySelections, function(defSel, key){
              console.log('here');
              if(!defSel.Corrected_in_inspection)setCompletedDate = false;
            });
          }
        });
    });
    if(setCompletedDate)setInspectionClosed();
  }

  function setInspectionClosed(){
    var inspectionDto = angular.copy($rootScope.Inspection);
    inspectionDto.date_closed = new Date();
    console.log(inspectionDto);
    var url = "../../ajaxaction.php?action=saveInspection";
    convenienceMethods.updateObject( inspectionDto, null, onSetInspectionClosed, onFailSetInspecitonClosed, url);
  }

  function onSetInspectionClosed(data){
    console.log('saved')
    data.Checklists = angular.copy($rootScope.Checklists);
    $rootScope.Inspection = data;
    $scope.inspection = data;
  }

  function onFailSetInspecitonClosed(){
    alert("There was an issue when the system tried to set the Inpsection's closeout date");
  }

}

inspectionReviewController = function($scope, $location, convenienceMethods, postInspectionFactory){
  
  function init(){
    if($location.search().inspection){
      var id = $location.search().inspection;
      if(!postInspectionFactory.getInspection()){
        $scope.doneLoading = false;
        convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id='+id+'&callback=JSON_CALLBACK', onFailGetInspeciton)
          .then(function(promise){

            //set the inspection date as a javascript date object
            if(promise.data.Date_started)promise.data = postInspectionFactory.setDateForView(promise.data,"Date_started");
            $scope.inspection = promise.data;
            $scope.inspection = postInspectionFactory.calculateScore($scope.inspection);
            $scope.doneLoading = true;
            // call the manager's setter to store the inspection in the local model
            postInspectionFactory.setInspection($scope.inspection);
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

  $scope.setViewDate = function(date){
    console.log(date);
    return convenienceMethods.getDate(date).formattedString;
  }
}