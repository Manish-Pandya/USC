angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule','ngQuickDate','once'])

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

.factory('postInspectionFactory', function(convenienceMethods){
  var inspection = {};

  return{
    getChecklistData: function(convenienceMethods){
        convenienceMethods.getData('../../ajaxaction.php?action=getInspectionById&id=132&callback=JSON_CALLBACK', setChecklist, onFailGet);
    },

    getChecklist: function(){
      if(inspection)return inspection;
      return this.getChecklistData;
    },

    updateChecklist: function(convenienceMethods){
      //this should call convenienceMethods call to update an object on the server
    },

    setChecklist: function(data){
      this.inspeciton = data;
    },
    onFailGet: function(){
      alert('uh oh');
    }
  }
});

mainController = function($scope, $location, convenienceMethods, $rootScope, postInspectionFactory){
  $scope.Inspection = postInspectionFactory.getChecklist;
}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods){}

inspectionReviewController = function($scope, $location, $anchorScroll, convenienceMethods, $filter, $rootScope){}