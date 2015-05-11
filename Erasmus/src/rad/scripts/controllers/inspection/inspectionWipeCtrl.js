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
    $scope.dataStore = dataStore;

    var getInspection = function(){

       return af.getInspectionById($stateParams.inspection)
            .then(
                function(inspection){
                    console.log(inspection);
                    inspection.loadPrincipalInvestigator();

                    //if if the inspection doesn't have an inspection wipe test, create one on page load
                    if(!inspection.Inspection_wipe_tests || !inspection.Inspection_wipe_tests.length){
                        inspection.Inspection_wipe_tests = [];
                        $rootScope.InspectionWipeTestCopy = new window.InspectionWipeTest();
                        $rootScope.InspectionWipeTestCopy.Inspection_id = inspection.Key_id;
                        $rootScope.InspectionWipeTestCopy.Class = "InspectionWipeTest";
                        $rootScope.InspectionWipeTestCopy.Reading_type = "Alpha/Beta";
                        $rootScope.InspectionWipeTestCopy.edit = true;
                        inspection.Inspection_wipe_tests.push($rootScope.InspectionWipeTestCopy);
                    }

                    $scope.inspection = dataStoreManager.getById('Inspection',$stateParams.inspection);
                    return inspection;
                }
            );  
    }

    $rootScope.InspectionPromise = getInspection();

    $scope.cancelParcelWipeTestEdit = function(parcel){
        $scope.editWipeTest = false;
        $rootScope.InspectionWipeTestCopy = {}
    }

    $scope.addWipes = function(test){
        $rootScope.InspectionWipeCopy = false;
        //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
        if(!test.Inspection_wipes)test.Inspection_wipes = [];
        var i = 10
        while(i--){
            var inspectionWipe = new window.InspectionWipe();
            inspectionWipe.Inspection_wipe_test_id = test.Key_id;
            inspectionWipe.Class = "InspectionWipe";
            inspectionWipe.edit = true;
            test.Inspection_wipes.push(inspectionWipe);
        }
        test.adding = true;
    }

    $scope.editInspectionWipe = function(wipe){
        wipe.edit = true;
        af.createCopy(wipe);
    }

    $scope.editInspectionWipeTest = function(test){
        test.edit = true;
        af.createCopy(test);
    }

    $scope.cancelEditInspectionWipeTest = function(test){
        test.edit = false;
        $rootScope.InspectionWipeTestCopy = false;
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

  
