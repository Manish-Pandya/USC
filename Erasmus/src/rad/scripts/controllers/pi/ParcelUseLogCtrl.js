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
    				function(){
              $scope.pi = af.getById('PrincipalInvestigator',$stateParams.pi);
              $scope.pi.loadWasteBags();
              return $scope.pi;
    				},
    				function(){}
    			)
      }

      var getParcel = function(pi){
        $scope.parcel = af.getById('Parcel',$stateParams.parcel);
        console.log($scope.parcel);
      }

      $scope.parcelPromise = getPi()
                              .then(getParcel);

      $scope.addUsage = function(parcel){
          $rootScope.ParcelUseCopy = new window.ParcelUse();
          $rootScope.ParcelUseCopy.Parcel_id = $scope.parcel.Key_id;
          $rootScope.ParcelUseCopy.ParcelUseAmounts = [];
          $rootScope.ParcelUseCopy.edit = true;
          var solidUsageAmount = new window.ParcelUseAmount();
          var liquidUsageAmount = new window.ParcelUseAmount();
          var vialUsageAmount = new window.ParcelUseAmount();

          solidUsageAmount.Waste_type_id = 4;
          liquidUsageAmount.Waste_type_id = 1;
          vialUsageAmount.Waste_type_id = 3;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          parcel.ParcelUses.unshift($rootScope.ParcelUseCopy);
      }

      $scope.editUse = function(use){
          af.createCopy(use);
          if(!$rootScope.ParcelUseCopy.Solids.length){
            var solidUsageAmount = new window.ParcelUseAmount();
            solidUsageAmount.Waste_type_id = 4;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
            $rootScope.ParcelUseCopy.Solids.push(solidUsageAmount);
          }
          if(!$rootScope.ParcelUseCopy.Liquids.length){
            var liquidUsageAmount = new window.ParcelUseAmount();
            liquidUsageAmount.Waste_type_id = 1;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
            $rootScope.ParcelUseCopy.Liquids.push(liquidUsageAmount);
          }
          if(!$rootScope.ParcelUseCopy.Vials.length){
            var vialUsageAmount = new window.ParcelUseAmount();
            vialUsageAmount.Waste_type_id = 3;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
            $rootScope.ParcelUseCopy.Vials.push(vialUsageAmount);
          }
          console.log($rootScope.ParcelUseCopy);

          use.edit = true;
      }

      $scope.addAmount = function(type){
          console.log(type);
          var amt = new window.ParcelUseAmount();
          if(type == "Solids")amt.Waste_type_id = 1;
          if(type == "Liquids")amt.Waste_type_id = 1;
          if(type == "Vials")amt.Waste_type_id = 1;
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(amt);
          $rootScope.ParcelUseCopy[type].push(amt);
      }
      
      $scope.selectCarboy = function(useAmount){
          useAmount.Carboy_id = useAmount.Carboy.Key_id;
      }

      $scope.selectContainer = function(useAmount){
          useAmount.Waste_bag_id = useAmount.Waste_bag.Key_id;
      }
      $scope.selectedBag = function(solid, bags){
          console.log(solid);
          var i = bags.length;
          while(i--){
            if(bags[i].Key_id == solid.Waste_bag_id)solid.Waste_bag = bags[i];
          }
      }
      $scope.cancel = function(use){
          use.edit = false;
          $rootScope.ParcelUseCopy = {};
      }

      $scope.saveUse = function(use){
          var copy = $rootScope.ParcelUseCopy
          var useDTO = {
            Parcel_id: copy.Parcel_id,
            //Date_of_use: 

          }
          /*private $quantity;

  /** Reference to the Isotope entity this usage concerns 

Date_of_use
Experiment_use
Date_used
ParcelUseAmounts
Quantity
  Curie_level
  Waste_type_id
  Carboy_id
  Waste_bag_id
  Parcel_use_id
  */

           af.saveParcelUse()
            .then(
              function(){

              }

            )
      }
 });
