'use strict';

/**
 * @ngdoc function
 * @name IBCDetailCtrl.controller:IBCDetailCtrl
 * @description
 * # IBCDetailCtrl
 * Controller of the IBC protocal detail view
 */
angular.module('ng-IBC')
    .controller('IBCDetailCtrl', function ($rootScope, $scope, $modal, $location, $stateParams, $q) {
        console.log("IBCDetailCtrl running");

        var getProtocol = function (): Promise<any> {
            $scope.protocol = new ViewModelHolder();
            $scope.revision = new ViewModelHolder();
            return $q.all([DataStoreManager.getById("IBCProtocol", $stateParams.id, $scope.protocol, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.SectionMap])])
                .then(function (p) {
                    console.log($scope.protocol);
                    console.log(DataStoreManager._actualModel);

                    var pRevision: ibc.IBCProtocolRevision = $scope.protocol.data.IBCProtocolRevisions[$scope.protocol.data.IBCProtocolRevisions.length - 1];
                    $q.all([DataStoreManager.getById("IBCProtocolRevision", pRevision.UID, $scope.revision, true)])
                });
        }

        $scope.testSave = function (data) {
            return $scope.loading = $q.all([DataStoreManager.save(data)]).then((r) => {
                console.log(r);
                console.log($scope.protocol.data);
                console.log(DataStoreManager._actualModel);
                return r;
            });
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getProtocol);
    })
    .controller('IBCDetailModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })