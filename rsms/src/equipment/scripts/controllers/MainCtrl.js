'use strict';

/**
 * @ngdoc function
 * @name EquipmentModule.controller:MainCtrl
 * @description
 * # MainCtrl
 * Primary Controller of the EquipmentModule
 */
angular.module('EquipmentModule')
  .controller('MainCtrl', function ($scope, $rootScope, applicationControllerFactory, $state, $modal,$q) {

      //do we have access to action functions?
      var af = $scope.af = applicationControllerFactory;
      $scope.$state = $state;

      // get and store approved classNames and namespace
      console.log("approved classNames:", InstanceFactory.getClassNames(equipment));

      // method to async fetch current roles
      $rootScope.getCurrentRoles = function () {
          if (!DataStoreManager.CurrentRoles) {
              return $q.all(
                  [XHR.GET("getCurrentRoles").then((roles) => {
                      DataStoreManager.CurrentRoles = roles;
                      return roles;
                  })]
              )
          } else {
              return $q.all(
                  [new Promise(function (resolve, reject) {
                      resolve(DataStoreManager.CurrentRoles);
                  }).then(() => {
                      return DataStoreManager.CurrentRoles;
                  })]
              )
          }
      }

  });
