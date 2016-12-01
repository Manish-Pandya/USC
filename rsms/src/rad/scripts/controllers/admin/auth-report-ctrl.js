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

      var getAllAuthReports = function () {
          af.getAllCarboys()
          .then(
              function (carboys) {
                  $scope.authReports = dataStore.AuthReport;
              },
              function () { }
          )
      }

      $rootScope.carboysPromise = af.getAllPIs().then(getAllAuthReports);

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
