'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('AuthCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
      //do we have access to action functions?
      var af = actionFunctionsFactory;
      $scope.af = af;

      var getRadPi = function () {
          return actionFunctionsFactory.getRadPIById($stateParams.pi)
                  .then(
                      function (pi) {                             
                          $rootScope.pi = pi;
                          return pi
                      },
                      function () {
                      }
                  )
                    .then(function (pi) {
                        return pi.loadPIAuthorizations().then(
                              function () {
                                  var auth = $rootScope.getHighestAuth(pi);
                                  auth.Amendment_label = auth.Amendment_number ? "Amendment " + auth.Amendment_number : "Original Authorization";
                                  auth.weight = parseInt(auth.Amendment_number || "0");
                                  return auth;
                              }
                          );
                    })
                    .then(function (auth) {
                      $scope.roomsLoading = auth.loadRooms().then(function () { auth.loadDepartments() });
                      return $scope.selectedPiAuth = auth;
                  });
      }

      $rootScope.parcelPromise = getRadPi();


  })
'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # InventoryViewCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InventoryViewCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
      var af = actionFunctionsFactory;
      $scope.af = af;
      $scope.constants = Constants;

      $rootScope.loading  = af.getRadPIById($stateParams.pi)
          .then(
              function (pi) {
                  console.log(pi);
                  $scope.pi = pi;
              },
              function () { }
          );     

  })

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

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('ParcelUseLogCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {

          var af = actionFunctionsFactory;
          $scope.af = af;
          $scope.constants = Constants;
          af.clearError();

          var getPi = function () {

          return af.getRadPIById($stateParams.pi)
                .then(
                    function(){
                          $rootScope.pi = dataStoreManager.getById('PrincipalInvestigator', $stateParams.pi);
                          var i = $rootScope.pi.ActiveParcels.length;
                          while(i--){
                              var parcel = dataStoreManager.getById("Parcel", $rootScope.pi.ActiveParcels.Key_id);
                              if (parcel) parcel.Authorization = $rootScope.pi.ActiveParcels.Authorization;
                          }

                          return $rootScope.pi;
                    },
                    function(){}
                )
      }

      var getParcel = function(){
        return af.getParcelById($stateParams.parcel)
            .then(
                function(){
                    $rootScope.parcel = dataStoreManager.getById("Parcel", $stateParams.parcel);
                    $rootScope.parcel.loadUses();
                    return $rootScope.parcel;
                }
            );
      }

      $scope.parcelPromise = getParcel()
                              .then(getPi);

      $scope.addUsage = function (parcel) {
          if(!$scope.parcel.ParcelUses)$scope.parcel.ParcelUses = [];
          var i = $scope.parcel.ParcelUses.length;
          while(i--){
            $scope.parcel.ParcelUses[i].edit=false
          }
          $rootScope.ParcelUseCopy = {};
          $rootScope.ParcelUseCopy = new window.ParcelUse();
          $rootScope.ParcelUseCopy.Parcel_id = $scope.parcel.Key_id;
          $rootScope.ParcelUseCopy.ParcelUseAmounts = [];
          $rootScope.ParcelUseCopy.Class = "ParcelUse";
          var solidUsageAmount = new window.ParcelUseAmount();
          var liquidUsageAmount = new window.ParcelUseAmount();
          var vialUsageAmount = new window.ParcelUseAmount();
          var otherUsageAmount = new window.ParcelUseAmount();

          solidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.SOLID;
          liquidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
          vialUsageAmount.Waste_type_id = Constants.WASTE_TYPE.VIAL;
          otherUsageAmount.Waste_type_id = Constants.WASTE_TYPE.OTHER;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(otherUsageAmount);

          parcel.ParcelUses.unshift($rootScope.ParcelUseCopy);
          $scope.editUse($rootScope.ParcelUseCopy);
      }

      $scope.editUse = function (use) {
          var parcelUseHasUseAmountType = function (use, typeId) {
              var i = use.ParcelUseAmounts.length;
              while (i--) {
                  var amt = use.ParcelUseAmounts[i];
                  if (amt.Waste_type_id == typeId) return true;
              }
              return false;
          }
          var i = $scope.parcel.ParcelUses.length;
          while(i--){
            $scope.parcel.ParcelUses[i].edit=false
          }

          $rootScope.ParcelUseCopy = {}
          $rootScope.use = use;
          af.createCopy(use);
          
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.SOLID)) {
            var solidUsageAmount = new window.ParcelUseAmount();
            solidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.SOLID;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(solidUsageAmount);
          }
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.LIQUID)) {
            var liquidUsageAmount = new window.ParcelUseAmount();
            liquidUsageAmount.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(liquidUsageAmount);
          }
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.VIAL)) {
            var vialUsageAmount = new window.ParcelUseAmount();
            vialUsageAmount.Waste_type_id = Constants.WASTE_TYPE.VIAL;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(vialUsageAmount);
          }
          if (!parcelUseHasUseAmountType($rootScope.ParcelUseCopy, Constants.WASTE_TYPE.OTHER)) {
            var otherUsageAmount = new window.ParcelUseAmount();
            otherUsageAmount.Waste_type_id = Constants.WASTE_TYPE.OTHER;
            $rootScope.ParcelUseCopy.ParcelUseAmounts.push(otherUsageAmount);
          }

          var i = use.ParcelUseAmounts.length;
          while (i--) {
              if (use.ParcelUseAmounts[i].Carboy_id) console.log(use.ParcelUseAmounts[i].Carboy);
              use.ParcelUseAmounts[i].OldQuantity = use.ParcelUseAmounts[i].Curie_level;
          }

          var modalInstance = $modal.open({
              templateUrl: 'views/pi/pi-modals/parcel-use-log-modal.html',
              controller: 'ModalParcelUseLogCtrl'
          });

      }  
 });
angular.module('00RsmsAngularOrmApp')
  .controller('ModalParcelUseLogCtrl', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory) {
      var af = actionFunctionsFactory;
      $scope.af = af;
      $scope.addAmount = function (type) {
          var amt = new window.ParcelUseAmount();
          if (type == "Solids") amt.Waste_type_id = Constants.WASTE_TYPE.SOLID;
          if (type == "Liquids") amt.Waste_type_id = Constants.WASTE_TYPE.LIQUID;
          if (type == "Vials") amt.Waste_type_id = Constants.WASTE_TYPE.VIAL;
          if (type == "Others") amt.Waste_type_id = Constants.WASTE_TYPE.OTHER;

          $rootScope.ParcelUseCopy.ParcelUseAmounts.push(amt);
          $rootScope.ParcelUseCopy[type].push(amt);
      }

      $scope.selectContainer = function (useAmount) {
          console.log(useAmount);
          if (!useAmount.Waste_bag) {
              useAmount.Waste_bag_id = null;
          } else {
              useAmount.Waste_bag_id = useAmount.Waste_bag.Key_id;
          }
      }
      $scope.selectedBag = function (solid, bags) {
          var i = bags.length;
          while (i--) {
              if (bags[i].Key_id == solid.Waste_bag_id) solid.Waste_bag = bags[i];
          }
      }
      $scope.close = function (use, parcel) {
          if (!use || !use.Key_id) $scope.parcel.ParcelUses.shift();
          use.edit = false;
          use.error = false;
          $rootScope.ParcelUseCopy = {};

          var i = use.ParcelUseAmounts.length;
          while (i--) {
              use.ParcelUseAmounts[i].Curie_level = use.ParcelUseAmounts[i].OldQuantity;
          }
          parcel.edit = false;
          $modalInstance.dismiss();
      }

      $scope.validateRemainder = function (parcel, copy, use) {
          use.error = null;
          var uses = parcel.ParcelUses;
          var valid = true;
          var total = 0;
          var i = uses.length;
          while (i--) {
              total += parseFloat(uses[i].Quantity);
          }

          total += parseFloat(copy.Quantity);
          //if we are editing, subtract the total from the copied use so that it's total isn't included twice
          if (use.Quantity) {
              total = total - parseFloat(use.Quantity);
          }

          if (total > parseFloat(parcel.Quantity)) {
              valid = false;
              use.error = 'Total usages must not be more than remaining package quantity.';
          }
          return valid;

      }

      $scope.saveParcelUse = function (parcel, copy, use) {
          if ($scope.validateUseAmounts(copy, use) && $scope.validateRemainder(parcel, copy, use)) {
              af.saveParcelUse(parcel, copy, use).then(function () {
                  $modalInstance.close();
              });
          }
      }

      //this is here specifically because form validation seems like it belongs in the controller (VM) layer rather than the CONTROLLER(actionFunctions layer) of this application,
      //which if you think about it, has sort of become an MVCVM
      $scope.validateUseAmounts = function (use, orig) {
          use.error = null;
          use.isValid = false;
          var total = 0;

          var i = use.ParcelUseAmounts.length;
          while (i--) {
              if (use.ParcelUseAmounts[i].Curie_level) total = total + parseFloat(use.ParcelUseAmounts[i].Curie_level);
          }
          total = Math.round(total * 100000) / 100000;

          if (parseFloat(use.Quantity) == total) {
              use.isValid = true;
          } else {
              orig.error = 'Total disposal amount must equal use amount.';
          }
          return use.isValid;
      }

      var parcelUseHasUseAmountType = function (use, typeId) {
          var i = use.ParcelUseAmounts.length;
          while (i--) {
              var amt = use.ParcelUseAmounts[i];
              if (amt.Waste_type_id == typeId) return true;
          }
          return false;
      }
  })

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
              $scope.CurrentScintVialCollections = [collection];
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

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('PiRadHomeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.piPromise = af.getRadPIById($stateParams.pi)
  			.then(
  				function(pi){
                    console.log(pi);
  					$scope.pi = pi;
  				},
  				function(){}
  			)

  });
'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('PIWipeTestController', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.constants = Constants;

        var getPI = function (id) {
            return af.getRadPIById(id)
                .then(
                    function (pi) {
                        $scope.pi = pi;
                    },
                    function () { }
                )
        }

        $rootScope.piPromise = getPI($stateParams.pi)       

        $scope.editPIWipe = function (test, wipe) {
            $rootScope.PIWipeCopy = {}
            if (!test.PIWipes) test.PIWipes = [];
            var i = test.PIWipes.length;
            while (i--) {
                test.PIWipes[i].edit = false;
            }

            if (!wipe) {
                $rootScope.PIWipeCopy = new window.PIWipe();
                $rootScope.PIWipeCopy.Class = "PIWipe";
                $rootScope.PIWipeCopy.Is_active = true;
                $rootScope.PIWipeCopy.PI_wipe_test_id = test.Key_id
                $rootScope.PIWipeCopy.edit = true;
                test.PIWipes.unshift($rootScope.PIWipeCopy);
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.addPIWipe = function (test) {
            $scope.pi.WipeTests.forEach(function (w) {
                w.showWipes = false;
                w.adding = false;
            });
            if (!test.PIWipes) test.PIWipes = [];
            //all wipe tests must have a background wipe
            if (!test.PIWipes[0] || !test.PIWipes[0].Location || test.PIWipes[0].Location != "Background") {
                var bgWipe = new window.PIWipe();
                bgWipe.PI_wipe_test_id = test.Key_id;
                bgWipe.Class = "PIWipe";
                bgWipe.edit = true;
                bgWipe.Location = "Background";
                test.PIWipes.unshift(bgWipe);
            }

            var piWipe = new window.PIWipe();
            piWipe.PI_wipe_test_id = test.Key_id;
            piWipe.Class = "PIWipe";
            piWipe.edit = true;
            test.PIWipes.push(piWipe);
            test.showWipes = true;
            test.adding = true;
        }

        $scope.cancelPIWipes = function (test) {
            console.log(test);
            for (var x = 0; x < test.PIWipes.length; x++) {
                if (!test.PIWipes[x].Key_id) {
                    test.PIWipes.splice(x, 1);
                }
            }
            test.adding = false;
        }

        $scope.openModal = function (object) {     
            var modalData = {};
            modalData.PI = $scope.pi;

            if (object) modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/pi/pi-modals/pi-wipe-modal.html',
                controller: 'PIWipeTestModalCtrl'
            });
        }

    })
    .controller('PIWipeTestModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        if (!$scope.modalData.PIWipeTest) {
            $scope.modalData.PIWipeTest = new window.PIWipeTest();
            $scope.modalData.PIWipeTest.Class = "PIWipeTest";
            $scope.modalData.PIWipeTest.Is_active = true;
            $scope.modalData.PIWipeTest.Principal_investigator_id = $scope.modalData.PI.Key_id;

        }

        $scope.save = function (test) {
            af.savePIWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

    }])
    

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:ParcelUseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('QuarterlyInventoryCtrl', function (convenienceMethods, $scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {

  		var af = actionFunctionsFactory;
  		$scope.af = af;

  		var getPi = function(){
        return af.getRadPIById($stateParams.pi)
    			.then(
    				function(pi){
              $scope.pi = pi;
              return pi;
    				},
    				function(){}
    			)
      }

      var getInventory = function(pi){
        return af.getQuartleryInventory(pi.Key_id)
                  .then(
                    function (inventory) {
                        $scope.pi_inventory = inventory;
                        return inventory;
                    }
                  )
      }

      $scope.openModal = function(object){
        console.log(object);
        var modalData = {};
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/pi/pi-modals/confirm-inventory.html',
          controller: 'InventoryConfirmationModalCtrl'
        });
      }

      $rootScope.inventoryPromise = getPi()
                              .then(getInventory)
 })
 .controller('InventoryConfirmationModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);
        
        $scope.savePiQuarterlyInventory = function(inventory, copy){
            af.savePiQuarterlyInventory(inventory, copy)
                .then($scope.close);
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
;

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RecepticalCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste receptical/solids container view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('RecepticalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
  		var af = actionFunctionsFactory;
  		$scope.af = af;
  		$rootScope.piPromise = af.getRadPIById($stateParams.pi)
  			.then(
  				function(pi){
  					var i = pi.SolidsContainers.length;
  					while (i--) {
						pi.SolidsContainers[i].loadRoom();
  					}
  					pi.loadRooms();
					$scope.pi = pi;
  				},
  				function(){}
  			)

	    $scope.openModal = function(templateName, object){
	        var modalData = {};
	        modalData.pi = $scope.pi;
	        if(object)modalData[object.Class] = object;
	        af.setModalData(modalData);
	        var modalInstance = $modal.open({
	          templateUrl: templateName+'.html',
	          controller: 'RecepticalModalCtrl'
	        });
	    }

  })
  .controller('RecepticalModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
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

		$scope.selectRoom = function(){
			$scope.modalData.SolidsContainerCopy.Room_id = $scope.modalData.SolidsContainerCopy.Room.Key_id;
		}

		$scope.saveSolidsContainer = function(pi, copy, container){
           $modalInstance.dismiss();
           af.deleteModalData();
           af.saveSolidsContainer( pi, copy, container )
		}

		$scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
		}

	});

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:UseLogCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI Use Log
 */
angular.module('00RsmsAngularOrmApp')
  .controller('UseLogCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal) {
          var af = actionFunctionsFactory;
          $scope.af = af;
          $rootScope.piPromise = $scope.parcelPromise = af.getRadPIById($stateParams.pi)
              .then(
                  function(pi){
                      $scope.pi = dataStoreManager.getById("PrincipalInvestigator", $stateParams.pi);
                      console.log(dataStore);
                  },
                  function(){}
              )
 });