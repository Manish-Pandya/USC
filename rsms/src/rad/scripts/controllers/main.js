'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the 00RsmsAngularOrmApp
 */
angular.module('00RsmsAngularOrmApp')
  .controller('MainCtrl', function ( $scope, actionFunctionsFactory ) {

    actionFunctionsFactory.getUserById( 2 )
    	.then( 
    		function( user ){
    			$scope.user = user;
    		}
    	);

    actionFunctionsFactory.getAllUsers()
    	.then( 
    		function( users ){
    			$scope.users = users;
    		}
    	);

  });
