'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('AllOrdersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
        var af = actionFunctionsFactory;
      console.log("orders ctrl")
        $scope.af = af;
        $rootScope.parcelPromise = af.getAllPIs()
                                        .then(function(){
                                            var i = dataStore.PrincipalInvestigator.length;
                                            while(i--){
                                                dataStore.PrincipalInvestigator[i].loadActiveParcels();
                                                dataStore.PrincipalInvestigator[i].loadPurchaseOrders();
                                                dataStore.PrincipalInvestigator[i].loadPIAuthorizations();
                                            }
                                            $scope.pis = dataStore.PrincipalInvestigator;
                                        });

        $scope.deactivate = function(carboy){
            var copy = dataStoreManager.createCopy(carboy);
            copy.Retirement_date = new Date();
            af.saveCarboy(carboy.PrincipalInvestigator, copy, carboy);
        }

        $scope.openModal = function(object, pi) {
            var modalData = {};
            if (!object) {
                object = new window.Parcel();
                object.Class = "Parcel";
            }
            modalData.pi = pi;
            modalData[object.Class] = object;
            console.log(modalData);
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/parcel-modal.html',
                controller: 'PiDetailModalCtrl'
            });
        }


        $scope.openWipeTestModal = function(parcel, pi){
            var modalData = {};
            modalData.pi = pi;
            modalData.Parcel = parcel;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
              templateUrl: 'views/admin/admin-modals/package-wipe-test.html',
              controller: 'WipeTestModalCtrl'
            });
        }

        $scope.updateParcelStatus = function(pi, parcel, status){
            var copy = new window.Parcel;
            angular.extend(copy, parcel);
            copy.Status = status;
            copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
            af.saveParcel( copy, parcel, pi )
        }

  })
