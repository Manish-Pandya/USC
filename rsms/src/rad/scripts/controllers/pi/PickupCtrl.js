'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PickupCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
          var af = actionFunctionsFactory;
          $scope.af = af;
          $rootScope.piPromise = af.getRadPIById($stateParams.pi)
              .then(
                  function(pi){
                      console.log(pi);
                      console.log(dataStore);
                      //pi.loadRooms();
                      if(pi.Pickups){
                          var i = pi.Pickups.length;
                          $scope.scheduledPickups = [];
                          while(i--){
                            if(!pi.Pickups[i].Pickup_date){
                                $scope.scheduledPickups.unshift(pi.Pickups[i]);
                            };
                          }
                      }
                    $scope.pi = pi;
                  },
                  function(){}
              )


       $scope.svTrays = 0;


        $scope.createPickup = function(pi){
            //collection of things to be picked up
            if(pi.Pickups.length){
                var i = pi.Pickups.length;
                while(i--){
                    if(pi.Pickups[i].Status == Constants.PICKUP.STATUS.REQUESTED)var pickup = pi.Pickups[i];
                }
            }

            if(!pickup){
                var pickup = new window.Pickup();
                pickup.Is_active = true;
                pickup.Class="Pickup";
                pickup.Carboy_use_cycles = [];
                pickup.Scint_vial_collections = [];
                pickup.Waste_bags = [];
                pickup.Principal_investigator_id = null;
                pickup.Requested_date = convenienceMethods.setMysqlTime(Date());
                pickup.Status = Constants.PICKUP.STATUS.REQUESTED;
                pickup.Principal_investigator_id = pi.Key_id;
            }


            //include proper objects in pickup
            if(pi.SolidsContainers){
                var i = pi.SolidsContainers.length;
                while(i--){
                    var container = pi.SolidsContainers[i];
                    var j =  container.WasteBagsForPickup.length;
                    while(j--){
                        if( container.include && !convenienceMethods.arrayContainsObject(pickup.Waste_bags, container.WasteBagsForPickup[j]))pickup.Waste_bags.push( container.WasteBagsForPickup[j] );
                    }
                }
            }

            if(pi.CurrentScintVialCollections){
                var i = pi.CurrentScintVialCollections.length;
                while(i--){
                    pickup.Scint_vial_trays = pi.CurrentScintVialCollections[i].svTrays;
                    if( pi.CurrentScintVialCollections[i].include && !convenienceMethods.arrayContainsObject(pickup.Scint_vial_collections, pi.CurrentScintVialCollections[i]) ) pickup.Scint_vial_collections.push( pi.CurrentScintVialCollections[i] );
                }
            }

            if(pi.CarboyUseCycles){
                var i = pi.CarboyUseCycles.length;
                while(i--){
                    if( pi.CarboyUseCycles[i].include && !convenienceMethods.arrayContainsObject(pickup.Carboy_use_cycles, pi.CarboyUseCycles[i])  )pickup.Carboy_use_cycles.push( pi.CarboyUseCycles[i] );
                }
                var modalData = {};
                modalData.pi = pi;
                modalData.pickup = pickup;
                af.setModalData(modalData);
                var modalInstance = $modal.open({
                  templateUrl: 'views/pi/pi-modals/pickup-modal.html',
                  controller: 'PickupModalCtrl'
                });
            }

        }

        $scope.solidsContainersHavePickups = function(containers){
            if(!containers)return false;
            var i = containers.length;
            while(i--){
                //if(!containers[i].WasteBagsForPickup.length)return false;
                if($scope.hasPickupItems(containers[i].WasteBagsForPickup))return true;
            }
            return false;
        }


        $scope.hasPickupItems = function(collection){
            //if(!collection.length)return false;
            var hasPickupItems = false;
            if(!collection)return false;
            var i = collection.length;
            while(i--){
                // TODO: Should collection[i].Contents ever be null? Had to add null check here before getting length, because it's null sometimes.
                if(!collection[i].Pickup_id && collection[i].Contents && collection[i].Contents.length){
                    hasPickupItems = true;
                }

            }
            return hasPickupItems;
        }

  })
  .controller('PickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
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

        $scope.requestPickup = function(pickup){
            $scope.close();
            var pickupCopy = dataStoreManager.createCopy(pickup);
            af.savePickup(pickup,pickupCopy,true)
                .then(
                    function(){

                    },
                    function(){

                    }
                )
        }


        $scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
        }

    });
