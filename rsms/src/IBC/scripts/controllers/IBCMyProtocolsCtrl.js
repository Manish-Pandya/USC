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
        $rootScope.pis = new ViewModelHolder();
        $rootScope.pi = new ViewModelHolder();
        return $q.all([DataStoreManager.getAll("PrincipalInvestigator", $rootScope.pis, [ibc.PrincipalInvestigator.ProtocolMap]), DataStoreManager.getById("PrincipalInvestigator", $stateParams.id, $rootScope.pi, [ibc.PrincipalInvestigator.ProtocolMap])])
            .then(function (p) {
            console.log($rootScope.pi);
            console.log(DataStoreManager._actualModel);
        });
    };
    var composeProtocols = function () {
        var promises = [];
        $rootScope.pi.data.Protocols.forEach(function (p) {
            promises.push(DataStoreManager.getById("IBCProtocol", p.UID, new ViewModelHolder(), [ibc.IBCProtocol.HazardMap, ibc.IBCProtocol.DepartmentMap]));
        });
        return $q.all(promises);
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getPI).then(composeProtocols);
    $scope.toggleActive = function (protocol) {
        protocol.Is_active = !protocol.Is_active;
        $scope.saving = $q.all([DataStoreManager.save(protocol)]);
    };
    $scope.openModal = function (object) {
        var modalData = { pi_id: $stateParams.id };
        if (!object) {
            object = new ibc.IBCProtocol;
            object.Is_active = true;
            object.PrincipalInvestigators.push($rootScope.pi.data);
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
    $scope.save = function (copy) {
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close)
            .then(function (p) {
            // recompose PI and its protocols...
            $q.all([DataStoreManager.getById("PrincipalInvestigator", $scope.modalData.pi_id, $rootScope.pi, [ibc.PrincipalInvestigator.ProtocolMap])])
                .then(function (p) {
                var promises = [];
                $rootScope.pi.data.Protocols.forEach(function (p) {
                    promises.push(DataStoreManager.getById("IBCProtocol", p.UID, new ViewModelHolder(), [ibc.IBCProtocol.HazardMap, ibc.IBCProtocol.DepartmentMap]));
                });
                $q.all(promises)
                    .then(function (p) {
                    console.log($rootScope.pi);
                    console.log(DataStoreManager._actualModel);
                });
            });
        });
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        DataStoreManager.ModalData = null;
    };
});
