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
    $scope.protocolStatuses = _.toArray(Constants.IBC_PROTOCOL_REVISION.STATUS);
    var getPI = function () {
        $scope.pi = new ViewModelHolder();
        return $q.all([DataStoreManager.getById("PrincipalInvestigator", $stateParams.id, $scope.pi, [ibc.PrincipalInvestigator.ProtocolMap])])
            .then(function (p) {
            var protocols = $scope.pi.data.Protocols;
            protocols.forEach(function (p) {
                DataStoreManager.getById("IBCProtocol", p.UID, new ViewModelHolder(), [ibc.IBCProtocol.HazardMap]);
            });
            console.log($scope.pi);
            console.log(DataStoreManager._actualModel);
        });
    };
    var composeProtocols = function () {
        var promises = [];
        $scope.pi.data.Protocols.forEach(function (p) {
            promises.push(DataStoreManager.getById("IBCProtocol", p.UID, new ViewModelHolder(), [ibc.IBCProtocol.HazardMap]));
        });
        return $q.all(promises);
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getPI).then(composeProtocols);
    $scope.toggleActive = function (protocol) {
        protocol.Is_active = !protocol.Is_active;
        $scope.saving = $q.all([DataStoreManager.save(protocol)]);
    };
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new ibc.IBCProtocol;
            object.Is_active = true;
            object.PrincipalInvestigators.push($scope.pi.data);
        }
        modalData[object.thisClass['name']] = object;
        DataStoreManager.ModalData = modalData;
        var modalInstance = $modal.open({
            templateUrl: 'views/modals/protocol-modal.html',
            controller: 'IBCMyProtocolsModalCtrl'
        });
    };
})
    .controller('IBCMyProtocolsModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, $q) {
    $scope.constants = Constants;
    $scope.modalData = DataStoreManager.ModalData;
    console.log($scope.modalData);
    $scope.pis = new ViewModelHolder();
    $scope.loading = $q.all([DataStoreManager.getAll("PrincipalInvestigator", $scope.pis)])
        .then(function (p) {
        console.log($scope.pis);
    });
    $scope.save = function (copy) {
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        DataStoreManager.ModalData = null;
    };
});
