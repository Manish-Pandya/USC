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

      $scope.openModal = function(object){
        console.log(object);
        var modalData = {};
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/pi/pi-modals/confirm-inventory.html',
          controller: 'InventoryConfirmationModalCtrl'
        });
      }

      $rootScope.inventoryPromise = getPi()
                              .then(getInventory);
 })
 .controller('InventoryConfirmationModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        
        $scope.savePiQuarterlyInventory = function(inventory, copy){
            af.savePiQuarterlyInventory(inventory, copy)
                .then($scope.close);
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
;
