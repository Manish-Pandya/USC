var roleBased = angular.module('roleBased', ['ui.bootstrap','convenienceMethodModule'])
    .directive('uiRoles', ['roleBasedFactory', function(roleBasedFactory) {
    return {
        restrict: 'A',
        link: function(scope, elem, attrs, test) {
           console.log(scope);
           console.log(elem);
           console.log(test);
        }
     }
    }])

    .factory('roleBasedFactory', function(convenienceMethods, $q, $rootScope, $http){
        var factory = {};
        factory.roles = [];

        factory.getCurrentRoles = function()
        {
            var deferred = $q.defer();
            //lazy load
            if(factory.roles.length){

                deferred.resolve(factory.roles);
                //return deferred.promise;
            }

            var url = '../ajaxaction.php?action=getCurrentUserRoles';
            $http.get(url)
                .success( function(data) {
                    console.log(data);
                    deferred.resolve(data);
                })
                .error(function(data, status, headers, config){
                });

            return deferred.promise;
        }

        factory.setRole = function(role){
            factory.roles = [role];
        }

        return factory;
    })

