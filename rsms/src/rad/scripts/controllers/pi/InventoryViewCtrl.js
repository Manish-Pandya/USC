'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # InventoryViewCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InventoryViewCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
      var af = actionFunctionsFactory;
      $scope.af = af;
      $scope.constants = Constants;

      $rootScope.loading  = af.getRadPIById($stateParams.pi)
          .then(
              function (pi) {
                  console.log(pi);
                  $scope.pi = pi;
              },
              function () { }
          );     

  })
