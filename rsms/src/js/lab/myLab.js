var myLab = angular.module('myLab', ['ui.bootstrap', 'shoppinpal.mobile-menu','convenienceMethodWithRoleBasedModule','once','cgBusy'])
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

        factory.getPI = function(id)
        {
          var deferred = $q.defer();

          if(factory.pi != null){
            console.log(factory.pi);
            deferred.resolve( factory.pi );
            return deferred.promise;
          }

          var url = "../../ajaxaction.php?&callback=JSON_CALLBACK&action=getMyLab&id="+id;
          convenienceMethods.getDataAsDeferredPromise(url).then(
            function(pi){
              factory.pi = pi;
              deferred.resolve(pi);
            },
            function(pi){
              deferred.reject();
            }
          );
          return deferred.promise
        }

        factory.getCurrentUser = function(id){
            var deferred = $q.defer();

            if(factory.user){
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
    
    console.log(roleBasedFactory);

    var getUser = function(){
        return mlf.getCurrentUser()
        .then(
            function(user){
                $scope.user = user;
                //if this user is a PI, we get their own PI record.  If not, we get their supervisor's record
                if(user.PrincipalInvestigator){
                    return user.PrincipalInvestigator.Key_id;
                }else{
                    return user.Supervisor_id;
                }
            }
        )
    }

    var getPI = function(id){
       return  mlf.getPI(id)
            .then(
                function(pi){
                    console.log(pi);
                    $scope.pi = pi;
                }
            )
    }


    //init call
    $scope.inspectionPromise = getUser()
                                .then(getPI)
}
