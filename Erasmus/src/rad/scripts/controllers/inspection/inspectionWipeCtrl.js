'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # RadminMainCtrl
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
  .controller('InspectionWipeCtrl', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $q) {
    //do we have access to action functions?
    var af = actionFunctionsFactory;
    $scope.af = af;

    var getInspection = function(){

       return af.getInspectionById($stateParams.inspection)
            .then(
                function(inspection){
                    $scope.inspection = dataStoreManager.getById('Inspection',$stateParams.inspection);
                    return inspection;
                }
            );  
    }

    $rootScope.InspectionPromise = getInspection();

    $scope.editWipeInspectionWipeTest = function(inspection, test){
        $rootScope.InspectionWipeTestCopy = {}

        if(!test){
            $rootScope.InspectionWipeTestCopy = new window.ParcelWipeTest();
            $rootScope.InspectionWipeTestCopy.Inspection_id = inspection.Key_id
            $rootScope.InspectionWipeTestCopy.Class = "InspectionWipeTest";
            $rootScope.InspectionWipeTestCopy.Is_active = true;
        }else{
            af.createCopy(test);
        }
        $scope.editWipeTest = true;
    }

    $scope.cancelParcelWipeTestEdit = function(parcel){
        $scope.editWipeTest = false;
        $rootScope.InspectionWipeTestCopy = {}
    }

    $scope.editInspectionWipe = function(wipeTest, wipe){

        var testPromise = $q.defer();

        //  if there is already a wipetest, resolve the promise with it
        if(wipeTest){
            console.log(wipeTest);
            testPromise.resolve(wipeTest);
        }
        // if not, create a new one and save it
        else{
            var wipeTest = new window.InspectionWipeTest();
            wipeTest.Inspection_id = $scope.inspection.Key_id;
            wipeTest.Class = "InspectionWipeTest";
            console.log(wipeTest);
            af.saveInspectionWipeTest(wipeTest)
                .then(function(returnedWipeTest){
                    $scope.inspection.Inspection_wipe_tests.push(returnedWipeTest);
                    testPromise.resolve(returnedWipeTest);
                    console.log(returnedWipeTest);
                    return testPromise.promise;
                },
                function(){
                    testPromise.reject();
                });
        }

        testPromise.promise
            .then(
                function(wipeTest){
                    if(!wipeTest.Inspection_wipes)wipeTest.Inspection_wipes = [];
                    var i = wipeTest.Inspection_wipes.length;
                    while(i--){
                        wipeTest.Inspection_wipes[i].edit = false;
                    }

                    if(!wipe){
                        $rootScope.InspectionWipeCopy = new window.InspectionWipe();
                        $rootScope.InspectionWipeCopy.Inspection_wipe_test_id = wipeTest.Key_id
                        $rootScope.InspectionWipeCopy.Class = "InspectionWipe";
                        $rootScope.InspectionWipeCopy.edit = true;
                        $rootScope.InspectionWipeCopy.Is_active = true;
                        wipeTest.Inspection_wipes.unshift($rootScope.InspectionWipeCopy);
                    }else{
                        $rootScope.InspectionWipeCopy = {};
                        var i = wipeTest.Inspection_wipes.length;
                        while(i--){
                            wipeTest.Inspection_wipes[i].edit = false;
                        }
                        wipe.edit = true;
                        af.createCopy(wipe);
                    }
                }
            )
        
    }

    $scope.cancelInspectionWipeEdit = function(wipe,test){
        wipe.edit = false;
        $rootScope.InspectionWipeCopy = {};
        var i = test.Inspection_wipes.length;
        while(i--){
            if(!test.Inspection_wipes[i].Key_id){
                test.Inspection_wipes.splice(i,1);
            }
        }
    }

  })

  
