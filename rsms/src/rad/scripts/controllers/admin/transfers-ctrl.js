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
                        return $scope.parcels = dataStore.Parcel;
                    }
                );
        }
        var getAllPis = function () {
            return af.getAllParcels()
                .then(
                    function (parcels) {
                        return $scope.parcels = dataStore.Parcel;
                    }
                );
        }
        var getUses = function () {
            return af.getAllPIs().then(
                function (pis) {
                    return $scope.pis = dataStore.PrincipalInvestigator;
                }
            )
        }

        $scope.loading = getParcels()
            .then(getAllPis)
            .then(getUses);

        $scope.openModal = function (object) {
            console.log(object);
            var modalData = {};
            if (object) modalData[object.Class] = object;
           
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/transfer-modal.html',
                controller: 'TransferModalCtrl'
            });
        }

    })
    .controller('TransferModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {


        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.dataStore = dataStore;
        $scope.modalData = af.getModalData();

        $scope.save = function (test) {
            af.saveParcelWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }
    }])
