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
    $scope.protocolStatuses = _.toArray(Constants.IBC_PROTOCOL_REVISION.STATUS);
    console.log($scope.protocolStatuses);
    function getAllProtocols() {
        $scope.protocols = [];
        $scope.loading = $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.PIMap])])
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
    $scope.reviewers = [];
    $scope.loading = $q.all([DataStoreManager.getAll("User", $scope.users)]).then(function (whateverGotReturned) {
        $scope.modalData.IBCProtocolRevision.doCompose(true);
        var approvedUsers = $scope.users.filter(function (u) {
            var hasCorrectRole = false;
            u.Roles.forEach(function (value, index, array) {
                if (value.Name == Constants.ROLE.NAME.IBC_MEMBER || value.Name == Constants.ROLE.NAME.IBC_CHAIR) {
                    hasCorrectRole = true;
                }
            });
            return hasCorrectRole;
        });
        $scope.reviewers = $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.concat(approvedUsers);
    });
    $scope.addRemoveReviewer = function (user, add) {
        var preliminaryReviewersIndex = $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.indexOf(user);
        if (add) {
            if ($scope.reviewers.indexOf(user) == -1) {
                $scope.reviewers.push(user);
            }
            if (user.isChecked && preliminaryReviewersIndex == -1) {
                $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.push(user);
            }
        }
        else {
            $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.splice(preliminaryReviewersIndex, 1);
        }
        console.log($scope.modalData.IBCProtocolRevision.PreliminaryReviewers);
    };
    $scope.save = function (copy) {
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
        DataStoreManager.ModalData = null;
    };
});
