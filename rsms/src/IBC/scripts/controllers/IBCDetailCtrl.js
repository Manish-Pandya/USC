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
    var getProtocol = function () {
        $scope.protocol = new ViewModelInstance();
        $scope.revision = new ViewModelInstance();
        $scope.responsesMapped = Object.create(null);
        return $q.all([DataStoreManager.getById("IBCProtocol", $stateParams.id, $scope.protocol, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.SectionMap])])
            .then(function (p) {
            console.log($scope.protocol);
            console.log(DataStoreManager._actualModel);
            DataStoreManager._actualModel['IBCProtocol']['Data'][0].viewModelWatcher.Department_id = DataStoreManager._actualModel['IBCProtocol']['Data'][0].viewModelWatcher.Department_id + " updated in controller";
            return;
            DataStoreManager.getById("IBCSection", $scope.protocol.data.IBCSections[0].UID, new ViewModelInstance(), false)
                .then(function (someData) {
                var pRevision = $scope.protocol.data.IBCProtocolRevisions[$scope.protocol.data.IBCProtocolRevisions.length - 1];
                $q.all([DataStoreManager.getById("IBCProtocolRevision", pRevision.UID, $scope.revision, true)])
                    .then(function (someData) {
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
    $scope.testSave = function (data) {
        return $scope.loading = $q.all([DataStoreManager.save(data)]).then(function (r) {
            console.log(r);
            console.log($scope.protocol.data);
            console.log(DataStoreManager._actualModel);
            return r;
        });
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getProtocol);
})
    .controller('IBCDetailModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
    $scope.constants = Constants;
    var rbf = roleBasedFactory;
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});
