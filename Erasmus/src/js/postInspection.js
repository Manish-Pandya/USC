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

      if(checklist.Master_hazard.toLowerCase().indexOf('biolog')){
        if(!checklistHolder.biologicalHazards.Questions)checklistHolder.biologicalHazards.Questions = [];
        checklistHolder.biologicalHazards.push(checklist);
        checklistHolder.biologicalHazards.Questions = checklistHolder.biologicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('chemical')){
        if(!checklistHolder.chemicalHazards.Questions)checklistHolder.chemicalHazards.Questions = [];
        checklistHolder.chemicalHazards.push(checklist);
        checklistHolder.chemicalHazards.Questions = checklistHolder.chemicalHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('radia')){
        if(!checklistHolder.radiationHazards.Questions)checklistHolder.radiationHazards.Questions = [];
        checklistHolder.radiationHazards.push(checklist);
        checklistHolder.radiationHazards.Questions = checklistHolder.radiationHazards.Questions.concat(this.getQuestionsByChecklist(checklist));
      }
      else if(checklist.Master_hazard.toLowerCase().indexOf('gener')){
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
        $scope.Inspection = postInspectionFactory.getInspection();
      });
  }else{
    $scope.Inspection = postInspectionFactory.getInspection();
  }
  */
}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods,postInspectionFactory){
  if(!postInspectionFactory.getInspection()){
    $scope.doneLoading = false;
    convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=132&callback=JSON_CALLBACK')
      .then(function(promise){
        $scope.inspection = promise.data;
        $scope.doneLoading = true;
        // call the manager's setter to store the inspection in the local model
        postInspectionFactory.setInspection($scope.inspection);
        $scope.doneLoading = true;
      });
  }else{
    $scope.Inspection = postInspectionFactory.getInspection();
  }
}

inspectionReviewController = function($scope, $location, convenienceMethods, postInspectionFactory){
  
  function init(){
    if(!postInspectionFactory.getInspection()){
      $scope.doneLoading = false;
      convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=132&callback=JSON_CALLBACK', onFailGetInspeciton)
        .then(function(promise){
          //set the inspection date as a javascript date object
          if(promise.data.Date_started)promise.data = postInspectionFactory.setDateForView(promise.data,"Date_started");
          $scope.inspection = promise.data;
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
      $scope.Inspection = postInspectionFactory.getInspection();
      if(!$scope.inspection.length) $scope.error="There was a problem when trying to get the inpseciton.  Check your internet connection."
      $scope.doneLoading = true;
    }
    $scope.options = ['Incomplete','Pending','Complete'];
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