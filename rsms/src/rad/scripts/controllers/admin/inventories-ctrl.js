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
        /*
        console.log($state);
        $scope.pi_inventory = dataStoreManager.getById("PIQuarterlyInventory", $state.params.pi_inventory);
        console.log($scope.pi_inventory);
        */
        af.getQuartleryInventory(1)
          .then(
            function(){
              $scope.pi_inventory = dataStoreManager.getById("PIQuarterlyInventory",1);
            }
          )
      }
      
      $scope.getAllPIs = af.getAllPIs()
            .then(
                function( pis ){
                    $scope.PIs = pis;
                    return;
                },
                function(){
                    $scope.error = "Couldn't get the PIs";
                    return false;
                }

            );
          
      $scope.af = af;
    
      $scope.inventoryPromise = af.getMostRecentInventory()
          .then(
              function(inventory){
                  $scope.inventory = inventory;
                  console.log(inventory);
              },
              function(){}
          )

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

      $scope.createInventory = function(endDate, dueDate){
        af.createQuarterlyInventory(endDate, dueDate)
          .then(
            function(inventory){
              $scope.inventory = inventory;
              console.log(inventory);
            },
            function(){}
          );
      }

  });
