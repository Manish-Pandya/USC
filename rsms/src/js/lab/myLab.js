var myLab = angular.module('myLab', [
  'ui.bootstrap',
  'shoppinpal.mobile-menu',
  'convenienceMethodWithRoleBasedModule',
  'once',
  'cgBusy',
  'angular.filter'])
.filter('openInspections', function () {
  return function (inspections) {
        if(!inspections)return;
        var i = inspections.length;
        var matches = [];
        while(i--){
            if(!inspections[i].Cap_submitted_date && !inspections[i].Date_closed)matches.push(inspections[i]);
        }
        return matches;
  };
})
.filter('closedInspections', function () {
  return function (inspections) {
        if(!inspections)return;
        var i = inspections.length;
        var matches = [];
        while(i--){
            if(inspections[i].Cap_submitted_date || inspections[i].Date_closed)matches.push(inspections[i]);
        }
        return matches;
  };
})
.factory('myLabFactory', function(convenienceMethods,$q,$rootScope){

        var factory = {};

        factory.archivedInspections = [];
        factory.user;
        factory.pi;

        factory.getMyLabWidgets = function(){
          var deferred = $q.defer();

          if(factory.MyLabWidgets != null){
            deferred.resolve( factory.MyLabWidgets );
            return deferred.promise;
          }

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getMyLabWidgets";
          convenienceMethods.getDataAsDeferredPromise(url).then(
            function(MyLabWidgets){
              factory.MyLabWidgets = MyLabWidgets;
              deferred.resolve(MyLabWidgets);
            },
            function(MyLabWidgets){
              deferred.reject();
            }
          );
          return deferred.promise
        };

        return factory;
});

function myLabController($scope, $rootScope, convenienceMethods, myLabFactory, roleBasedFactory, $q) {
    var mlf = myLabFactory
    $scope.mlf = mlf;

    var getWidgets = function(){
      return  mlf.getMyLabWidgets()
      .then(
          function(MyLabWidgets){
              $scope.MyLabWidgets = MyLabWidgets;
          }
      )
    }

    //init call
    $scope.inspectionPromise = getWidgets();
}
