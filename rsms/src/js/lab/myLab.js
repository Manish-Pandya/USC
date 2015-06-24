var myLab = angular.module('myLab', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodWithRoleBasedModule','once'])

.factory('myLabFactory', function(convenienceMethods,$q,$rootScope){

        var factory = {};

        factory.archivedInspections = [];
        factory.openInspections = [];

        factory.getOpenInspections = function(id)
        {
          var deferred = $q.defer();

          if(factory.openInspections.length){
            deferred.resolve( factory.openInspections );
            return deferred.promise;
          }

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getOpenInspectionsByPIId&id="+id;
          convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              factory.openInspections = promise;
            },
            function(promise){
              deferred.reject();
            }
          );
          return deferred.promise
        }

        factory.getPreviousInspections = function(id)
        {
          var deferred = $q.defer();

          if(factory.previousInspections.length){
            deferred.resolve( factory.previousInspections );
            return deferred.promise;
          }

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getInspectionsByPIId&piId="+id;
          convenienceMethods.getDataAsDeferredPromise(url).then(
            function(promise){
              factory.previousInspections = promise;
              deferred.resolve(promise);
            },
            function(promise){
              deferred.reject();
            }
          );
          return deferred.promise
        }



        return factory;
});

function myLabController($scope, $rootScope, convenienceMethods, myLabFactory, roleBasedFactory) {
    var mlf =myLabFactory
    $scope.mlf = mlf;

    console.log(roleBasedFactory);

}
