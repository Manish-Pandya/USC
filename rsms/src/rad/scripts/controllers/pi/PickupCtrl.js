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
                  function (pi) {
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
                      var i = pi.SolidsContainers.length;
                      while (i--) {
                          pi.SolidsContainers[i].loadWasteBagsForPickup();
                          pi.SolidsContainers[i].loadCurrentWasteBags();
                      }
                    $scope.pi = pi;
                  },
                  function(){}
              )
          $scope.solidsContainerHasPickups = function (container) {
              if (!container) return false;

              if ($scope.hasPickupItems(container.WasteBagsForPickup)) return true;

              return false;
          }


          $scope.hasPickupItems = function (collection) {
              if (!collection || !collection.length) return false;
              var hasPickupItems = false;
              if (!collection) return false;
              var i = collection.length;
              while (i--) {
                  // TODO: Should collection[i].Contents ever be null? Had to add null check here before getting length, because it's null sometimes.
                  if (!collection[i].Pickup_id && collection[i].Class == "WasteBag" || (!collection[i].Pickup_id && collection[i].Contents && collection[i].Contents.length)) {
                      hasPickupItems = true;
                  }

              }
              return hasPickupItems;
          }

          $scope.setSVCollection = function (pi) {
              if (pi.CurrentScintVialCollections && pi.CurrentScintVialCollections.length) return;              
              var collection = new window.ScintVialCollection();
              collection.Principal_investigator_id = pi.Key_id;
              collection.new = true;
              pi.CurrentScintVialCollections = [collection];
              console.log(pi);
          }

        $scope.svTrays = 0;

        $scope.wasteInContainersScheduledScheduled = function (containers) {
            var i = containers.length;
            while (i--) {
                if (containers[i].Pickup_id) return true;
            }
            return false;
        }

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
            if (pi.SolidsContainers) {
                var i = pi.SolidsContainers.length;
                while(i--){
                    var container = pi.SolidsContainers[i];
                    var j =  container.WasteBagsForPickup.length;
                    while(j--){
                        if (container.include && !convenienceMethods.arrayContainsObject(pickup.Waste_bags, container.WasteBagsForPickup[j])) {
                            pickup.Waste_bags.push(container.WasteBagsForPickup[j]);
                        }
                    }
                    //conditionally include the current waste bag
                    if (container.includeCurrentBag && !convenienceMethods.arrayContainsObject(pickup.Waste_bags, container.CurrentWasteBags[0])) {
                        pickup.Waste_bags.push(container.CurrentWasteBags[0]);
                    }
                }
            }

            if(pi.CurrentScintVialCollections){
                var i = pi.CurrentScintVialCollections.length;
                pickup.Scint_vial_trays = 0;
                while(i--){
                    if( pi.CurrentScintVialCollections[i].include && !convenienceMethods.arrayContainsObject(pickup.Scint_vial_collections, pi.CurrentScintVialCollections[i]) ) pickup.Scint_vial_collections.push( pi.CurrentScintVialCollections[i] );

                    if (pi.CurrentScintVialCollections[i].svTrays) {
                        pickup.Scint_vial_trays = parseInt(pickup.Scint_vial_trays) + parseInt(pi.CurrentScintVialCollections[i].svTrays);
                    }
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

  })
  .controller('PickupModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance, convenienceMethods) {
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
            //var pickupCopy = dataStoreManager.createCopy(pickup);

            var pickupCopy = {
                Class: "Pickup",
                Key_id: pickup.Key_id || null,
                Scint_vial_collections: pickup.Scint_vial_collections,
                Waste_bags: pickup.Waste_bags,
                Status: pickup.Status,
                Principal_investigator_id: pickup.Principal_investigator_id,
                Scint_vial_trays: pickup.Scint_vial_trays,
                Requested_date: convenienceMethods.setMysqlTime(new Date())
            }

            pickupCopy.Carboy_use_cycles = [];
            var i = pickup.Carboy_use_cycles.length;
            while (i--) {
                var originalCycle = pickup.Carboy_use_cycles[i];
                var cycle = new CarboyUseCycle();
                for (var prop in originalCycle) {
                    if (typeof originalCycle[prop] != "object" && typeof originalCycle[prop] != "array") {
                        cycle[prop] = originalCycle[prop];
                    }
                }
                pickupCopy.Carboy_use_cycles[i] = cycle;
            }

            af.savePickup(pickup, pickupCopy, true);
        }


        $scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
        }

    });
