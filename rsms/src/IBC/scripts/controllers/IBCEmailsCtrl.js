'use strict';

/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCCtrl
 * @description
 * # IBCDetailCtrl
 * Controller of the IBC protocal Emails view
 */
angular.module('ng-IBC')
    .controller('IBCEmailCtrl', function ($rootScope, $scope, $modal, $location) {
        console.log("IBCEmailCtrl running");
        //$scope.loading = DataStoreManger.getAll([])
    })
    .controller('IBCEmailModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })