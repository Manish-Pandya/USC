'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
  .controller('AuthReportCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
      var af = $scope.af = actionFunctionsFactory;

      var getAllPIAuthorizations = function () {
          af.getAllPIAuthorizations()
          .then(
              function (piAuths) {
                  $scope.piAuths = [];
                  var piAuths = _.groupBy(dataStore.PIAuthorization, 'Principal_investigator_id');
                  for (var pi_id in piAuths) {
                      var newest_pi_auth = piAuths[pi_id].sort(function (a, b) {
                          var sortVector = b.Approval_date - a.Approval_date || b.Amendment_number - a.Amendment_number || b.Key_id - a.Key_id;
                          return sortVector;
                      })[0];
                      $scope.piAuths.push(newest_pi_auth);
                  }
                  console.log($scope.piAuths);
              },
              function () {
                  console.log("dang!");
              }
          )
      }

      $rootScope.piAuthsPromise = af.getAllPIs().then(getAllPIAuthorizations);

      /*$scope.getHighestAmendmentNumber = function (amendments) {
          if (!amendments) return;

          var highestAuthNumber = 0;
          _.sortBy(amendments, [function (amendment) {
              return moment(amendment.Approval_date).valueOf();
          }]);
          for (var i = 0; i < amendments.length; i++) {
              var amendment = amendments[i];
              convenienceMethods.dateToIso(amendment.Approval_date, amendment, "Approval_date", true);
              convenienceMethods.dateToIso(amendment.Termination_date, amendment, "Termination_date", true);
              amendment.Amendment_label = amendment.Amendment_number ? "Amendment " + amendment.Amendment_number : "Original Authorization";
              amendment.Amendment_label = amendment.Termination_date ? amendment.Amendment_label + " (Terminated " + amendment.view_Termination_date + ")" : amendment.Amendment_label + " (" + amendment.view_Approval_date + ")";
              amendment.weight = i;
              console.log(i);
          }

          $scope.mappedAmendments = amendments;

          $scope.selectedPiAuth = $scope.mappedAmendments[amendments.length - 1];
          $scope.selectedAmendment = amendments.length - 1;
          return $scope.selectedAmendment;
      }*/

      console.log("AuthReportCtrl running");
  });
