'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('WipeTestController', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;

    var getParcels = function(){
       $rootScope.parcelsPromise = af.getAllParcels()
        .then(
            function(parcels){
                $rootScope.parcels = parcels;
                return parcels;
            },
            function(){
            }
        );  
    }

    getParcels();

    $scope.editWipeParcelWipeTest = function(parcel, test){
        $rootScope.ParcelWipeTestCopy = {}

        if(!test){
            $rootScope.ParcelWipeTestCopy = new window.ParcelWipeTest();
            $rootScope.ParcelWipeTestCopy.Parcel_id = parcel.Key_id
            $rootScope.ParcelWipeTestCopy.Class = "ParcelWipeTest";
        }else{
            af.createCopy(test);
        }

        var i = $scope.wipeTestParcels.length
        while(i--){
            $scope.wipeTestParcels[i].Creating_wipe = false;
        }
        parcel.Creating_wipe = true;
        
    }

    $scope.cancelParcelWipeTestEdit = function(parcel){
        parcel.Creating_wipe = false;
        $rootScope.ParcelWipeTestCopy = {}
    }


    $scope.editWipeParcelWipe = function(wipeTest, wipe){
        $rootScope.ParcelWipeCopy = {}
        if(!wipeTest.ParcelWipes)wipeTest.ParcelWipes = [];
        var i = wipeTest.ParcelWipes.length;
        while(i--){
            $scope.wipeTest.ParcelWipes[i].edit = false;
        }

        if(!wipe){
            $rootScope.ParcelWipeCopy = new window.ParcelWipe();
            $rootScope.ParcelWipeCopy.Parcel_wipe_test_id = wipeTest.Key_id
            $rootScope.ParcelWipeCopy.Class = "ParcelWipe";
            $rootScope.ParcelWipeCopy.edit = true;
            wipeTest.ParcelWipes.unshift($rootScope.ParcelWipeCopy);
        }else{
            wipe.edit = true;
            af.createCopy(wipe);
        }
        
    }

    //Suggested/common locations for performing parcel wipes
    $scope.parcelWipeLocations = ['Outside','Inside','Bag','Styrofoam','Cylinder','Vial','Lead Pig'];

    $scope.openModal = function(templateName, object){
        var modalData = {};
        modalData.pi = $scope.pi;
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: templateName+'.html',
          controller: 'PiDetailModalCtrl'
        });
    }

  })
  .controller('PiDetailModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        if(!$scope.modalData.AuthorizationCopy){
            $scope.modalData.AuthorizationCopy = {
                Class: 'Authorization',
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Isotope:{},
                Isotope_id: null,
                Is_active: true
            }
        }

        if(!$scope.modalData.PurchaseOrderCopy){
            $scope.modalData.PurchaseOrderCopy = {
                Class: 'PurchaseOrder',
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Purchase_order_number:null,
                Is_active: true
            }
        }

        if(!$scope.modalData.ParcelCopy){
            $scope.modalData.ParcelCopy = {
                Class: 'Parcel',
                Purchase_order:null,
                Purchase_order_id:null,
                Status:'Pre-order',
                Isotope:null,
                Isotope_id:null,
                Arrival_date:null,
                Is_active: true,
                Principal_investigator_id: $scope.modalData.pi.Key_id
            }
        }

        if(!$scope.modalData.SolidsContainerCopy){
            $scope.modalData.SolidsContainerCopy = {
                Class: 'SolidsContainer',
                Room_id:null,
                Principal_investigator_id:$scope.modalData.pi.Key_id,
                Is_active: true
            }
        }

        var isotopePromise = af.getAllIsotopes()
            .then(
                function(){
                    $scope.isotopes = af.getCachedCollection('Isotope');
                },
                function(){
                    $rootScope.error = "There was a problem retrieving the list of all isotopes.  Please check your internet connection and try again."
                }
            )
        $scope.carboys = af.getCachedCollection('CarboyUseCycle');

        $scope.selectIsotope = function(isotope){
            if($scope.modalData.AuthorizationCopy)$scope.modalData.AuthorizationCopy.Isotope_id = $scope.modalData.AuthorizationCopy.Isotope.Key_id;
            if($scope.modalData.ParcelCopy)$scope.modalData.ParcelCopy.Isotope_id = $scope.modalData.ParcelCopy.Isotope.Key_id;
        }

        $scope.selectPO = function(po){
            if($scope.modalData.ParcelCopy)$scope.modalData.ParcelCopy.Purchase_order_id = $scope.modalData.ParcelCopy.Purchase_order.Key_id;
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

        $scope.saveAuthorization = function(pi, copy, auth){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.saveAuthorization( pi, copy, auth )
        }

        $scope.saveParcel = function(pi, copy, parcel){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.saveParcel( pi, copy, parcel )
        }


        $scope.savePO = function(pi, copy, po){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.savePurchaseOrder( pi, copy, po )
        }

        $scope.saveContainer = function(pi, copy, container){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.saveSolidsContainer( pi, copy, container )
        }

        $scope.saveCarboy = function(pi, copy, carboy){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.saveCarboy( pi, copy, carboy )
        }

        $scope.markAsArrived = function(pi, copy, parcel){
            copy.Status = "Arrived";
            copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
            $scope.saveParcel(pi, copy, parcel);
        }

        $scope.addCarboyToLab = function(cycle, pi, room){
            cycle.Is_active = false;
            $modalInstance.dismiss();
            af.deleteModalData();
            af.addCarboyToLab(cycle, pi, room)
        }

  }])
  
