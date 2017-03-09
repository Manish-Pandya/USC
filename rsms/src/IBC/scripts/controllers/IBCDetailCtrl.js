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
    var getProtocol = function (id) {
        $scope.protocol = {};
        $scope.revision = {};
        $scope.responsesMapped = Object.create(null);
        return $q.all([DataStoreManager.getById("IBCProtocol", id, $scope.protocol, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.SectionMap])])
            .then(function (p) {
            DataStoreManager.getById("IBCSection", $scope.protocol.IBCSections[0].UID, {}, false)
                .then(function (someData) {
                var pRevision = $scope.protocol.IBCProtocolRevisions[$scope.protocol.IBCProtocolRevisions.length - 1];
                $q.all([DataStoreManager.getById("IBCProtocolRevision", pRevision.UID, $scope.revision, true)])
                    .then(function (someData) {
                    console.log(DataStoreManager._actualModel);
                    /*for (var n = 0; n < $scope.revision.IBCResponses.length; n++) {
                        var response = $scope.revision.IBCResponses[n];
                        console.log(response);
                        if (!$scope.revision.responsesMapped[response.Answer_id]) $scope.revision.responsesMapped[response.Answer_id] = [];
                        $scope.revision.responsesMapped[response.Answer_id].push(response);
                    }*/
                });
            });
        });
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getProtocol($stateParams.id));
})
    .controller('IBCDetailModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
    $scope.constants = Constants;
    var rbf = roleBasedFactory;
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});
