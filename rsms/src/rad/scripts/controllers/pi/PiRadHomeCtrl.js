'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PiRadHomeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.piPromise = af.getRadPIById($stateParams.pi)
  			.then(
  				function(pi){
                    console.log(pi);
  					$scope.pi = pi;
  				},
  				function(){}
  			)

  });