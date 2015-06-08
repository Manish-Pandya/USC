'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('QuarterlyInventoryCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {

  		var af = actionFunctionsFactory;
  		$scope.af = af;

  		var getPi = function(){
        return af.getRadPIById($stateParams.pi)
    			.then(
    				function(pi){
              console.log(pi);
              $scope.pi = pi;
              return pi;
    				},
    				function(){}
    			)
      }

      var getInventory = function(pi){
        console.log(pi);
        return af.getQuartleryInventory(pi.Key_id)
                  .then(
                    function(inventory){
                      $scope.pi_inventory = inventory;
                    }
                  )
      }

      $rootScope.inventoryPromise = getPi()
                              .then(getInventory);
 });
