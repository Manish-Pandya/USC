'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PiDetailCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;

    var getRadPi = function(){
        var pi = af.getById("PrincipalInvestigator",$stateParams.pi);
        return actionFunctionsFactory.getRadPI(pi)
                .then(
                    function(){
                        $rootScope.pi = pi;
                        console.log(pi);
                        return pi;
                    },
                    function(){
                    }
                );
    }
    //get the all the pis
    $rootScope.pisPromise
        .then(
            function(){
                $rootScope.piPromise = actionFunctionsFactory.getAllPIs()
                    .then(getRadPi);
                }
            )


    $scope.onSelectPi = function (pi)
    {
        $state.go('.pi-detail',{pi:pi.Key_id});
    }

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

    $scope.openWipeTestModal = function(parcel){
        var modalData = {};
        modalData.pi = $scope.pi;
        modalData.Parcel = parcel;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/admin/admin-modals/package-wipe-test.html',
          controller: 'WipeTestModalCtrl'
        });
    }

    $scope.markAsArrived = function(pi, parcel){
        var copy = new window.Parcel;
        angular.extend(copy, parcel);
        copy.Status = Constants.PARCEL.STATUS.DELIVERED;
        copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
        af.saveParcel( copy, parcel, pi )
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
                Status:'Ordered',
                Isotope:null,
                Isotope_id:null,
                Arrival_date:null,
                Is_active: true,
                Principal_investigator_id: $scope.modalData.pi.Key_id
            }
        }

        if(!$scope.modalData.PIAuthorizationCopy){
            $scope.modalData.PIAuthorizationCopy = {
                Class: 'PIAuthorization',
                Rooms:null,
                Authorization_number: null,
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

        $scope.savePIAuthorization = function( copy, auth ){
            var pi = $scope.modalData.pi;
            $modalInstance.dismiss();
            af.deleteModalData();
            af.savePIAuthorization(copy, auth, pi);
        }

        $scope.saveAuthorization = function (pi, copy, auth) {
            $modalInstance.dismiss();
            af.deleteModalData();
            af.saveAuthorization(pi, copy, auth)
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
            copy.Status = Constants.PARCEL.STATUS.ARRIVED;
            copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
            $scope.saveParcel(pi, copy, parcel);
        }

        $scope.addCarboyToLab = function(cycle, pi){
            cycle.loadCarboy();
            cycle.Is_active = false;
            $modalInstance.dismiss();
            af.deleteModalData();
            af.addCarboyToLab(cycle, pi);
        }

        $scope.roomIsAuthorized = function(room, authorization){
            room.isAuthorized = false;
            if(!authorization.Rooms && authorization.Key_id)return;
            if(authorization.Rooms){
                var i = authorization.Rooms.length;
                while(i--){
                    if(authorization.Rooms[i].Key_id == room.Key_id){
                        return true;
                    }
                }
                return false;
            }else{
                return true;
            }
            return false;
        }

        $scope.departmentIsAuthorized = function(department, authorization){
            department.isAuthorized = false;
            if(!authorization.Departments && authorization.Key_id)return;
            if(authorization.Departments){
                var i = authorization.Departments.length;
                while(i--){
                    if(authorization.Departments[i].Key_id == department.Key_id){
                        return true;
                    }
                }
                return false;
            }else{
                return true;
            }
            return false;
        }
/*
        $scope.checkboxChange = function(thing, authorization){
            if(thing.isAuthorized){
                if(!authorization[thing.Class+'s']){
                    authorization[thing.Class+'s'] = [thing];
                }else{
                    var thingFound = false;
                    var i = authorization[thing.Class+'s'].length;
                    while(i--){
                        if(authorization[thing.Class+'s'][i].Key_id == thing.Key_id){
                            thingFound = true;
                        }
                    }
                    if(!thingFound){
                        authorization[thing.Class+'s'].push(thing)
                    }
                }
            }else{
                var thingFound = false;
                var i = authorization[thing.Class+'s'].length;
                while(i--){
                    if(authorization[thing.Class+'s'][i].Key_id == thing.Key_id){
                        authorization[thing.Class+'s'].splice(i,1);
                        thing.isAuthorized = false;
                    }
                }
            }
        }
        */

  }])

