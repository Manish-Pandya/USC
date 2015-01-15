'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
  .controller('RadminMainCtrl', function ($scope, $q, $http, actionFunctionsFactory) {
    //do we have access to action functions?
    $scope.af = actionFunctionsFactory;


    //get the root hazard node
    $scope.piPromise = actionFunctionsFactory.getAllPIs()
    	.then(
            function( pis ){
                $scope.pis = pis;
            },
            function(){
                $scope.error = 'There was an error when the system tried to get the list of Principal Investigators.  Please check your internet connection and try again.'
            }

        );

    $scope.name = function(hazard){
        return parseFloat(hazard.Name);
    }

  });
