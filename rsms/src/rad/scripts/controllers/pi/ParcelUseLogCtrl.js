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
                          $scope.pi = dataStoreManager.getById('PrincipalInvestigator', $stateParams.pi);
                          var i = $scope.pi.ActiveParcels.length;
                          while(i--){
                              var parcel = dataStoreManager.getById("Parcel",$scope.pi.ActiveParcels.Key_id);
                              if(parcel)parcel.Authorization = $scope.pi.ActiveParcels.Authorization;
                              console.log(parcel);
                          }

                          return $scope.pi;
                    },
                    function(){}
                )
      }

      var getParcel = function(){
        console.log(dataStore);
        af.getParcelById($stateParams.parcel)
            .then(
                function(){
                    $scope.parcel = dataStoreManager.getById("Parcel",$stateParams.parcel);
                    $scope.parcel.loadUses();
                    console.log($scope.parcel);
                }
            );
      }

      $scope.parcelPromise = getPi()
                              .then(getParcel);

      $scope.addUsage = function(parcel){
          if(!$scope.parcel.ParcelUses)$scope.parcel.ParcelUses = [];
          var i = $scope.parcel.ParcelUses.length;
          while(i--){
            $scope.parcel.ParcelUses[i].edit=false
          }
          $rootScope.ParcelUseCopy = {};
          $rootScope.ParcelUseCopy = new window.ParcelUse();
          $rootScope.ParcelUseCopy.Parcel_id = $scope.parcel.Key_id;
          $rootScope.ParcelUseCopy.ParcelUseAmounts = [];
          $rootScope.ParcelUseCopy.edit = true;
          $rootScope.ParcelUseCopy.Class = "ParcelUse";
          var solidUsageAmount = new window.ParcelUseAmount();
          var liquidUsageAmount = new window.ParcelUseAmount();
          var vialUsageAmount = new window.ParcelUseAmount();
          var otherUsageAmount = new window.ParcelUseAmount();

          $rootScope.ParcelUseCopy.isNew = true;
          solidUsageAmount.Waste_type_id = 4;
          liquidUsageAmount.Waste_type_id = 1;
          vialUsageAmount.Waste_type_id = 3;
          otherUsageAmount.Waste_type_id = 5;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(otherUsageAmount);

          parcel.ParcelUses.unshift($rootScope.ParcelUseCopy);
      }

      $scope.editUse = function(use){
          var i = $scope.parcel.ParcelUses.length;
          while(i--){
            $scope.parcel.ParcelUses[i].edit=false
          }
          $rootScope.ParcelUseCopy = {}

          af.createCopy(use);

          if(!parcelUseHasUseAmountType($rootScope.ParcelUseCopy,4)){
            var solidUsageAmount = new window.ParcelUseAmount();
            solidUsageAmount.Waste_type_id = 4;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          }
          if(!parcelUseHasUseAmountType($rootScope.ParcelUseCopy,1)){
            var liquidUsageAmount = new window.ParcelUseAmount();
            liquidUsageAmount.Waste_type_id = 1;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          }
          if(!parcelUseHasUseAmountType($rootScope.ParcelUseCopy,3)){
            var vialUsageAmount = new window.ParcelUseAmount();
            vialUsageAmount.Waste_type_id = 3;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          }
          if(!parcelUseHasUseAmountType($rootScope.ParcelUseCopy,5)){
            var otherUsageAmount = new window.ParcelUseAmount();
            otherUsageAmount.Waste_type_id = 5;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(otherUsageAmount);
            console.log(otherUsageAmount)
          }

          use.edit = true;
      }

      $scope.addAmount = function(type){
          console.log(type);
          var amt = new window.ParcelUseAmount();
          if(type == "Solids")amt.Waste_type_id = 4;
          if(type == "Liquids")amt.Waste_type_id = 1;
          if(type == "Vials")amt.Waste_type_id = 3;
          if(type == "Others")amt.Waste_type_id = 5;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(amt);
          $rootScope.ParcelUseCopy[type].push(amt);
      }

      $scope.selectCarboy = function(useAmount){
          if( !useAmount.Carboy ){
            useAmount.Carboy_id = null;
          }else{
            useAmount.Carboy_id = useAmount.Carboy.Key_id;
          }
      }

      $scope.selectContainer = function(useAmount){
          if(!useAmount.Waste_bag){
            useAmount.Waste_bag_id = null;
          }else{
            useAmount.Waste_bag_id = useAmount.Waste_bag.Key_id;
          }
      }
      $scope.selectedBag = function(solid, bags){
          var i = bags.length;
          while(i--){
            if(bags[i].Key_id == solid.Waste_bag_id)solid.Waste_bag = bags[i];
          }
      }
      $scope.cancel = function(use){
          if($rootScope.ParcelUseCopy.isNew == true)$scope.parcel.ParcelUses.shift();
          use.edit = false;
          $rootScope.ParcelUseCopy = {};
      }

      $scope.validateRemainder = function(parcel) {
          var uses = parcel.ParcelUses;
          parcel.inValid = false;
          var total = 0;
          var i = uses.length;
          while(i--){
            console.log(uses[i].Quantity);
            total += parseInt(uses[i].Quantity);
          }

          total += parseInt($rootScope.ParcelUseCopy.Quantity);
          //if we are editing, subtract the total from the copied use so that it's total isn't included twice
          if($rootScope.ParcelUseCopy.Key_id){
            total = total - parseInt(dataStoreManager.getById("ParcelUse", $rootScope.ParcelUseCopy.Key_id).Quantity);
          }

          if(total > parcel.Quantity){
            parcel.inValid = true;
            $rootScope.error = 'Total usages must not be more than package quantity.';
          }

      }

      //this is here specifically because form validation seems like it belongs in the controller (VM) layer rather than the CONTROLLER(actionFunctions layer) of this application,
      //which if you think about it, has sort of become an MVCVM
      $scope.validateUseAmounts = function(use){
          $rootScope.error = '';
          use.isValid = false;
          var total = 0;

          var i = use.ParcelUseAmounts.length;
          while(i--){
            if(use.ParcelUseAmounts[i].Curie_level)total = total + parseInt(use.ParcelUseAmounts[i].Curie_level);
          }

          if(use.Quantity == total){
            use.isValid = true;
          }else{
            $rootScope.error = 'Total disposal amount must equal use amount.';
          }
      }

      var parcelUseHasUseAmountType = function(use, typeId){
          var i = use.ParcelUseAmounts.length;
          while(i--){
            var amt = use.ParcelUseAmounts[i];
            if(amt.Waste_type_id == typeId)return true;
          }
          return false;
      }
 });
