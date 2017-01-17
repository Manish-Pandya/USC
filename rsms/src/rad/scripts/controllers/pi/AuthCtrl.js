'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('AuthCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
      //do we have access to action functions?
      var af = actionFunctionsFactory;
      $scope.af = af;

      var getRadPi = function () {
          return actionFunctionsFactory.getRadPIById($stateParams.pi)
                  .then(
                      function (pi) {                             
                          $rootScope.pi = pi;
                          return pi
                      },
                      function () {
                      }
                  )
                    .then(function (pi) {
                        return pi.loadPIAuthorizations().then(
                              function () {
                                  var auth = $rootScope.getHighestAuth(pi);
                                  auth.Amendment_label = auth.Amendment_number ? "Amendment " + auth.Amendment_number : "Original Authorization";
                                  auth.weight = parseInt(auth.Amendment_number || "0");
                                  return auth;
                              }
                          );
                    })
                    .then(function (auth) {
                      $scope.roomsLoading = auth.loadRooms().then(function () { auth.loadDepartments() });
                      return $scope.selectedPiAuth = auth;
                  });
      }

      $rootScope.parcelPromise = getRadPi();


  })