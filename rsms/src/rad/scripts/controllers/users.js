'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:UserCtrl
 * @description
 * # MainCtrl
 * Controller of the 00RsmsAngularOrmApp
 */
angular.module('00RsmsAngularOrmApp')
  .controller('UserCtrl', function ($scope, $q, $http, actionFunctionsFactory) {
    //do we have access to action functions?
    $scope.af = actionFunctionsFactory;

    var getAllUsers = function()
    {
       return actionFunctionsFactory.getAllUsers()
                .then(
                    function( users ){
                        return users;
                    },
                    function(){
                        $scope.error = "Couldn't get the users";
                        return false;
                    }

                );
    },

    getAllPIs = function()
    {
         return actionFunctionsFactory.getAllPIs()
            .then(
                function( pis ){
                    return;
                },
                function(){
                    $scope.error = "Couldn't get the PIs";
                    return false;
                }

            ); 
    },

    exposeUsers = function( users )
    {
        $scope.users = users;
    }


    $scope.UsersLoading = getAllUsers()
    	.then( exposeUsers )
    	//.then( getAllPIs );

  });
