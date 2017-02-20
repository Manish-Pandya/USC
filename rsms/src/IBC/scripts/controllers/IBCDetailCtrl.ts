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

        var getProtocol = function (id: number | string): Promise<any> {
            $scope.protocol = {};
            $scope.revision = {};
            return $q.all([DataStoreManager.getById("IBCProtocol", id, $scope.protocol)])
                .then(
                    function (p) {
                        DataStoreManager.getById("IBCSection", $scope.protocol.IBCSections[0].UID, {}, true)
                            .then(
                            function (someData) {
                                var pRevision: ibc.IBCProtocolRevision = $scope.protocol.IBCProtocolRevisions[$scope.protocol.IBCProtocolRevisions.length - 1];
                                console.log($scope.revision);
                                $q.all([DataStoreManager.getById("IBCProtocolRevision", pRevision.UID, $scope.revision, [ibc.IBCProtocolRevision.IBCReponseMap])])
                                        .then(
                                            function (someData) {
                                                console.log($scope.revision);
                                            }
                                        )
                                }
                            )
                    }
                );
        }

        $scope.loading = getProtocol($stateParams.id);
    })
    .controller('IBCDetailModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })