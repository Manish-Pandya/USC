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
          $scope.constants = Constants;

          var getPi = function(){
          return af.getRadPIById($stateParams.pi)
                .then(
                    function(){
                          $scope.pi = dataStoreManager.getById('PrincipalInvestigator', $stateParams.pi);
                          var i = $scope.pi.ActiveParcels.length;
                          while(i--){
                              var parcel = dataStoreManager.getById("Parcel", $scope.pi.ActiveParcels.Key_id);
                              if(parcel)parcel.Authorization = $scope.pi.ActiveParcels.Authorization;
                          }

                          return $scope.pi;
                    },
                    function(){}
                )
      }

      var getParcel = function(){
        return af.getParcelById($stateParams.parcel)
            .then(
                function(){
                    $scope.parcel = dataStoreManager.getById("Parcel",$stateParams.parcel);
                    $scope.parcel.loadUses();
                    return $scope.parcel;
                }
            );
      }

      $scope.parcelPromise = getParcel()
                              .then(getPi);

      $scope.addUsage = function (parcel) {
          parcel.edit = true;
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
          solidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.SOLID;
          liquidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
          vialUsageAmount.Waste_type_id = Constants.WASTE_TYPE.VIAL;
          otherUsageAmount.Waste_type_id = Constants.WASTE_TYPE.OTHER;

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
          
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.SOLID)) {
            var solidUsageAmount = new window.ParcelUseAmount();
            solidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.SOLID;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          }
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.LIQUID)) {
            var liquidUsageAmount = new window.ParcelUseAmount();
            liquidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          }
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.VIAL)) {
            var vialUsageAmount = new window.ParcelUseAmount();
            vialUsageAmount.Waste_type_id = Constants.WASTE_TYPE.VIAL;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          }
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.OTHER)) {
            var otherUsageAmount = new window.ParcelUseAmount();
            otherUsageAmount.Waste_type_id = Constants.WASTE_TYPE.OTHER;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(otherUsageAmount);
          }

          var i = use.ParcelUseAmounts.length;
          while (i--) {
              if (use.ParcelUseAmounts[i].Carboy_id) console.log(use.ParcelUseAmounts[i].Carboy);
              use.ParcelUseAmounts[i].OldQuantity = use.ParcelUseAmounts[i].Curie_level;
          }
         
          use.edit = true;
      }

      $scope.addAmount = function(type){
          var amt = new window.ParcelUseAmount();
          if (type == "Solids") amt.Waste_type_id = Constants.WASTE_TYPE.SOLID;
          if (type == "Liquids") amt.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
          if (type == "Vials") amt.Waste_type_id = Constants.WASTE_TYPE.VIAL;
          if (type == "Others") amt.Waste_type_id = Constants.WASTE_TYPE.OTHER;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(amt);
          $rootScope.ParcelUseCopy[type].push(amt);
      }

      $scope.selectCarboy = function (useAmount) {
          if( !useAmount.Carboy ){
            useAmount.Carboy_id = null;
          }else{
            useAmount.Carboy_id = useAmount.Carboy.Key_id;
          }
      }

      $scope.selectContainer = function (useAmount) {
          console.log(useAmount);
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
      $scope.cancel = function(use, parcel){
          if($rootScope.ParcelUseCopy.isNew == true)$scope.parcel.ParcelUses.shift();
          use.edit = false;
          use.error = false;
          $rootScope.ParcelUseCopy = {};
          
          var i = use.ParcelUseAmounts.length;
          while (i--) {
              use.ParcelUseAmounts[i].Curie_level = use.ParcelUseAmounts[i].OldQuantity;
          }
          parcel.edit = false;
      }

      $scope.validateRemainder = function (parcel, copy, use) {
          use.error = null;
          var uses = parcel.ParcelUses;
          var valid = true;
          var total = 0;
          var i = uses.length;
          while(i--){
              total += parseFloat(uses[i].Quantity);
          }

          total += parseFloat(copy.Quantity);
          //if we are editing, subtract the total from the copied use so that it's total isn't included twice
          if (use.Quantity) {
              total = total - parseFloat(use.Quantity);
          }

          if(total > parseFloat(parcel.Quantity)){
            valid = false;
            use.error = 'Total usages must not be more than remaining package quantity.';
          }
          return valid;

      }

      $scope.saveParcelUse = function (parcel, copy, use) {
          if ($scope.validateUseAmounts(copy, use) && $scope.validateRemainder(parcel, copy, use)) {
              af.saveParcelUse(parcel, copy, use);
          }
      }

      //this is here specifically because form validation seems like it belongs in the controller (VM) layer rather than the CONTROLLER(actionFunctions layer) of this application,
      //which if you think about it, has sort of become an MVCVM
      $scope.validateUseAmounts = function(use, orig){
          use.error = null;
          use.isValid = false;
          var total = 0;

          var i = use.ParcelUseAmounts.length;
          while(i--){
            if(use.ParcelUseAmounts[i].Curie_level)total = total + parseFloat(use.ParcelUseAmounts[i].Curie_level);
          }
          total = Math.round(total * 100000) / 100000;

          if(parseFloat(use.Quantity) == total){
            use.isValid = true;
          }else{
            orig.error = 'Total disposal amount must equal use amount.';
          }
          return use.isValid;
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
