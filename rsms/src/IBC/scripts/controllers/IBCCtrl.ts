﻿'use strict';

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
            return $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.PIMap, ibc.IBCProtocol.SectionMap])])
            .then(
                function (whateverGotReturned) {
                    console.log($scope.protocols);
                    console.log(DataStoreManager._actualModel);
                }
            )
            .catch(
                function (reason) {
                    console.log("bad Promise.all:", reason);
                }
            )
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getAllProtocols);

        $scope.toggleActive = function (protocol) {
            protocol.Is_active = !protocol.Is_active;
            $scope.saving = $q.all([DataStoreManager.save(protocol)]);
        }

        $scope.openModal = function (object: FluxCompositerBase) {
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
        }

    })
    .controller('IBCModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, $q) {
        $scope.constants = Constants;
        $scope.modalData = DataStoreManager.ModalData;

        $scope.users = [];
        $scope.reviewers = [];

        $scope.loading = $q.all([DataStoreManager.getAll("User", $scope.users), DataStoreManager.resolveCompMaps($scope.modalData.IBCProtocolRevision, true)])
            .then((stuff) => {
                $scope.modalData.IBCProtocolRevision.doCompose(true);
                // loop through and check current PreliminaryReviewers before concactinating final reviewers list.
                $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.forEach((value) => {
                    value.isChecked = true;
                })

                var approvedUsers = $scope.users.filter(function (u) {
                    var hasCorrectRole: boolean = false;
                    if (_.indexOf($scope.modalData.IBCProtocolRevision.PreliminaryReviewers, u) == -1) {
                        u.Roles.forEach((value: ibc.Role, index: number, array: ibc.Role[]) => {
                            if (value.Name == Constants.ROLE.NAME.IBC_MEMBER || value.Name == Constants.ROLE.NAME.IBC_CHAIR) {
                                hasCorrectRole = true;
                            }
                        })
                    }
                    return hasCorrectRole;
                })
                $scope.reviewers = $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.concat(approvedUsers);
                console.log($scope.reviewers);
            });

        $scope.addRemoveReviewer = function (user, add: boolean) {
            var preliminaryReviewersIndex: number = _.indexOf($scope.modalData.IBCProtocolRevision.PreliminaryReviewers, user);
            if (add) {
                if (_.indexOf($scope.reviewers, user) == -1) {
                    $scope.reviewers.push(user);
                }
                if (user.isChecked && preliminaryReviewersIndex == -1) {
                    $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.push(user);
                }
            } else {
                $scope.modalData.IBCProtocolRevision.PreliminaryReviewers.splice(preliminaryReviewersIndex, 1);
            }
            console.log($scope.modalData.IBCProtocolRevision.PreliminaryReviewers);
        }

        $scope.save = function (copy) {
            $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
        }

        $scope.close = function () {
            $modalInstance.dismiss();
            DataStoreManager.ModalData = null;
        }
    })