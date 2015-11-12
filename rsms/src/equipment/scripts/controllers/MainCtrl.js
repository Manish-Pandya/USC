'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:MainCtrl
 * @description
 * # MainCtrl
 * Primary Controller of the EquipmentModule
 */
angular.module('EquipmentModule')
  .controller('MainCtrl', function ($scope, $rootScope, actionFunctionsFactory, $state, $modal) {
    //do we have access to action functions?
    var af = $scope.af = actionFunctionsFactory;
    $scope.$state = $state;

  });
