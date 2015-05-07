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
                    if(!dataStore.MiscellaneousWipeTest)dataStore.MiscellaneousWipeTest=[];
                    $rootScope.wasteBags = dataStore.WasteBag;
                    return bags;
                }
            )
    }

    var getAllCarboys = function(){
        return af.getAllCarboys()
            .then(
                function(carboys){
                    console.log(carboys);
                    if(!dataStore.Carboy)dataStore.Carboy=[];
                    $rootScope.carboys = dataStore.Carboy;
                    return carboys;
                }
            )
    }


    var getSVCollections = function(){
        return af.getAllScintVialCollections()
            .then(
                function(svCollections){
                    if(!dataStore.ScintVialCollection)dataStore.ScintVialCollection=[];
                    $rootScope.svCollections = dataStore.ScintVialCollection;
                    return svCollections;
                }
            )
    }

    getAllWasteBags()
        .then(getSVCollections)
        .then(getAllDrums)
        .then(getAllCarboys);

    $scope.editWipeParcelWipeTest = function(parcel, test){
        $rootScope.ParcelWipeTestCopy = {}

        if(!test){
            $rootScope.ParcelWipeTestCopy = new window.ParcelWipeTest();
            $rootScope.ParcelWipeTestCopy.Parcel_id = parcel.Key_id
            $rootScope.ParcelWipeTestCopy.Class = "ParcelWipeTest";
            $rootScope.ParcelWipeTestCopy.Is_active = true;
        }else{
            af.createCopy(test);
        }

        var i = $scope.wipeTestParcels.length
        while(i--){
            $scope.wipeTestParcels[i].Creating_wipe = false;
        }
        parcel.Creating_wipe = true;
        
    }

    $scope.cancelParcelWipeTestEdit = function(parcel){
        parcel.Creating_wipe = false;
        $rootScope.ParcelWipeTestCopy = {}
    }

    $scope.editWipeParcelWipe = function(wipeTest, wipe){
        $rootScope.ParcelWipeCopy = {}
        if(!wipeTest.Parcel_wipes)wipeTest.Parcel_wipes = [];
        var i = wipeTest.Parcel_wipes.length;
        while(i--){
            wipeTest.Parcel_wipes[i].edit = false;
        }

        if(!wipe){
            $rootScope.ParcelWipeCopy = new window.ParcelWipe();
            $rootScope.ParcelWipeCopy.Parcel_wipe_test_id = wipeTest.Key_id
            $rootScope.ParcelWipeCopy.Class = "ParcelWipe";
            $rootScope.ParcelWipeCopy.edit = true;
            $rootScope.ParcelWipeCopy.Is_active = true;
            wipeTest.Parcel_wipes.unshift($rootScope.ParcelWipeCopy);
        }else{
            wipe.edit = true;
            af.createCopy(wipe);
        }
        
    }

    $scope.cancelParcelWipeEdit = function(wipe,test){
        wipe.edit = false;
        $rootScope.ParcelWipeCopy = {};
        var i = test.Parcel_wipes.length;
        while(i--){
            if(!test.Parcel_wipes[i].Key_id){
                test.Parcel_wipes.splice(i,1);
            }
        }
    }

    $scope.clouseOutMWT = function(test){
        af.createCopy(test);
        $rootScope.MiscellaneousWipeTestCopy.Closeout_date = convenienceMethods.setMysqlTime(new Date());
        af.saveMiscellaneousWipeTest($rootScope.MiscellaneousWipeTestCopy);
    }

    $scope.cancelMiscWipeTestEdit = function(test){
        $scope.Creating_wipe = false;
        $rootScope.ParcelWipeTestCopy = {}
    }

    $scope.editMiscWipe = function(test, wipe){
        $rootScope.MiscellaneousWipeCopy = {}
        if(!test.Miscellaneous_wipes)test.Miscellaneous_wipes = [];
        var i = test.Miscellaneous_wipes.length;
        while(i--){
            test.Miscellaneous_wipes[i].edit = false;
        }

        if(!wipe){
            $rootScope.MiscellaneousWipeCopy = new window.MiscellaneousWipe();
            $rootScope.MiscellaneousWipeCopy.Class = "MiscellaneousWipe";
            $rootScope.MiscellaneousWipeCopy.Is_active = true;
            $rootScope.MiscellaneousWipeCopy.miscellaneous_wipe_test_id = test.Key_id
            $rootScope.MiscellaneousWipeCopy.edit = true;
            test.Miscellaneous_wipes.unshift($rootScope.MiscellaneousWipeCopy);
        }else{
            wipe.edit = true;
            af.createCopy(wipe);
        }
        
    }

    $scope.cancelMiscWipeEdit = function(test, wipe){
        wipe.edit = false;
        $rootScope.MiscellaneousWipeCopy = {};
        var i = test.Miscellaneous_wipes.length;
        while(i--){
            if(!test.Miscellaneous_wipes[i].Key_id){
                console.log()
                test.Miscellaneous_wipes.splice(i,1);
            }
        }
    }

    //Suggested/common locations for performing parcel wipes
    $scope.parcelWipeLocations = ['Outside','Inside','Bag','Styrofoam','Cylinder','Vial','Lead Pig'];

    $scope.openModal = function(object){
        console.log(object);
        var modalData = {};
        if(object)modalData[object.Class] = object;
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

        if(!$scope.modalData.MiscellaneousWipeTest){
            $scope.modalData.MiscellaneousWipeTest = new window.MiscellaneousWipeTest();
            $scope.modalData.MiscellaneousWipeTest.Class = "MiscellaneousWipeTest";
            $scope.modalData.MiscellaneousWipeTest.Is_active = true;
        }

        $scope.save = function(test){
            af.saveMiscellaneousWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function(){
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
  