'use strict';
/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCCtrl
 * @description
 * # IBCDetailCtrl
 * Controller of the IBC protocals home view
 */
angular.module('ng-IBC')
    .controller('IBCCtrl', function ($rootScope, $scope, $modal, $location, $q) {
    console.log("IBCCtrl running");
    console.log("approved classNames:", InstanceFactory.getClassNames(ibc));
    $scope.protocolStatuses = _.toArray(Constants.IBC_PROTOCOL_REVISION.STATUS);
    console.log($scope.protocolStatuses);
    function getAllProtocols() {
        $scope.protocols = [];
        $scope.loading = $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, true)])
            .then(function (whateverGotReturned) {
            console.log($scope.protocols);
            console.log(DataStoreManager._actualModel);
        })
            .catch(function (reason) {
            console.log("bad Promise.all:", reason);
        });
    }
    $scope.loading = $rootScope.getCurrentRoles().then(getAllProtocols);
})
    .controller('IBCModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
    $scope.constants = Constants;
    var rbf = roleBasedFactory;
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});
