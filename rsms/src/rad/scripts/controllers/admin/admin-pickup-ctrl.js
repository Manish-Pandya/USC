'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('AdminPickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
          var af = actionFunctionsFactory;
          var getAllPickups = function(){
              af.getAllPickups()
              .then(
                  function(pickups){
                      console.log(pickups);
                      $scope.pickups = pickups;
                      console.log(dataStore);
                  },
                  function(){}
              )
          }

          $scope.af = af;
          $rootScope.pickupsPromise = af.getRadModels()
                                        .then(
                                            function () {
                                                var pickups = dataStoreManager.get("Pickup");
                                                for (var i = 0; i < pickups.length; i++) {
                                                    if (pickups[i].Status == Constants.PICKUP.STATUS.PICKED_UP
                                                        || ( pickups[i].Status == Constants.PICKUP.STATUS.REQUESTED && pickups[i].Waste_bags.length )
                                                        || pickups[i].Scint_vial_collections.length
                                                        || pickups[i].Carboy_use_cycles.length) {
                                                            pickups[i].loadCarboyUseCycles();
                                                            pickups[i].loadWasteBags();
                                                            pickups[i].loadCurrentScintVialCollections();
                                                    }
                                                }
                                                
                                                $scope.pickups = dataStoreManager.get("Pickup");
                                            }
                                        )

        $scope.setStatusAndSave = function(pickup, oldStatus, isChecked){
            console.log(status);
            console.log(isChecked);
            isChecked = !isChecked;
            console.log(isChecked);

            var pickupCopy = dataStoreManager.createCopy(pickup);

            if(isChecked == true){
                pickupCopy.Status = oldStatus;
            }else{
                if(oldStatus == Constants.PICKUP.STATUS.PICKED_UP){
                    pickupCopy.Status = null;
                }else{
                    pickupCopy.Status = Constants.PICKUP.STATUS.PICKED_UP;
                }
            }

            af.savePickup(pickup,pickupCopy, true);

        }



  })
  .controller('AdminPickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
        var af = actionFunctionsFactory;
        $scope.af = af;

        $scope.modalData = af.getModalData();

        if(!$scope.modalData.SolidsContainerCopy){
            $scope.modalData.SolidsContainerCopy = {
                Class: 'SolidsContainer',
                Room_id:null,
                Is_active: true
            }
        }

        $scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
        }

    });
