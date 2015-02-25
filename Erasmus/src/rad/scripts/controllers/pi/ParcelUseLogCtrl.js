'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('ParcelUseLogCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {

  		var af = actionFunctionsFactory;
  		$scope.af = af;

  		var getPi = function(){
        return af.getRadPIById($stateParams.pi)
    			.then(
    				function(pi){
    					$scope.pi = pi;
              return pi;
    				},
    				function(){}
    			)
      }

      var getParcel = function(id){
        var parcel = af.getById('Parcel',$stateParams.parcel);
        return af.getParcelUses(parcel)
          .then(
            function(){
              $scope.parcel = parcel;
            }
          );
      }

      $scope.parcelPromise = getPi()
                              .then(getParcel);
      
 });
