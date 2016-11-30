'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('TransferCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getRadModels();
        var getParcels = function () {
            return af.getAllParcels()
                .then(
                    function (parcels) {
                        dataStore.Parcel.forEach(function (p) {
                            p.loadAuthorization();
                        })
                        return $scope.parcels = dataStore.Parcel;
                    }
                );
        }
        var getAllPis = function () {
            return af.getAllPIs().then(
            function (pis) {
                    return $scope.pis = dataStore.PrincipalInvestigator;
                }
            )
        }
        var getUses = function () {
            return af.getAllParcelUses().then(
                function (pis) {
                    return $scope.uses = dataStore.ParcelUse;
                }
            )
        }
        var getAuths = function () {
            return af.getAllPIAuthorizations().then(
                function (pis) {
                    return $scope.auths = dataStore.PIAuthorization;
                }
            )
        }

        $scope.loading = getAllPis()
            .then(getUses)
            .then(getAuths)
            .then(getParcels);

        $scope.openTransferInModal = function (object) {
            console.log(object);
            var modalData = {};
            if (object) {
                modalData.Parcel = object;
            } else {
                modalData.Parcel = { Class: "Parcel" };
            }
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/transfer-in-modal.html',
                controller: 'TransferModalCtrl'
            });
        }

    })
    .controller('TransferModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {


        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.dataStore = dataStore;
        $scope.modalData = af.getModalData();
        $scope.cv = convenienceMethods;

        $scope.onSelectPi = function (pi) {
            pi.loadPIAuthorizations();
            pi.loadActiveParcels();
            $scope.modalData.PI = pi;
        }

        $scope.getHighestAuth = function (pi) {
            if (pi && pi.Pi_authorization && pi.Pi_authorization.length) {
                var auths = _.sortBy(pi.Pi_authorization, [function (amendment) {
                    return moment(amendment.Approval_date).valueOf();
                }]);

                return auths[auths.length - 1];
            }
        }

        $scope.saveTransferIn = function (copy, parcel) {
            copy.Transfer_in_date = convenienceMethods.setMysqlTime(af.getDate(copy.view_Transfer_in_date));
            af.saveParcel(copy, parcel, $scope.modalData.PI)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }
    }])
