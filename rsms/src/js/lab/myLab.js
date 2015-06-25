var myLab = angular.module('myLab', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodWithRoleBasedModule','once','cgBusy'])

.factory('myLabFactory', function(convenienceMethods,$q,$rootScope){

        var factory = {};

        factory.archivedInspections = [];
        factory.openInspections = [];
        factory.previousInspections = [];
        factory.user = {};

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
              deferred.resolve(factory.openInspections);
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

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getArchivedInspectionsByPIId&piId="+id;
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

        factory.getCurrentUser = function(id){
            var deferred = $q.defer();

            if(factory.user.length){
                deferred.resolve( factory.user );
                return deferred.promise;
            }
            var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getCurrentUser";
            convenienceMethods.getDataAsDeferredPromise(url).then(
                function(promise){
                  factory.user = promise;
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
    var mlf = myLabFactory
    $scope.mlf = mlf;

    //GLOBAL_SESSION_USER

    console.log(roleBasedFactory);

    var getUser = function(){
        return mlf.getCurrentUser()
        .then(
            function(user){
                console.log(user);
                $scope.user = user;
                //if this user is a PI, we get their own PI record.  If not, we get their supervisor's record
                if(user.Principal_investigator_id){
                    return user.Principal_investigator_id;
                }else{
                    return user.Supervisor_id;
                }
            }
        )
    }

    var getOpenInspections = function(id){
       return  mlf.getOpenInspections(id)
            .then(
                function(openInspections){
                    console.log(openInspections);
                    $scope.openInspections = openInspections;
                    return id;
                }
            )
    }

    var getPreviousInspections = function(id){
        return mlf.getPreviousInspections(id)
            .then(
                function(previousInspections){
                    $scope.previousInspections = previousInspections;
                }
            )
    }

    //init call
    $scope.inspectionPromise = getUser()
                                .then(getOpenInspections)
                                .then(getPreviousInspections);

}
