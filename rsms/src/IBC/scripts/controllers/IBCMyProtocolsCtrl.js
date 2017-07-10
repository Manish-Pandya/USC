'use strict';
/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCMyProtocolsCtrl
 * @description
 * # IBCMyProtocolsCtrl
 * Controller of the IBC my-protocols view
 */
angular.module('ng-IBC')
    .controller('IBCMyProtocolsCtrl', function ($rootScope, $scope, $modal, $location, $stateParams, $q) {
    console.log("IBCMyProtocolsCtrl running");
    $scope.loading = $rootScope.getCurrentRoles();
})
    .controller('IBCMyProtocolsModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
    $scope.constants = Constants;
    var rbf = roleBasedFactory;
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});
