'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:UseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('UseLogCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
          var af = actionFunctionsFactory;
          $scope.af = af;
          $rootScope.piPromise = $scope.parcelPromise = af.getRadPIById($stateParams.pi)
              .then(
                  function(pi){
                      $scope.pi = dataStoreManager.getById("PrincipalInvestigator", $stateParams.pi);
                      console.log(dataStore);
                  },
                  function(){}
              )
 });
