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
                    console.log(cycles);
                    if(!dataStore.CarboyUseCycle)dataStore.CarboyUseCycle=[];
                    $scope.cycles = dataStore.CarboyUseCycle;
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
                    console.log(isotopes);
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

    $scope.assignDrum = function(object){
        console.log(object);
        var modalData = {};
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/admin/admin-modals/drum-assignment.html',
          controller: 'DrumAssignmentCtrl'
        });
    }

    $scope.drumModal = function(object){
        console.log(object);
        var modalData = {};
        if(object)modalData[object.Class] = object;
        af.setModalData(modalData);
        var modalInstance = $modal.open({
          templateUrl: 'views/admin/admin-modals/drum-shipment.html',
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

    $scope.pour = function(cycle){
        af.createCopy(cycle);
        af.saveCarboyUseCycle($rootScope.CarboyUseCycleCopy, cycle, true)
    }

    $scope.editReading = function(reading){
        reading.edit = true;
        af.createCopy(reading);
    }

    $scope.addReading = function(cycle){
        $rootScope.CarboyReadingAmountCopy = new window.CarboyReadingAmount();
        $rootScope.CarboyReadingAmountCopy.Carboy_use_cycle_id = cycle.Key_id;
        $rootScope.CarboyReadingAmountCopy.edit = true;
        cycle.Carboy_reading_amounts.push($rootScope.CarboyReadingAmountCopy);
    }

  })
  .controller('DrumAssignmentCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        $scope.saveWasteBag = function(bag, copy){
            $scope.close();
            $rootScope.saving = af.saveWasteBag(bag, copy)
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
        console.log($scope.modalData);
                    $scope.close();

        $scope.shipDrum = function(drum, copy){
            $rootScope.saving = af.saveDrum(drum, copy)
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
