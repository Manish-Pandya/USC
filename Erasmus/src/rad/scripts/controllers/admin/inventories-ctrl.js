'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InventoriesCtrl', function ($scope, actionFunctionsFactory, $rootScope) {

  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$scope.inventoryPromise = af.getMostRecentInventory()
  			.then(
  				function(inventory){
  					$scope.inventory = inventory;
  				},
  				function(){}
  			)

      $scope.getInventoriesByPiId = function(id){
          alert(id);
          $scope.piInventoriesPromise = af.getInventoriesByPiId(id)
            .then(
              function(piInventories){
                  $scope.piInventories = piInventories; 
              }
            )
      }
  });