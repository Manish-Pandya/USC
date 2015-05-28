'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InventoriesCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.piPromise = af.getAllQuarterlyInventories()
  			.then(
  				function(inventories){
  					$scope.inventories = dataStore.QuarterlyInventory;
  				},
  				function(){}
  			)

  });