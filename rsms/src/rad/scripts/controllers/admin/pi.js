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
        return actionFunctionsFactory.getRadPIById($stateParams.pi)
                .then(
                    function(pi){
                       // pi = new window.PrincipalInvestigator();
                        pi.loadUser();
                        pi.loadRooms();
                        pi.loadActiveParcels();
                        pi.loadPurchaseOrders();
                        pi.loadPIAuthorizations();
                        pi.loadCarboyUseCycles();
                        pi.loadSolidsContainers();
                        $rootScope.pi = pi;
                        //$scope.getHighestAmendmentNumber($scope.mappedAmendments);
                        return pi;
                    },
                    function(){
                    }
                );
    }

    $rootScope.radPromise = af.getRadModels()
                                .then(getRadPi);


    $scope.onSelectPi = function (pi) {
        $state.go('.pi-detail',{pi:pi.Key_id});
    }

    $scope.selectAmendement = function (num) {
        console.log(num);
        $scope.mappedAmendments.forEach(function (a) {
            if (a.weight == num) {
                $scope.selectedPiAuth = a;
                return;
            }
        })
    }

    $scope.openModal = function (templateName, object, isAmendment) {

        var modalData = {};
        modalData.pi = $scope.pi;
        modalData.isAmendment = isAmendment || false;
        if (object) modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: templateName+'.html',
          controller: 'PiDetailModalCtrl'
        });

        modalInstance.result.then(function (thing) {
            $scope.getHighestAmendmentNumber($rootScope.pi.Pi_authorization);
        })
    }

    $scope.getHighestAmendmentNumber = function (amendments) {
        if (!amendments)  return;
               
        var highestAuthNumber = 0;
        _.sortBy(amendments, [function (amendment) {
            return moment(amendment.Approval_date).valueOf();
        }]);
        for (var i = 0; i < amendments.length; i++) {
            var amendment = amendments[i];
            convenienceMethods.dateToIso(amendment.Approval_date, amendment, "Approval_date", true);
            convenienceMethods.dateToIso(amendment.Termination_date, amendment, "Termination_date", true);
            amendment.Amendment_label = amendment.Amendment_number ? "Amendment " + amendment.Amendment_number : "Original Authorization";
            amendment.Amendment_label = amendment.Termination_date ? amendment.Amendment_label + " (Terminated " + amendment.view_Termination_date + ")" : amendment.Amendment_label + " (" + amendment.view_Approval_date + ")";
            amendment.weight = i;
            console.log(i);
        }

        $scope.mappedAmendments = amendments;

        $scope.selectedPiAuth = $scope.mappedAmendments[amendments.length - 1];
        $scope.selectedAmendment = amendments.length - 1;
        return $scope.selectedAmendment;
        
    }

    $scope.openAuthModal = function (templateName, piAuth, auth) {
        var modalData = {};
        modalData.pi = $scope.pi;
        if (piAuth) modalData[piAuth.Class] = piAuth;
        if (auth) modalData[auth.Class] = auth;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: templateName + '.html',
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
        var copy = new window.Parcel();
        angular.extend(copy, parcel);
        copy.Status = Constants.PARCEL.STATUS.DELIVERED;
        copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
        af.saveParcel( copy, parcel, pi )
    }

    $scope.reopenAuth = function (piAuth) {
        var copy = new window.PIAuthorization();
        angular.extend(copy, piAuth);
 
        copy.Termination_date = null;
        for (var n = 0; n < copy.Authorizations; n++) {
            copy.Authorizations[n].Is_active = true;
        }
        af.savePIAuthorization(copy, piAuth, $scope.pi);
        
    }

    
  })
  .controller('PiDetailModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        $scope.getHighestAuth = function (pi) {
            if (pi.Pi_authorization && pi.Pi_authorization.length) {
                var auths = _.sortBy(pi.Pi_authorization, [function (amendment) {
                    return moment(amendment.Approval_date).valueOf();
                }]);

                return auths[auths.length - 1];
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

        if (!$scope.modalData.PIAuthorizationCopy) {
            $scope.modalData.PIAuthorizationCopy = {
                Class: 'PIAuthorization',
                Rooms: [],
                Authorization_number: null,
                Is_active: true,
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Authorizations: []
            }
        }

        $scope.getApprovalDate = function (a, isAmendment) {
            if (isAmendment) {
                return "";
            }
            return a.view_Approval_date;
        }

        if(!$scope.modalData.AuthorizationCopy){
            $scope.modalData.AuthorizationCopy = {
                Class: 'Authorization',
                Principal_investigator_id: $scope.modalData.pi.Key_id,
                Isotope:{},
                Isotope_id: null,
                Is_active: true,
                Pi_authorization_id: $scope.modalData.PIAuthorizationCopy ? $scope.modalData.PIAuthorizationCopy.Key_id : null
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

        $scope.getTerminationDate = function (piAuth) {
            if (piAuth.Termination_date) piAuth.Form_Termination_date = convenienceMethods.dateToIso(piAuth.Termination_date);
        }

        $scope.carboys = af.getCachedCollection('CarboyUseCycle');

        $scope.selectIsotope = function (auth) {
            auth.Isotope = dataStoreManager.getById("Isotope", auth.Isotope_id);
            if ($scope.modalData.AuthorizationCopy && $scope.modalData.AuthorizationCopy.Isotope) {
                $scope.modalData.AuthorizationCopy.Isotope_id = $scope.modalData.AuthorizationCopy.Isotope.Key_id;
                if ($scope.modalData.ParcelCopy && $scope.modalData.ParcelCopy.Isotope) $scope.modalData.ParcelCopy.Isotope_id = $scope.modalData.ParcelCopy.Isotope.Key_id;
            }
        }

        $scope.selectPO = function(po){
            if($scope.modalData.ParcelCopy)$scope.modalData.ParcelCopy.PurchaseOrderrder = dataStoreManager.getById("PurchaseOrder",$scope.modalData.ParcelCopy.Purchase_order_id);
        }

        $scope.selectAuth = function(po){
            if($scope.modalData.ParcelCopy)$scope.modalData.ParcelCopy.Authorization = dataStoreManager.getById("Authorization",$scope.modalData.ParcelCopy.Authorization_id)
        }

        $scope.addIsotope = function (id) {
            var newAuth = new Authorization();
            newAuth.Class = "Authorization";
            newAuth.Pi_authorization_id = id;
            newAuth.Is_active = newAuth.isIncluded = true;
            newAuth.Isotope = new Isotope();
            newAuth.Isotope.Class = "Isotope";
            $scope.modalData.PIAuthorizationCopy.Authorizations.push(newAuth);
        }

        $scope.close = function(auth){
            af.deleteModalData();
            if (auth) {
                var i = auth.Authorizations.length;
                while (i--) {
                    var is = auth.Authorizations[i];
                    if (!is.Key_id) auth.Authorizations.splice(i,1);
                }
            }

            $modalInstance.dismiss();
        }

        $scope.savePIAuthorization = function (copy, auth, terminated) {
            var pi = $scope.modalData.pi;
            if ($scope.modalData.isAmendment) copy.Key_id = null;
            copy.Approval_date = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.view_Approval_date));
            if (!terminated){
                for (var n = 0; n < copy.Authorizations; n++) {
                    if (!terminated && !copy.Authorizations[n].isIncluded) {
                        copy.Authorizations.splice(n, 1);
                    }
                }
            }else{
                copy.Is_active = false;
                copy.Termination_date = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.Form_Termination_date));
                for (var n = 0; n < copy.Authorizations; n++) {                    
                    copy.Authorizations[n].Is_active = false;                    
                }
            }
            af.savePIAuthorization(copy, auth, pi).then(function () {
                $modalInstance.close();
                af.deleteModalData();
            });
            
        }

        $scope.saveAuthorization = function (piAuth, copy, auth) {
            copy.Pi_authorization_id = copy.Pi_authorization_id || pi.Pi_authorization.Key_id;
            $modalInstance.dismiss();
            af.deleteModalData();
            af.saveAuthorization(piAuth, copy, auth)
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

        $scope.addCarboyToLab = function (cycle, pi) {
            console.log(cycle);
            //cycle.loadCarboy();
            cycle.Is_active = false;
            $modalInstance.dismiss();
            var cycleCopy = {
                Class: "CarboyUseCycle",
                Room_id: cycle.Room.Key_id,
                Principal_investigator_id: pi.Key_id,
                Key_id: cycle.Key_id || null,
                Carboy_id: cycle.Carboy_id
            }
            console.log(cycleCopy);
            af.deleteModalData();
            af.addCarboyToLab(cycleCopy,pi);
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

        $scope.getSuggestedAmendmentNumber = function (pi) {
            //get a suggetion for amendment number
            $scope.suggestedAmendmentNumber;
            var i = $scope.modalData.PIAuthorizationCopy.Authorizations.length;
            var gapFound = false;
            if (i > 1) {
                while (i--) {
                    if (!$scope.modalData.PIAuthorizationCopy.Authorizations[i]) {
                        gapFound = true;
                        break;
                    }
                }
                if (gapFound) {
                    $scope.suggestedAmendmentNumber = i;
                } else {
                    $scope.suggestedAmendmentNumber = $scope.modalData.PIAuthorizationCopy.Authorizations.length;
                }
            } else {
                $scope.suggestedAmendmentNumber = $scope.modalData.PIAuthorizationCopy.Authorizations.length;
            }
            return $scope.suggestedAmendmentNumber
        }

  }])

