'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InspectionWipeCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;

    var getInspection = function(){


       return af.getInspectionById($stateParams.inspection)
            .then(
                function(inspection){
                    $rootScope.inspection = dataStore.Inspection;
                    return inspection;
                }
            );  
    }

    $rootScope.InspectionPromise = getInspection();

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

  })

  
