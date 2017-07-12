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

        var getPI = function (): Promise<any> {
            $scope.pi = new ViewModelHolder();

            return $q.all([DataStoreManager.getById("PrincipalInvestigator", $stateParams.id, $scope.pi, [ibc.PrincipalInvestigator.ProtocolMap])])
                .then(function (p) {
                    console.log($scope.pi);
                    console.log(DataStoreManager._actualModel);
                });
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getPI);

        $scope.toggleActive = function (protocol) {
            protocol.Is_active = !protocol.Is_active;
            $scope.saving = $q.all([DataStoreManager.save(protocol)]);
        }

        $scope.openModal = function (object: FluxCompositerBase) {
            var modalData = {};
            if (!object) {
                object = new ibc.IBCProtocol;
            }
            modalData[object.thisClass['name']] = object;
            DataStoreManager.ModalData = modalData;
            var modalInstance = $modal.open({
                templateUrl: 'views/modals/assign-for-review-modal.html',
                controller: 'IBCModalCtrl'
            });
        }

    })
    .controller('IBCMyProtocolsModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory, $q) {
        $scope.constants = Constants;
        $scope.modalData = DataStoreManager.ModalData;
        var rbf = roleBasedFactory;

        $scope.save = function (copy) {
            $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
        }

        $scope.close = function () {
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
        }
    })