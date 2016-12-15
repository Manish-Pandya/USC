'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:MainCtrl
 * @description
 * # MainCtrl
 * Primary Controller of the EquipmentModule
 */
angular.module('EquipmentModule')
  .controller('MainCtrl', function ($scope, $rootScope, applicationControllerFactory, $state, $modal) {
      console.log("MainCtrl running");

      //do we have access to action functions?
      var af = $scope.af = applicationControllerFactory;
      $scope.$state = $state;

      // get and store approved classNames and namespace
      console.log("approved classNames:", InstanceFactory.getClassNames(equipment));

      // method to async fetch current roles
      $rootScope.getCurrentRoles = function () {
          if (!DataStoreManager.CurrentRoles) {
              return XHR.GET("getCurrentRoles").then((roles) => { DataStoreManager.CurrentRoles = roles; })
          } else {
              return new Promise(resolve, reject).then(() => { return resolve(DataStoreManager.CurrentRoles) })
          }
      }

  });
