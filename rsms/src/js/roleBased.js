var roleBased = angular.module('roleBased', ['ui.bootstrap'])
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

    .factory('roleBasedFactory', function( $q, $rootScope ){
        var factory = {};
        factory.roles = {};
        factory.U;
        //expose this factory to all views
        $rootScope.rbf = factory;

        //store the current user's permissions as an int
        factory.userPermissions = GLOBAL_SESSION_ROLES["userPermissions"];

        factory.getRoles = function(){
            if(factory.roles.length != 0){
                var i = GLOBAL_SESSION_ROLES["allRoles"].length;
                while(i--){
                    for(var prop in GLOBAL_SESSION_ROLES["allRoles"][i]){
                        factory.roles[prop] = GLOBAL_SESSION_ROLES["allRoles"][i][prop];
                    }
                }
            }
            return factory.roles;
        }

        //expose an object map of all possible roles to all the views
        $rootScope.R = factory.getRoles();
        //expose the currently logged in user to the view
        $rootScope.U = GLOBAL_SESSION_USER;
        console.log(factory.U);

        factory.getUser = function () {
            if (!factory.U) {
                factory.U = GLOBAL_SESSION_USER;
            }
            return factory.U;
        }

        factory.sumArray = function(array){
            var i = array.length;
            var total = 0;
            while(i--){
                if(typeof array[i] == "object")return;
                total += parseInt(array[i]);
            }
            return total;
        }

        factory.getHasPermission = function( elementRoles ){
            return factory.sumArray(elementRoles) & factory.userPermissions;
        }

        return factory;
    })
    .controller('roleBasedCtrl', function ($scope, $rootScope) {
    });

