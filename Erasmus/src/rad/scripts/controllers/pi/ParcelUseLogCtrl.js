'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('ParcelUseLogCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {

  		var af = actionFunctionsFactory;
  		$scope.af = af;

  		var getPi = function(){
        return af.getRadPIById($stateParams.pi)
    			.then(
    				function(pi){
              return pi;
    				},
    				function(){}
    			)
      }

      var getParcel = function(pi){
        var parcel = af.getById('Parcel',$stateParams.parcel);
        return af.getParcelUses(parcel)
          .then(
            function(){
              $scope.parcel = parcel;
              $scope.pi = pi;
            }
          );
      }

      $scope.parcelPromise = getPi()
                              .then(getParcel);

      $scope.addUsage = function(parcel){
          var use = new window.ParcelUse();
          use.Parcel_id = $scope.parcel.Key_id;
          use.ParcelUseAmounts = [];
          use.edit = true;
          var solidUsageAmount = new window.ParcelUseAmount();
          var liquidUsageAmount = new window.ParcelUseAmount();
          var vialUsageAmount = new window.ParcelUseAmount();

          solidUsageAmount.Waste_type_id = 4;
          liquidUsageAmount.Waste_type_id = 1;
          vialUsageAmount.Waste_type_id = 3;

          use.ParcelUseAmounts.push(solidUsageAmount);
          use.ParcelUseAmounts.push(liquidUsageAmount);
          use.ParcelUseAmounts.push(vialUsageAmount);
          parcel.ParcelUses.unshift(use);
      }

      $scope.editUse = function(use){
          console.log(dataStoreManager.get("Carboy"));
          console.log(use);
          if(!use.Solids.length){
            var solidUsageAmount = new window.ParcelUseAmount();
            solidUsageAmount.Waste_type_id = 4;
            use.ParcelUseAmounts.push(solidUsageAmount);
          }
          if(!use.Liquids.length){
            var liquidUsageAmount = new window.ParcelUseAmount();
            liquidUsageAmount.Waste_type_id = 1;
            use.ParcelUseAmounts.push(liquidUsageAmount);
          }
          if(!use.Vials.length){
            var vialUsageAmount = new window.ParcelUseAmount();
            vialUsageAmount.Waste_type_id = 3;
            use.ParcelUseAmounts.push(vialUsageAmount);
          }

          use.edit = true;
      }
      
      $scope.selectCarboy = function(useAmount){
          useAmount.Carboy_id = useAmount.Carboy.Key_id;
      }
 });
