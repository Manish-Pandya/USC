'use strict';

    /*
        This is a random test file that can be used for individually testing various methods and whatnot.

        Whatever you need to display, bind it to $scope.testData.

        Ignore the TypeError: Cannot read property 'Label' of undefined message in the console - 
        whatever it is, it doesn't matter.

        I'm leaving the various commented out code just in case we need to double check that
        some method or another isn't broken.
    */


angular.module('00RsmsAngularOrmApp')
.controller('TestCtrl', function ($scope, actionFunctionsFactory, dataSwitchFactory) {
    // allow calling action functions in the scope
    $scope.af = actionFunctionsFactory;
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
    actionFunctionsFactory.getParcelUseById(1).then(function(object) {
        $scope.testData = object;
        console.log(object);
        setTimeout(function() {
            console.log(object);
        }, 0);
        //object.loadParcel();
    });
    //*/

    //many to many
    actionFunctionsFactory.getPrincipalInvestigatorById(1).then(function(object) {
        console.log('hooray!');
        console.log(object);
        $scope.testData = object;
        //object.loadRooms();
    });
    //*/

});
