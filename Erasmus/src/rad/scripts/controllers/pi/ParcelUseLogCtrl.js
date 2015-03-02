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
          $rootScope.ParcelUseCopy.isNew = true;
          solidUsageAmount.Waste_type_id = 4;
          liquidUsageAmount.Waste_type_id = 1;
          vialUsageAmount.Waste_type_id = 3;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          parcel.ParcelUses.unshift($rootScope.ParcelUseCopy);
      }

      $scope.editUse = function(use){
          var i = $scope.parcel.ParcelUses.length;
          while(i--){
            $scope.parcel.ParcelUses[i].edit=false
          }
          $rootScope.ParcelUseCopy = {}

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


      //this is here specifically because form validation seems like it belongs in the controller (VM) layer rather than the CONTROLLER(actionFunctions layer) of this application,
      //which if you think about it, has sort of become an MVCVM 

      $scope.validateUseAmounts = function(use){
          $rootScope.error = '';
          use.isValid = false;
          var total = 0;

          var i = use.Liquids.length;
          while(i--){
            if(use.Liquids[i].Curie_level)total = total + parseInt(use.Liquids[i].Curie_level);
          }

          var j = use.Solids.length;
          while(j--){
            if(use.Solids[j].Curie_level)total = total + parseInt(use.Solids[j].Curie_level);
          }

          var k = use.Vials.length;
          while(k--){
            if(use.Vials[k].Curie_level)total = total + parseInt(use.Vials[k].Curie_level);
          }

          console.log('total '+total);
          console.log('quantity '+use.Quantity)
          if(use.Quantity == total){
            use.isValid = true;
          }else{
            $rootScope.error = 'Total disposal amount must equal use amount.';
          }
      }
 });
