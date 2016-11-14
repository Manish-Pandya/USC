'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('OrdersCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
          var af = actionFunctionsFactory;
          $scope.af = af;
          $scope.constants = Constants;
          $rootScope.parcelPromise = af.getAllIsotopes()
                                        .then(getPI);

          var getPI =  af.getRadPIById($stateParams.pi)
              .then(
                  function(pi){
                    console.log(pi);
                    $scope.pi = pi;
                  },
                  function(){}
              )

        $scope.openModal = function(object){
            var modalData = {};
            modalData.pi = $scope.pi;
            if(object)modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
              templateUrl: 'views/pi/pi-modals/orders-modal.html',
              controller: 'OrderModalCtrl'
            });
        }

  })
  .controller('OrderModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
        var af = actionFunctionsFactory;
        $scope.constants = Constants;
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

        if(!$scope.modalData.ParcelCopy){
            $scope.modalData.ParcelCopy = {
                Class: 'Parcel',
                Is_active: true,
                Status: Constants.PARCEL.STATUS.REQUESTED,
                Principal_investigator_id: $scope.modalData.pi.Key_id
            }
        }

        $scope.selectRoom = function(){
            $scope.modalData.ParcelCopy.Room_id = $scope.modalData.ParcelCopy.Room.Key_id;
        }

        $scope.checkMaxOrder = function (parcel) {
            $scope.quantityExceeded = false;
            var pi = $scope.modalData.pi;
            var i = pi.CurrentIsotopeInventories.length;
            while (i--) {
                if (pi.CurrentIsotopeInventories[i].Authorization_id == parcel.Authorization_id) {
                    console.log(pi.CurrentIsotopeInventories[i].Max_order);
                    if (parseFloat(pi.CurrentIsotopeInventories[i].Max_order) < parseFloat(parcel.Quantity)) {
                        $scope.relevantInventory = pi.CurrentIsotopeInventories[i];
                        return false;
                    } else {
                        return true;
                    }
                }
            }
            return true;
        }

        $scope.saveParcel = function(pi, copy, parcel){
           af.deleteModalData();
           af.saveParcel( pi, copy, parcel ).
                            then(
                                function(){
                                   $scope.close();
                                }
                            )
        }

        $scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
        }

    });
