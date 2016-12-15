'use strict';

    /*
        This is a random test file that can be used for individually testing various methods and whatnot.

        Whatever you need to display, bind it to $scope.testData.

        Ignore the TypeError: Cannot read property 'Label' of undefined message in the console - 
        whatever it is, it doesn't matter.

        I'm leaving the various commented out code just in case we need to double check that
        some method or another isn't broken.
    */


angular.module('EquipmentModule')
.controller('TestCtrl', function ($scope, applicationControllerFactory, dataSwitchFactory) {
    // allow calling action functions in the scope
    $scope.af = applicationControllerFactory;
    $scope.callLoader = function() {
        $scope.testData[$scope.loaderName]();
    }

    // just checking getAlls work.
    /*
    $scope.testData = actionFunctionsFactory.getAllIsotopes();.then(function(data) {
        console.log("GOT DATA?");
        console.log(data);
        $scope.testData = data;
    });
    //*/

    //one to many
    /*
    actionFunctionsFactory.getDrumById(1).then(function(object) {
        console.log(object);
        $scope.testData = object;
        object.loadWasteBags();
    });
    //*/

    //one to many, with methodString
    /*
    actionFunctionsFactory.getHazardById(1).then(function(object) {
        $scope.testData = object;
        console.log(object);
        setTimeout(function() {
            console.log(object);
        }, 0);
        //object.loadSubHazards();
    });
    //*/

    //many to many
    //*/

});
