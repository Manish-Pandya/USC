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
                          pi.loadPIAuthorizations();
                          $rootScope.pi = pi;

                          $scope.auths = $scope.pi.Pi_authorization;
                          $scope.mappedAmendments = [];
                          for (var i = 0; i < $scope.pi.Pi_authorization.length; i++) {
                              var amendment = $scope.auths[i];
                              console.log(amendment)
                              amendment.Amendment_label = amendment.Amendment_number ? "Amendment " + amendment.Amendment_number : "Original Authorization";
                              amendment.weight = parseInt(amendment.Amendment_number || "0");
                              $scope.mappedAmendments[parseInt(amendment.weight)] = amendment;
                          }

                          $scope.getHighestAmendmentNumber = function (amendments) {
                              if (!amendments) var highestAuthNumber = "0";

                              if (amendments.length == 1) {
                                  $scope.selectedAmendment = parseInt(0);
                                  $scope.selectedPiAuth = $scope.mappedAmendments[parseInt(0)];
                                  var highestAuthNumber = "0";
                              } else {
                                  var highestAuthNumber = 0;
                                  for (var i = 0; i < amendments.length; i++) {
                                      var auth = amendments[i];
                                      if (auth.Amendment_number && auth.Amendment_number > highestAuthNumber) {
                                          highestAuthNumber = auth.Amendment_number;
                                      }
                                      console.log(i);

                                  }
                                  $scope.selectedAmendment = parseInt(highestAuthNumber);;
                                  $scope.selectedPiAuth = $scope.mappedAmendments[parseInt(highestAuthNumber)];
                                  console.log(highestAuthNumber);
                                  return highestAuthNumber;

                              }
                          }
                          $scope.getHighestAmendmentNumber($scope.mappedAmendments);


                          return pi;
                      },
                      function () {
                      }
                  );
      }

      $rootScope.radPromise = getRadPi();


  })