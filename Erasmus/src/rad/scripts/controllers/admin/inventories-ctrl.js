'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InventoriesCtrl', function ($scope, actionFunctionsFactory, $rootScope, $state) {

  		var af = actionFunctionsFactory;
      var getInventory = function(){
        console.log($state);
        $scope.pi_inventory = dataStoreManager.getById("PIQuarterlyInventory", $state.params.pi_inventory);
        console.log($scope.pi_inventory);
      }
  		$scope.af = af;
  		$scope.inventoryPromise = af.getMostRecentInventory()
  			.then(
  				function(inventory){
  					$scope.inventory = inventory;
  				},
  				function(){}
  			)

      console.log($state.current);
      if($state.current.name == 'radmin-quarterly-inventory'){
        getInventory();
      }

      $scope.getInventoriesByPiId = function(id){
          $scope.piInventoriesPromise = af.getInventoriesByPiId(id)
            .then(
              function(piInventories){
                  console.log(piInventories);
                  $scope.piInventories = piInventories; 
              }
            )
      }


  });