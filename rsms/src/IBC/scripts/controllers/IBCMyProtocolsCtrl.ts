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
    })
    .controller('IBCMyProtocolsModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })