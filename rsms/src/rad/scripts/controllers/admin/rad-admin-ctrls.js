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

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
    .filter('authsFilter', function () {
        return function (auths, filterObj) {
            var filtered = auths.filter(function (a) {
                if (a.Termination_date) return false;
                if (!filterObj) return true;

                if (filterObj.piName && a.PiName.toLowerCase().indexOf(filterObj.piName.toLowerCase()) == -1) {
                    return false;
                }

                if (filterObj.department) {
                    if (!a.Departments.some(function (d) {
                       return d.Name.toLowerCase().indexOf(filterObj.department.toLowerCase()) != -1;
                    })) return  false;
                }

                if (filterObj.room) {
                    if (!a.Rooms.some(function (r) {
                        return r.Name.toLowerCase().indexOf(filterObj.room.toLowerCase()) != -1;
                    })) return  false;
                }

                if (filterObj.building) {
                    if(!a.Rooms.some(function (r) {
                        return r.Building.Name.toLowerCase().indexOf(filterObj.building.toLowerCase()) != -1;
                    })) return false;
                }

                return true;
            })
            return filtered;
        }
    })
  .controller('AuthReportCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
      var af = $scope.af = actionFunctionsFactory;
      if (!$rootScope.filterObj) $rootScope.filterObj = {showNew:false};
      var getAllPIAuthorizations = function () {
          af.getAllPIAuthorizations()
          .then(
              function (piAuths) {
                  $rootScope.piAuths = [];
                  var piAuths = _.groupBy(dataStore.PIAuthorization, 'Principal_investigator_id');
                  for (var pi_id in piAuths) {
                      var newest_pi_auth = piAuths[pi_id].sort(function (a, b) {
                          var sortVector = b.Approval_date - a.Approval_date || b.Amendment_number - a.Amendment_number || b.Key_id - a.Key_id;
                          return sortVector;
                      })[0];
                      $rootScope.piAuths.push(newest_pi_auth);
                      $rootScope.filtered = $rootScope.piAuths;
                  }
                  console.log($scope.piAuths);
              },
              function () {
                  console.log("dang!");
              }
          )
      }

      $rootScope.piAuthsPromise = af.getAllPIs().then(getAllPIAuthorizations);
      $rootScope.search = function (filterObj,auths) {
        if (!filterObj.fromDate) return $scope.piAuths;
        $scope.filtered = auths.filter(function (a) {
            var d = moment(a.Approval_date);
            if (d < moment(filterObj.fromDate)) return false;
            if (filterObj.toDate && d > moment(filterObj.toDate)) return false;
            return true;
        });
        console.log($scope.filtered);
        return $scope.filtered;
      }

      $scope.print = function () {
          window.print();
      }

      console.log("AuthReportCtrl running asdf");
  });

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('CarboysCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		var getAllCarboys = function(){
  			af.getAllCarboys()
  			.then(
  				function(carboys){  	
  					$scope.carboys = dataStore.Carboy;
  				},
  				function(){}
  			)
  		}

  		$scope.af = af;
  		$rootScope.carboysPromise = af.getAllPIs()
  										.then(getAllCarboys);
    
        $scope.deactivate = function(carboy){
            var copy = dataStoreManager.createCopy(carboy);
            copy.Retirement_date = new Date();
            af.saveCarboy(carboy.PrincipalInvestigator, copy, carboy);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Carboy();
                object.Class = "Carboy";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/carboy-modal.html',
                controller: 'CarboysModalCtrl'
            });
        }

  })
  .controller('CarboysModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;

		$scope.modalData = af.getModalData();
        console.log($scope.modalData);
        $scope.save = function(carboy) {
            af.saveCarboy(carboy.PrincipalInvestigator, carboy, $scope.modalData.Carboy)
                .then($scope.close);
        }

		$scope.close = function(){
           $modalInstance.dismiss();
            dataStore.Carboy.push($scope.modalData.CarboyCopy);
           af.deleteModalData();
		}

	});

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('disposalCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;
    $scope.cv = convenienceMethods;

    var getAllDrums = function(){
       return af.getAllDrums()
            .then(
                function(drums){
                    if(!dataStore.Drum)dataStore.Drum=[];
                    $rootScope.drums = dataStore.Drum;
                    return drums;
                }
            );  
    }

    var getAllWasteBags = function(){
        return af.getAllWasteBags()
            .then(
                function(bags){
                    if(!dataStore.WasteBag)dataStore.WasteBag=[];
                    var i = dataStore.WasteBag.length;
                    while(i--){
                        dataStore.WasteBag[i].loadPickup()
                    }
                    $scope.wasteBags = dataStore.WasteBag;
                    return bags;
                }
            )
    }

    var getCycles = function(){
        return af.getAllCarboyUseCycles()
            .then(
                function(cycles){
                    if (!dataStore.CarboyUseCycle) dataStore.CarboyUseCycle = [];
                    $scope.cycles = dataStoreManager.get("CarboyUseCycle");
                    return cycles;
                }
            )
    }


    var getSVCollections = function(){
        return af.getAllScintVialCollections()
            .then(
                function(svCollections){
                    if(!dataStore.ScintVialCollection)dataStore.ScintVialCollection=[];
                    var i = dataStore.ScintVialCollection.length;
                    while(i--){
                        dataStore.ScintVialCollection[i].loadPickup()
                    }
                    $rootScope.svCollections = dataStore.ScintVialCollection;
                    return svCollections;
                }
            )
    }

    var getIsotopes = function () {
        return af.getAllIsotopes()
            .then(
                function (isotopes) {
                    $rootScope.isotopes = dataStore.Isotope;
                    return isotopes;
                }
            )
    }

    var getMiscWaste = function () {
        return af.getAllMiscellaneousWaste()
            .then(
                function (mics) {
                    $rootScope.miscWastes = dataStore.MiscellaneousWaste;
                    return mics;
                }
            )
    }

    getAllWasteBags()
        .then(getIsotopes)
        .then(getSVCollections)
        .then(getAllDrums)
        .then(getCycles)
        .then(getMiscWaste);

    $scope.date = new Date();

    $scope.assignDrum = function(object){
        var modalData = {};
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/admin/admin-modals/drum-assignment.html',
          controller: 'DrumAssignmentCtrl'
        });
    }

    $scope.drumModal = function(object){
        var modalData = {};
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/admin/admin-modals/drum-shipment.html',
          controller: 'DrumShipCtrl'
        });
    }

    $scope.editDrum = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.Drum();
            object.Class = "Drum";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/drum-modal.html',
            controller: 'DrumShipCtrl'
        });
    }

    $scope.editCycle = function(cycle){
        cycle.edit=true;
        af.createCopy(cycle);
    }
    $scope.cancelEditCycle = function(cycle){
        cycle.edit = false;
        $rootScope.CarboyUseCycleCopy = {}
    }

    $scope.pour = function (cycle) {
        if (!cycle.pourable) {
            if (window.confirm("This carboy will not decay until "+convenienceMethods.dateToIso(cycle.Pour_allowed_date) + ". Are you sure you want to pour it now?")) {
                pour(cycle);
            }
        } else {
            pour(cycle);
        }
        function pour(cycle) {
            af.createCopy(cycle);
            af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle, true)
        }
    }

    $scope.editReading = function(reading){
        reading.edit = true;
        af.createCopy(reading);
    }

    $scope.addReading = function (cycle) {
        cycle.readingEdit = true;
        $rootScope.CarboyReadingAmountCopy = new window.CarboyReadingAmount();
        $rootScope.CarboyReadingAmountCopy.Carboy_use_cycle_id = cycle.Key_id;
        $rootScope.CarboyReadingAmountCopy.edit = true;
        $rootScope.CarboyReadingAmountCopy.Class = "CarboyReadingAmount";
        if (!cycle.Carboy_reading_amounts) cycle.Carboy_reading_amounts = [];
        cycle.Carboy_reading_amounts.push($rootScope.CarboyReadingAmountCopy);
    }
    
    $scope.removeReading = function(cycle, reading){
        reading.edit = true;
        af.createCopy(reading);
        for (var n = 0; n < cycle.Carboy_reading_amounts.length; n++) {
            if(cycle.Carboy_reading_amounts[n] == reading) {
                // TODO, make sure this is actually being saved. Don't think it is currently.
                af.createCopy(cycle);
                cycle.Carboy_reading_amounts.splice(n, 1);
                af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle);
            }
        }
    }

    $scope.getIsPastHotRoomDate = function (cycle) {
        var todayAtMidnight = new Date();
        todayAtMidnight.setHours(0, 0, 0, 0);
        var date = cycle.Hot_check_date;
        var hotCheckSeconds = convenienceMethods.getDate(date).getTime();
        return hotCheckSeconds < todayAtMidnight.getTime();
    }
    $scope.resetHotRoomDate = function (cycle) {
        af.createCopy(cycle);
        $rootScope.CarboyUseCycleCopy.Hot_check_date = convenienceMethods.setMysqlTime(new Date());
        af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle);
    }
    $scope.getDateRead = function (reading) {
        if (reading.Date_read) return convenienceMethods.dateToIso(reading.Date_read);
        return convenienceMethods.dateToIso(convenienceMethods.setMysqlTime(new Date()));
    }

    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new window.MiscellaneousWaste();
            object.Class = "MiscellaneousWaste";
        }
        modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
            templateUrl: 'views/admin/admin-modals/misc-waste-modal.html',
            controller: 'MiscWasteModalCtrl'
        });

        modalInstance.result.then(function () {
            getMiscWaste();
        });
    }
  })
  .controller('DrumAssignmentCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        $scope.saveWasteBag = function(bag, copy){
            $scope.close();
            $rootScope.saving = af.saveWasteBag(bag, copy)
                                    .then(reloadDrum)
        }

        $scope.saveCarboyUseCycle = function (cycle, copy) {
            $scope.close();
            $rootScope.saving = af.saveCarboyUseCycle(copy, cycle)
                                    .then(reloadDrum)
        }

        $scope.saveSVCollection = function(collection, copy){
            $scope.close();
            $rootScope.saving = af.saveSVCollection(collection, copy)
                                    .then(reloadDrum)
        }

        var reloadDrum = function(obj){
            var drum =  dataStoreManager.getById("Drum", obj.Drum_id);
            af.replaceDrum(drum)
                .then(
                    function(returnedDrum){
                        return drum;
                    }
            );
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
  .controller('DrumShipCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        $scope.shipDrum = function (drum, copy) {
            copy.Date_destroyed = convenienceMethods.setMysqlTime(convenienceMethods.getDate(copy.view_Date_destroyed));
            $rootScope.saving = af.saveDrum(drum, copy);
            $scope.close();
        }

        $scope.saveDrum = function (drum, copy) {
            $rootScope.saving = af.saveDrum(drum, copy)
            $scope.close();
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
  .controller('drumDetailCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
      var af = $scope.af = actionFunctionsFactory;

      var getDrum = function (id) {
          return af.getAllDrums()
               .then(
                   function (drums) {
                       if (!dataStore.Drum) dataStore.Drum = [];                       
                       $scope.drum = dataStoreManager.getById("Drum", id);
                       $scope.drum.loadDrumWipeTest();
                       return $scope.drum;
                   }
               );
      }

      $rootScope.loading = getDrum($stateParams.drumId);

      $scope.editDrumWipeTest = function (drum, test) {
          $rootScope.DrumWipeTestCopy = {}

          if (!test) {
              $rootScope.DrumWipeTestCopy = new window.DrumWipeTest();
              $rootScope.DrumWipeTestCopy.Drum_id = drum.Key_id
              $rootScope.DrumWipeTestCopy.Class = "DrumWipeTest";
              $rootScope.DrumWipeTestCopy.Is_active = true;
          } else {
              af.createCopy(test);
          }
          drum.Creating_wipe = true;
      }

      $scope.cancelDrumWipeTestEdit = function (drum) {
          drum.Creating_wipe = false;
          $rootScope.DrumWipeTestCopy = {}
      }

      $scope.cancelDrumWipeEdit = function (test, smear) {
          smear.edit = false;
          $rootScope.DrumWipeTestCopy = {}
      }

      $scope.editDrumWipe = function (wipeTest, wipe) {
          if (!wipeTest.Drum_wipes) wipeTest.Drum_wipes = [];

          $rootScope.DrumWipeCopy = {}
          var i = wipeTest.Drum_wipes.length;
          while (i--) {
              wipeTest.Drum_wipes[i].edit = false;
          }

          if (!wipe) {
              $rootScope.DrumWipeCopy = new window.DrumWipe();
              $rootScope.DrumWipeCopy.Drum_wipe_test_id = wipeTest.Key_id
              $rootScope.DrumWipeCopy.Class = "DrumWipe";
              $rootScope.DrumWipeCopy.edit = true;
              $rootScope.DrumWipeCopy.Is_active = true;
              wipeTest.Drum_wipes.unshift($rootScope.DrumWipeCopy);
          } else {
              wipe.edit = true;
              af.createCopy(wipe);
          }

      }

  })
    .controller('MiscWasteModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        var md = $scope.modalData = af.getModalData();

        var amount = new ParcelUseAmount();
        if (md.MiscellaneousWasteCopy &&
            (!md.MiscellaneousWasteCopy.Parcel_use_amountss
            || !md.MiscellaneousWasteCopy.Parcel_use_amounts.length > 0)) {
            amount.Miscellaneous_waste_id == md.MiscellaneousWasteCopy || null;
        } else {
            angular.extend(amount, md.MiscellaneousWaste.Parcel_use_amounts[0]);
        }
        md.MiscellaneousWasteCopy.Parcel_use_amounts = [amount];

        $scope.save = function (copy, mw) {
            console.log(copy, mw);
            af.saveMiscellaneousWaste(copy, mw).then(function () {
                $modalInstance.close(mw);
            });
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

    }])

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiRadHomeCtrl
 * @description
 * # PiRadHomeCtrl
 * Controller of the 00RsmsAngularOrmApp PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InventoriesCtrl', function ($scope, actionFunctionsFactory, $rootScope, $state) {

      var af = actionFunctionsFactory;
      var getInventory = function(){
        /*
        console.log($state);
        $scope.pi_inventory = dataStoreManager.getById("PIQuarterlyInventory", $state.params.pi_inventory);
        console.log($scope.pi_inventory);
        */
        af.getQuartleryInventory(1)
          .then(
            function(){
              $scope.pi_inventory = dataStoreManager.getById("PIQuarterlyInventory",1);
            }
          )
      }
      
      $scope.getAllPIs = af.getAllPIs()
            .then(
                function( pis ){
                    $scope.PIs = pis;
                    return;
                },
                function(){
                    $scope.error = "Couldn't get the PIs";
                    return false;
                }

            );
          
      $scope.af = af;
    
      $scope.inventoryPromise = af.getMostRecentInventory()
          .then(
              function(inventory){
                  $scope.inventory = inventory;
                  console.log(inventory);
              },
              function(){}
          )

      if($state.current.name == 'radmin-quarterly-inventory'){
        getInventory();
      }

      $scope.getInventoriesByPiId = function(id){
          $scope.piInventoriesPromise = af.getInventoriesByPiId(id)
            .then(
              function(piInventories){
                  console.log(piInventories);
                  $scope.piInventories = piInventories;
              }
            )
      }

      $scope.createInventory = function(endDate, dueDate){
        af.createQuarterlyInventory(endDate, dueDate)
          .then(
            function(inventory){
              $scope.inventory = inventory;
              console.log(inventory);
            },
            function(){}
          );
      }

  });

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PickupCtrl
 * @description
 * # RecipticalCtrl
 * Controller of the 00RsmsAngularOrmApp PI waste Pickups view
 */
angular.module('00RsmsAngularOrmApp')
  .controller('IsotopeCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modal, convenienceMethods) {
  		var af = actionFunctionsFactory;

  		var getAllIsotopes = function(){
  			af.getAllIsotopes()
  			.then(
  				function(isotopes){  	
  					$scope.isotopes = dataStore.Isotope;
  				},
  				function(){}
  			)
  		}

  		$scope.af = af;
  		$rootScope.isotopesPromise = getAllIsotopes();
    
        $scope.deactivate = function(isotope){
            var copy = dataStoreManager.createCopy(isotope);
            copy.Is_active = !copy.Is_active;
            af.saveCarboy(copy, isotope);
        }
    
        $scope.openModal = function(object) {
            var modalData = {};
            if (!object) {
                object = new window.Carboy();
                object.Class = "Carboy";
            }
            modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/isotope-modal.html',
                controller: 'IsotopeModalCtrl'
            });
        }

  })
  .controller('IsotopeModalCtrl', function ($scope, actionFunctionsFactory, $stateParams, $rootScope, $modalInstance) {
		var af = actionFunctionsFactory;
		$scope.af = af;
		$scope.modalData = af.getModalData();
    
        if(!af.getModalData().Isotope){
            $scope.modalData.IsotopeCopy = new window.Isotope();
            $scope.modalData.IsotopeCopy.Class="Isotope";
        }
    
        console.log($scope.modalData);
        $scope.save = function(copy, isotope) {
            af.saveIsotope(copy, isotope)
                .then($scope.close);
        }

		$scope.close = function(){
           $modalInstance.dismiss();
           af.deleteModalData();
		}

	});

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
                        pi.loadWasteBags();
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
        console.log(amendments);

        var highestAuthNumber = 0;
        amendments.sort(function (a, b) {
            return moment(a.Approval_date).valueOf() - moment(b.Approval_date).valueOf();
        })

        console.log(amendments);

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

    $scope.markAsArrived = function (pi, parcel) {
        var copy = new window.Parcel();
        angular.extend(copy, parcel);
        copy.Status = Constants.PARCEL.STATUS.DELIVERED;
        copy.Arrival_date = convenienceMethods.setMysqlTime(new Date());
        $scope.saving = af.saveParcel( copy, parcel, pi )
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
        $scope.cm = convenienceMethods;

        

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
                Room_id: cycle.Room ? cycle.Room.Key_id : null,
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


'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
    .controller('RadminMainCtrl', function ($scope, $rootScope, actionFunctionsFactory, $state, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.$state = $state;
        af.getRadModels()
            .then(
                function (models) {
                    var pis = dataStoreManager.get('PrincipalInvestigator');
                    console.log(dataStore);
                    $scope.typeAheadPis = [];
                    var i = pis.length;
                    while (i--) {
                            if (pis[i].User) {
                                var pi = {
                                Key_id:pis[i].Key_id,
                                User:{
                                    Name: pis[i].User.Name,
                                    Key_id: pis[i].Key_id
                                }
                            };
                        }
                        $scope.typeAheadPis.push(pi);
                    }
                }
            )

        $scope.onSelectPi = function (pi) {
            $state.go('radmin.pi-detail', {
                pi: pi.Key_id
            });
        }

    });

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:RadminMainCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin
 */
angular.module('00RsmsAngularOrmApp')
  .controller('RadminParentCtrl', function ($scope, $q, $http, actionFunctionsFactory, $state,pis) {
    alert('in contorller');
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

'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('WipeTestController', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        var getParcels = function () {
            return af.getAllParcels()
                .then(
                    function (parcels) {
                        var i = parcels.length;
                        while (i--) {
                            parcels[i].loadPrincipalInvestigator();
                        }
                        $rootScope.parcels = dataStore.Parcel;
                        return parcels;
                    }
                );
        }

        var getMiscTests = function () {
            return af.getAllMiscellaneousWipeTests()
                .then(
                    function (tests) {
                        if (!dataStore.MiscellaneousWipeTest) dataStore.MiscellaneousWipeTest = [];
                        $rootScope.miscellaneousWipeTests = dataStore.MiscellaneousWipeTest;
                    }
                )
        }

        getParcels()
            .then(getMiscTests);

        $scope.editParcelWipeTest = function (parcel, test) {
            $rootScope.ParcelWipeTestCopy = {}

            if (!test) {
                $rootScope.ParcelWipeTestCopy = new window.ParcelWipeTest();
                $rootScope.ParcelWipeTestCopy.Parcel_id = parcel.Key_id
                $rootScope.ParcelWipeTestCopy.Class = "ParcelWipeTest";
                $rootScope.ParcelWipeTestCopy.Is_active = true;
            } else {
                af.createCopy(test);
            }
            parcel.Creating_wipe = true;
        }

        $scope.cancelParcelWipeTestEdit = function (parcel) {
            parcel.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {}
        }

        $scope.editWipeParcelWipe = function (wipeTest, wipe) {
            $rootScope.ParcelWipeCopy = {}
            if (!wipeTest.Parcel_wipes) wipeTest.Parcel_wipes = [];
            var i = wipeTest.Parcel_wipes.length;
            while (i--) {
                wipeTest.Parcel_wipes[i].edit = false;
            }

            if (!wipe) {
                $rootScope.ParcelWipeCopy = new window.ParcelWipe();
                $rootScope.ParcelWipeCopy.Parcel_wipe_test_id = wipeTest.Key_id
                $rootScope.ParcelWipeCopy.Class = "ParcelWipe";
                $rootScope.ParcelWipeCopy.edit = true;
                $rootScope.ParcelWipeCopy.Is_active = true;
                wipeTest.Parcel_wipes.unshift($rootScope.ParcelWipeCopy);
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.addMiscWipes = function (test) {
            //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
            if (!test.Miscellaneous_wipes) test.Miscellaneous_wipes = [];
            var i = 10
            while (i--) {
                var miscellaneousWipe = new window.MiscellaneousWipe();
                miscellaneousWipe.Miscellaneous_wipe_test_id = test.Key_id;
                miscellaneousWipe.Class = "MiscellaneousWipe";
                miscellaneousWipe.edit = true;
                test.Miscellaneous_wipes.push(miscellaneousWipe);
            }
            test.adding = true;
        }

        $scope.cancelParcelWipeEdit = function (wipe, test) {
            wipe.edit = false;
            $rootScope.ParcelWipeCopy = {};
            var i = test.Parcel_wipes.length;
            while (i--) {
                if (!test.Parcel_wipes[i].Key_id) {
                    test.Parcel_wipes.splice(i, 1);
                }
            }
        }

        $scope.clouseOutMWT = function (test) {
            af.createCopy(test);
            $rootScope.MiscellaneousWipeTestCopy.Closeout_date = convenienceMethods.setMysqlTime(new Date());
            af.saveMiscellaneousWipeTest($rootScope.MiscellaneousWipeTestCopy);
        }

        $scope.cancelMiscWipeTestEdit = function (test) {
            $scope.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {}
        }

        $scope.editMiscWipe = function (test, wipe) {
            $rootScope.MiscellaneousWipeCopy = {}
            if (!test.Miscellaneous_wipes) test.Miscellaneous_wipes = [];
            var i = test.Miscellaneous_wipes.length;
            while (i--) {
                test.Miscellaneous_wipes[i].edit = false;
            }

            if (!wipe) {
                $rootScope.MiscellaneousWipeCopy = new window.MiscellaneousWipe();
                $rootScope.MiscellaneousWipeCopy.Class = "MiscellaneousWipe";
                $rootScope.MiscellaneousWipeCopy.Is_active = true;
                $rootScope.MiscellaneousWipeCopy.miscellaneous_wipe_test_id = test.Key_id
                $rootScope.MiscellaneousWipeCopy.edit = true;
                test.Miscellaneous_wipes.unshift($rootScope.MiscellaneousWipeCopy);
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.cancelMiscWipeEdit = function (test, wipe) {
            wipe.edit = false;
            $rootScope.MiscellaneousWipeCopy = {};
            var i = test.Miscellaneous_wipes.length;
            while (i--) {
                if (!test.Miscellaneous_wipes[i].Key_id) {
                    console.log()
                    test.Miscellaneous_wipes.splice(i, 1);
                }
            }
        }

        //Suggested/common locations for performing parcel wipes
        $scope.parcelWipeLocations = ['Background', 'Outside', 'Inside', 'Bag', 'Styrofoam', 'Cylinder', 'Vial', 'Lead Pig'];

        $scope.openModal = function (object) {
            console.log(object);
            var modalData = {};
            if (object) modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/misc-wipe-modal.html',
                controller: 'MiscellaneousWipeTestCtrl'
            });
        }

    })
    .controller('MiscellaneousWipeTestCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        if (!$scope.modalData.MiscellaneousWipeTest) {
            $scope.modalData.MiscellaneousWipeTest = new window.MiscellaneousWipeTest();
            $scope.modalData.MiscellaneousWipeTest.Class = "MiscellaneousWipeTest";
            $scope.modalData.MiscellaneousWipeTest.Is_active = true;
        }

        $scope.save = function (test) {
            af.saveMiscellaneousWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
    .controller('WipeTestModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {

        //TODO:  if af.getModalData() doesn't have wipeTest, create and save one for it
        //       creating wipe test message while loading
        //
        //

        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();

        $scope.editParcelWipeTest = function(parcel, originalParcel, force) {
            if (!parcel.Wipe_test || !parcel.Wipe_test.length) {
                parcel.Wipe_test = [modelInflatorFactory.instantiateObjectFromJson(new window.ParcelWipeTest())];
                parcel.Wipe_test[0].parcel_id = parcel.Key_id
                parcel.Wipe_test[0].Class = "ParcelWipeTest";
                parcel.Wipe_test[0].edit = true;
                parcel.Wipe_test[0].Parcel_wipes = [];
                for (var i = 0; i < 7; i++) {
                    var wipe = new window.ParcelWipe();
                    wipe.Parcel_wipe_test_id = parcel.Key_id ? parcel.Key : null;
                    wipe.Rading_type = "LSC";
                    wipe.edit = true;
                    wipe.Class = 'ParcelWipe';
                    if (i == 0) wipe.Location = "Background";
                    parcel.Wipe_test[0].Parcel_wipes.push(wipe);
                }
                if(!force) var force = true;
            } else {
                console.log(parcel);
                af.createCopy(parcel.Wipe_test[0]);
            }
            if(force)originalParcel.Creating_wipe = true;
        }

        $scope.editParcelWipeTest($scope.modalData.ParcelCopy, $scope.modalData.Parcel);

        $scope.cancelParcelWipeTestEdit = function (parcel) {
            parcel.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {}
        }

        $scope.editWipeParcelWipe = function (wipeTest, wipe, force) {
            $rootScope.ParcelWipeCopy = {}
            if (!wipeTest.Parcel_wipes) wipeTest.Parcel_wipes = [];
            var i = wipeTest.Parcel_wipes.length;
            while (i--) {
                wipeTest.Parcel_wipes[i].edit = false;
            }

            if (!wipe) {
                af.getModalData().Wipe_test = new window.ParcelWipe();
                af.getModalData().Wipe_test.Parcel_wipe_test_id = wipeTest.Key_id
                af.getModalData().Wipe_test.Class = "ParcelWipe";
                af.getModalData().Wipe_test.edit = true;
                af.getModalData().Wipe_test.Is_active = true;
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.addMiscWipes = function (test) {
            //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
            if (!test.Miscellaneous_wipes) test.Miscellaneous_wipes = [];
            var i = 10
            while (i--) {
                var miscellaneousWipe = new window.MiscellaneousWipe();
                miscellaneousWipe.Miscellaneous_wipe_test_id = test.Key_id;
                miscellaneousWipe.Class = "MiscellaneousWipe";
                miscellaneousWipe.edit = true;
                test.Miscellaneous_wipes.push(miscellaneousWipe);
            }
            test.adding = true;
        }

        $scope.cancelParcelWipeEdit = function (wipe, test) {
            wipe.edit = false;
            $rootScope.ParcelWipeCopy = {};
            var i = test.Parcel_wipes.length;
            while (i--) {
                if (!test.Parcel_wipes[i].Key_id) {
                    test.Parcel_wipes.splice(i, 1);
                }
            }
        }

        $scope.onClick = function () {
            alert('wrong ctrl')
        }

        //Suggested/common locations for performing parcel wipes
        $scope.parcelWipeLocations = [
            {
                Name: "Background"
            },
            {
                Name: "Outside"
            },
            {
                Name: "Inside"
            },
            {
                Name: "Bag"
            },
            {
                Name: "Styrofoam"
            },
            {
                Name: "Cylinder"
            },
            {
                Name: "Vial"
            },
            {
                Name: "Lead Pig"
            }
        ]
        $scope.setLocation = function (wipe) {
            if (wipe.Location) {
                var i = $scope.parcelWipeLocations.length;
                while (i--) {
                    if (wipe.Location == $scope.parcelWipeLocations.Name) {
                        wipe.DropLocation = $scope.parcelWipeLocations[i];
                        $scope.$apply();
                        break;
                    }
                }
            }
            console.log(wipe);

        }

        $scope.save = function (test) {
            af.saveParcelWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            $scope.modalData.Parcel.Creating_wipe = false;
            af.deleteModalData();
            $modalInstance.dismiss();
        }
 }])