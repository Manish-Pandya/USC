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
            //console.log(DataStoreManager._actualModel);
        })
            .catch(function (reason) {
            console.log("bad Promise.all:", reason);
        });
    }
    $scope.loading = $rootScope.getCurrentRoles().then(getAllProtocols);
    $scope.toggleActive = function (protocol) {
        protocol.Is_active = !protocol.Is_active;
        $scope.saving = $q.all([DataStoreManager.save(protocol)]);
    };
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new ibc.IBCProtocolRevision;
        }
        modalData[object.thisClass['name']] = object;
        DataStoreManager.ModalData = modalData;
        var modalInstance = $modal.open({
            templateUrl: 'views/modals/assign-for-review-modal.html',
            controller: 'IBCModalCtrl'
        });
    };
})
    .controller('IBCModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, $q) {
    $scope.constants = Constants;
    $scope.modalData = DataStoreManager.ModalData;
    $scope.users = [];
    DataStoreManager.getAll("User", $scope.users);
    $scope.save = function (copy) {
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        DataStoreManager.ModalData = null;
    };
});
