﻿'use strict';

/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCAssignCtrl
 * @description
 * # IBCAssignCtrl
 * Controller of the IBC protocol Assign for Review view
 */
angular.module('ng-IBC')
    .controller('IBCAssignCtrl', function ($rootScope, $scope, $modal, $location, $q, convenienceMethods) {
        console.log("IBCAssignCtrl running");
        $scope.cv = convenienceMethods;
        
        function getProtocols() {
            $scope.protocols = [];
            $scope.reviewers = [];
            $scope.loading = $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.PIMap]), DataStoreManager.getAll("IBCProtocolRevision", [], true), DataStoreManager.getAll("User", $scope.reviewers, true)])
            .then((stuff) => {
                console.log($scope.protocols);
                console.log(DataStoreManager._actualModel);

                $scope.reviewers = $scope.reviewers.filter(function (u) {
                    var hasCorrectRole: boolean = false;
                    u.Roles.forEach((value: ibc.Role, index: number, array: ibc.Role[]) => {
                        if (value.Name == Constants.ROLE.NAME.IBC_MEMBER || value.Name == Constants.ROLE.NAME.IBC_CHAIR) {
                            hasCorrectRole = true;
                        }
                    })
                    return hasCorrectRole;
                })
            });
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getProtocols);

        $scope.addRemoveReviewer = function (protocol: ibc.IBCProtocol, user: ibc.User, add: boolean) {
            var protocolRevision: ibc.IBCProtocolRevision = protocol.IBCProtocolRevisions[protocol.IBCProtocolRevisions.length - 1];
            var primaryReviewersIndex: number = _.findIndex(protocolRevision.PrimaryReviewers, ["UID", user.UID]);
            if (add && primaryReviewersIndex == -1) {
                if (primaryReviewersIndex == -1) {
                    protocolRevision.PrimaryReviewers.push(user);
                }
            } else if (primaryReviewersIndex > -1) {
                protocolRevision.PrimaryReviewers.splice(primaryReviewersIndex, 1);
            }
        }

        $scope.save = function (protocols: ibc.IBCProtocol[]) {
            console.log(protocols);
            var protocolRevisions: ibc.IBCProtocolRevision[] = [];
            protocols.forEach((value) => {
                if (value.IBCProtocolRevisions) {
                    protocolRevisions.push(value.IBCProtocolRevisions[value.IBCProtocolRevisions.length - 1]);
                }
            })
            protocolRevisions[protocolRevisions.length - 1]["Status"] = Constants.IBC_PROTOCOL_REVISION.STATUS.IN_REVIEW;
            $scope.saving = $q.all([DataStoreManager.save(protocolRevisions)])
                .then(() => {
                    console.log("i finished saving");
                });
        }
    })
    .controller('IBCAssignModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })