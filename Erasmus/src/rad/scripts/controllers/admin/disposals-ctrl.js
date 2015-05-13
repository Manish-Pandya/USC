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

    getAllWasteBags()
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

  })
  .controller('DrumAssignmentCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        $scope.saveWasteBag = function(bag, copy){
            $rootScope.saving = af.saveWasteBag(bag, copy)
                                    .then(reloadDrum)
                                    .then($scope.close);
        }

        $scope.saveSVCollection = function(collection, copy){
            $rootScope.saving = af.saveSVCollection(collection, copy)
                                    .then(reloadDrum)
                                    .then($scope.close);
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
  
