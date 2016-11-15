﻿'use strict';

/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCCtrl
 * @description
 * # IBCDetailCtrl
 * Controller of the IBC protocals home view
 */
angular.module('ng-IBC')
    .controller('IBCCtrl', function ($rootScope, $scope, $modal, $location) {
        console.log("IBCCtrl running");

        function getAllProtocols() {
            $scope.protocols = [];
            Promise.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, true)])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.protocols);
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
        }

        $rootScope.getCurrentRoles().then(getAllProtocols);
    })
    .controller('IBCModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })