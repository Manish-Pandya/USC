var roleBased = angular.module('roleBased', ['ui.bootstrap','convenienceMethodWithRoleBasedModule'])
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
        factory.roles = GLOBAL_SESSION_ROLES;

        factory.sumArray = function(array){
            console.log(array);
            var i = array.length;
            var total = 0;
            while(i--){
                console.log(array[i]);
                if(typeof array[i] != "number")return;
                total += array[i];
            }
            return total;
        }

        var tempRoles = [2,4,8];
        factory.getHasPermission = function( elementRoles ){
            return factory.sumArray(elementRoles) & factory.sumArray(tempRoles);
        }

        return factory;
    })

