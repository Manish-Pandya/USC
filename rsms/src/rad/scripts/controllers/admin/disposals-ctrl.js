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

    var getIsotopes = function(){
        return af.getAllIsotopes()
            .then(
                function(isotopes){
                    $rootScope.isotopes = dataStore.Isotope;
                    return isotopes;
                }
            )
    }

    getAllWasteBags()
        .then(getIsotopes)
        .then(getSVCollections)
        .then(getAllDrums)
        .then(getCycles);

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
