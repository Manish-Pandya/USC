angular.module('postInspections', ['ui.bootstrap', 'convenienceMethodModule','ngQuickDate','ngRoute','once'])

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

.factory('postInspectionFactory', function(convenienceMethods){
  var factory = {};
  factory.getInspectionData = function(){
    return convenienceMethods.getDataAsPromise('../../ajaxaction.php?action=getInspectionById&id=132&callback=JSON_CALLBACK', this.setChecklist, this.onFailGet);
  };

  factory.getInspection = function(){
    if(!this.inspection){
      this.getInspectionData().then(function(promise) {
        this.inspection = promise.data;
        return this.inspection;
      });
    }else{
      return this.inspection;
    }
  };

  factory.updateInspection = function(){
    //this should call convenienceMethods call to update an object on the server
  };

  factory.setInspection = function(data){
    return this.inspection = data;
  };

  factory.onFailGet = function(){
    alert('uh oh');
  };
  return factory;
});

mainController = function($scope, $location, convenienceMethods, $rootScope, postInspectionFactory){
  $scope.Inspection = postInspectionFactory.getInspection();
  console.log($scope.Inspection);
}

inspectionConfirmationController = function($scope, $location, $anchorScroll, convenienceMethods){}

inspectionReviewController = function($scope, $location, $anchorScroll, convenienceMethods, $filter, $rootScope){}