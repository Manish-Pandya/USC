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

        $scope.test = "Look at my asshole!";

        var getProtocol = function (id: number | string): Promise<any> {
            $scope.protocol = {};
            $scope.revision = {};
            $scope.responsesMapped = <{ [index: string]: ibc.IBCResponse[] }>Object.create(null);
            return $q.all([DataStoreManager.getById("IBCProtocol", id, $scope.protocol)])
                .then(
                    function (p) {
                        DataStoreManager.getById("IBCSection", $scope.protocol.IBCSections[0].UID, {}, true)
                            .then(
                            function (someData) {
                                var pRevision: ibc.IBCProtocolRevision = $scope.protocol.IBCProtocolRevisions[$scope.protocol.IBCProtocolRevisions.length - 1];
                                $q.all([DataStoreManager.getById("IBCProtocolRevision", pRevision.UID, $scope.revision, [ibc.IBCProtocolRevision.IBCReponseMap])])
                                        .then(
                                            function (someData) {
                                                console.log($scope.revision);
                                                for (var n = 0; n < $scope.revision.IBCResponses.length; n++) {
                                                    var response = $scope.revision.IBCResponses[n];
                                                    if (!$scope.responsesMapped[response.Answer_id]) $scope.responsesMapped[response.Answer_id] = [];
                                                    $scope.responsesMapped[response.Answer_id].push(response);
                                                }
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