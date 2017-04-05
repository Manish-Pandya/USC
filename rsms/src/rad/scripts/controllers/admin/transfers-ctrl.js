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
        $scope.dsm = dataStoreManager;

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
                modalData.pi = dataStoreManager.getById("PrincipalInvestigator", object.Principal_investigator_id);
            } else {
                modalData.Parcel = { Class: "Parcel" };
            }
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/transfer-in-modal.html',
                controller: 'TransferModalCtrl'
            });
        }

        $scope.openTransferInventoryModal = function (object) {
            console.log(object);
            var modalData = {};
            if (object) {
                modalData.Parcel = object;
                modalData.pi = dataStoreManager.getById("PrincipalInvestigator", object.Principal_investigator_id);
            } else {
                modalData.Parcel = { Class: "Parcel" };
            }
            modalData.Parcel.Is_active = true;
            modalData.Parcel.Status = Constants.PARCEL.STATUS.DELIVERED;
            //all inventory transfers get a date of the end of the year before the system's o
            console.log(modalData);
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/transfer-inventory-modal.html',
                controller: 'TransferModalCtrl'
            });
        }

        $scope.openTransferOutModal = function (object) {
            console.log(object);
            var modalData = {};
            if (object) {
                if (object.Parcel_id) {
                    var parcel = dataStoreManager.getById("Parcel", object.Parcel_id)
                    if (parcel) var auth = dataStoreManager.getById("Authorization", parcel.Authorization_id);
                    if (auth) var piAuth = dataStoreManager.getById("PIAuthorization", auth.Pi_authorization_id);
                    if (piAuth) modalData.pi = dataStoreManager.getById("PrincipalInvestigator", piAuth.Principal_investigator_id);
                    modalData.pi.loadActiveParcels().then(function () {
                        modalData.ParcelUse = object;
                        af.setModalData(modalData);
                        var modalInstance = $modal.open({
                            templateUrl: 'views/admin/admin-modals/transfer-out-modal.html',
                            controller: 'TransferModalCtrl'
                        });
                    })
                }
            } else {
                modalData.ParcelUse = { Class: "ParcelUse" };
                af.setModalData(modalData);
                var modalInstance = $modal.open({
                    templateUrl: 'views/admin/admin-modals/transfer-out-modal.html',
                    controller: 'TransferModalCtrl'
                });
            }
            
        }

        $scope.openTransferBetweenModal = function (object) {
            console.log(object);
            var modalData = {};
            modalData.transferBetween = true;

            

            if (object) {
                if (!object.Destination_parcel_id) {
                    object.DestinationParcel = new Parcel();
                    object.DestinationParcel.Class = "Parcel";
                }

                if (object.Parcel_id) {
                    var parcel = dataStoreManager.getById("Parcel", object.Parcel_id)
                    if (parcel) var auth = dataStoreManager.getById("Authorization", parcel.Authorization_id);
                    if (auth) var piAuth = dataStoreManager.getById("PIAuthorization", auth.Pi_authorization_id);
                    if (piAuth) modalData.pi = dataStoreManager.getById("PrincipalInvestigator", piAuth.Principal_investigator_id);

                    modalData.pi.loadActiveParcels().then(function () {
                        modalData.ParcelUse = object;
                        af.setModalData(modalData);
                        var modalInstance = $modal.open({
                            templateUrl: 'views/admin/admin-modals/transfer-between-modal.html',
                            controller: 'TransferModalCtrl'
                        });
                    })
                }
            } else {
                
                modalData.ParcelUse = { Class: "ParcelUse" };
                var object = modalData.ParcelUse;
                object.DestinationParcel = new Parcel();
                object.DestinationParcel.Class = "Parcel";               

                af.setModalData(modalData);
                var modalInstance = $modal.open({
                    templateUrl: 'views/admin/admin-modals/transfer-between-modal.html',
                    controller: 'TransferModalCtrl'
                });
            }

        }

    })
    .controller('TransferModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {


        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.dataStore = dataStore;
        $scope.dsm = dataStoreManager;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.cv = convenienceMethods;


        //set up local model for transfer between

        $scope.onSelectPi = function (pi, parcel) {
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
            console.log(parcel);
            copy.Transfer_in_date = convenienceMethods.setMysqlTime(af.getDate(copy.view_Transfer_in_date));
            af.saveParcel(copy, parcel, $scope.modalData.PI)
                .then($scope.close);
        }

        $scope.saveTransferOut = function (parcel, copy, use) {
            $scope.modalData.tooMuch = false;
            if (copy.Quantity > parcel.Remainder) {
                $scope.modalData.tooMuch = "You can't transfer that much.";
                return;
            }
            parcel.loadUses().then(function () {
                var amt = new ParcelUseAmount();
                amt.Parcel_use_id = copy.Key_id || null;
                if (copy.ParcelUseAmounts && copy.ParcelUseAmounts.length) {
                    amt.Key_id = copy.ParcelUseAmounts[0].Key_id || null;
                    amt.Comments = copy.ParcelUseAmounts[0].Comments;
                }
                amt.Class = "ParcelUseAmount";
                amt.Curie_level = copy.Quantity;
                amt.Waste_type_id = Constants.WASTE_TYPE.TRANSFER;

                copy.ParcelUseAmounts = [amt];
                copy.Date_transferred = convenienceMethods.setMysqlTime(copy.view_Date_transferred);
                console.log(copy);
               
                //if it walks like a duck
                if (!use.Key_id) use = false;
                $scope.saving = af.saveParcelUse(parcel, copy, use)
                    .then($scope.close);
            })
            
        }


        $scope.selectReceivingPi = function (pi) {
            $scope.loading = pi.loadPIAuthorizations().then(function () {
                console.log(pi);
                $scope.auths = $scope.getHighestAuth(pi);
                console.log($scope.auths);
                return $scope.auths;
            })
        }
        $scope.getReceivingPi = function (use) {
            var pi = dataStoreManager.getById("PrincipalInvestigator", use.DestinationParcel.Principal_investigator_id);
            $scope.selectReceivingPi(pi);
            return pi;
        }
        $scope.saveTransferBetween = function (parcel, copy, use) {
            $scope.modalData.tooMuch = false;
            if (copy.Quantity > parcel.Remainder) {
                $scope.modalData.tooMuch = "You can't transfer that much.";
                return;
            }

            var parcels = dataStoreManager.get("Parcel");
            $scope.rsError = false;
            parcels.forEach(function (p) {
                if (p.Rs_number == copy.DestinationParcel.Rs_number) $scope.rsError = true;
            });
            if ($scope.rsError) return;
            parcel.loadUses().then(function () {
                var amt = new ParcelUseAmount();
                amt.Parcel_use_id = copy.Key_id || null;
                if (copy.ParcelUseAmounts && copy.ParcelUseAmounts.length) {
                    amt.Key_id = copy.ParcelUseAmounts[0].Key_id || null;
                    amt.Comments = copy.ParcelUseAmounts[0].Comments;
                }
                amt.Class = "ParcelUseAmount";
                amt.Curie_level = copy.Quantity;
                amt.Waste_type_id = Constants.WASTE_TYPE.TRANSFER;

                copy.ParcelUseAmounts = [amt];
                copy.Date_transferred = convenienceMethods.setMysqlTime(copy.view_Date_transferred);
                copy.DestinationParcel.Transfer_in_date = convenienceMethods.setMysqlTime(copy.view_Date_transferred);

                console.log(copy);

                //if it walks like a duck
                if (!use.Key_id) use = false;
                $scope.saving = af.saveParcelUse(parcel, copy, use)
                    .then($scope.close);
            })
        }

        $scope.getTransferNumberSuggestion = function (str) {
            console.log(str);
            var parcels = dataStoreManager.get("Parcel");
            var num = 0;
            var finalNum = 1;
            parcels.forEach(function (p) {
                if (p.Rs_number.indexOf(str) != -1) {
                    console.log(p.Rs_number.substring(2));
                    var pNum = parseInt(p.Rs_number.substring(2));
                    if (pNum > num) num = pNum;
                }
            });
            return num+1;
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }
    }])
